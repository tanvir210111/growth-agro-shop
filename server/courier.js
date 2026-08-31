/**
 * server/courier.js - BD Courier Integration Module (Production Hardened)
 * 
 * Provides secure, rate-limited, timeout-protected, and normalized proxying
 * for BD Courier Customer Delivery Ratio & Fraud Check API.
 * 
 * Architecture:
 * Admin Panel -> POST /api/courier/check -> server/courier.js -> BD Courier API (api.bdcourier.com)
 */

const https = require('https');
const fs = require('fs');
const path = require('path');

/**
 * Resolve BD Courier API key from process environment or local .env file (Zero hardcoding)
 */
function resolveApiKey(overrideKey) {
  if (overrideKey) return overrideKey;
  if (process.env.BD_COURIER_API_KEY) return process.env.BD_COURIER_API_KEY;
  if (process.env.BDCOURIER_API_KEY) return process.env.BDCOURIER_API_KEY;

  const candidatePaths = [
    path.join(__dirname, '..', 'E Commerce Baby', '.env'),
    path.join(__dirname, '..', '.env')
  ];
  for (const envPath of candidatePaths) {
    if (fs.existsSync(envPath)) {
      try {
        const lines = fs.readFileSync(envPath, 'utf8').split(/\r?\n/);
        for (const line of lines) {
          const trimmed = line.trim();
          if (trimmed.startsWith('BD_COURIER_API_KEY=') || trimmed.startsWith('BDCOURIER_API_KEY=')) {
            let val = trimmed.split('=')[1].trim();
            if ((val.startsWith('"') && val.endsWith('"')) || (val.startsWith("'") && val.endsWith("'"))) {
              val = val.slice(1, -1);
            }
            if (val) {
              process.env.BD_COURIER_API_KEY = val;
              return val;
            }
          }
        }
      } catch (e) {}
    }
  }
  return null;
}

const COURIER_TIMEOUT_MS = parseInt(process.env.BD_COURIER_TIMEOUT_MS || '8000', 10);
const CACHE_TTL_MS = 5 * 60 * 1000; // 5 minutes cache
const CACHE_MAX_ENTRIES = 500;

// Short-lived in-memory cache for repeated courier checks
const courierCache = new Map();

// Rate limiter for courier check endpoint (per IP)
const courierRateLimitMap = new Map();
const RATE_LIMIT_WINDOW_MS = 60 * 1000;
const RATE_LIMIT_MAX_REQUESTS = parseInt(process.env.RATE_LIMIT_COURIER || '60', 10);

/**
 * Clean up expired cache and rate limit entries
 */
function cleanupState() {
  const now = Date.now();
  for (const [phone, item] of courierCache.entries()) {
    if (now > item.expiresAt) {
      courierCache.delete(phone);
    }
  }
  for (const [ip, item] of courierRateLimitMap.entries()) {
    if (now > item.resetTime) {
      courierRateLimitMap.delete(ip);
    }
  }
}
setInterval(cleanupState, 60000).unref();

/**
 * Mask phone number for safe logging (e.g. 01712345678 -> 017****5678)
 */
function maskPhone(phone) {
  if (!phone || typeof phone !== 'string' || phone.length < 7) return '***';
  return phone.slice(0, 3) + '****' + phone.slice(-4);
}

/**
 * Strict server-side validation for 11-digit Bangladeshi mobile numbers
 */
function validateBdPhone(rawPhone) {
  if (typeof rawPhone !== 'string') {
    return { valid: false, error: 'Phone number must be a string.' };
  }

  const trimmed = rawPhone.trim();
  if (!trimmed) {
    return { valid: false, error: 'Phone number is required.' };
  }

  if (trimmed.length > 20) {
    return { valid: false, error: 'Phone number is too long.' };
  }

  // Check for invalid injection characters
  if (/[<>{}$`'"]/.test(trimmed)) {
    return { valid: false, error: 'Invalid characters in phone number.' };
  }

  const clean = trimmed.replace(/[\s\-]/g, '');
  const match = clean.match(/^(?:\+88|88)?(01[3-9]\d{8})$/);

  if (!match) {
    return { valid: false, error: 'Invalid Bangladeshi mobile number format (expected 01XXXXXXXXX).' };
  }

  return { valid: true, normalized: match[1] };
}

/**
 * Check rate limit for courier API
 */
function checkCourierRateLimit(ip) {
  const now = Date.now();
  let record = courierRateLimitMap.get(ip);

  if (!record || now > record.resetTime) {
    record = { count: 1, resetTime: now + RATE_LIMIT_WINDOW_MS };
    courierRateLimitMap.set(ip, record);
    return { allowed: true, remaining: RATE_LIMIT_MAX_REQUESTS - 1 };
  }

  record.count++;
  if (record.count > RATE_LIMIT_MAX_REQUESTS) {
    const retryAfterSec = Math.ceil((record.resetTime - now) / 1000);
    return { allowed: false, retryAfterSec };
  }

  return { allowed: true, remaining: RATE_LIMIT_MAX_REQUESTS - record.count };
}

/**
 * Calculate internal heuristic trust assessment & Delivery Advance Decision
 * 
 * Case 1: No delivery history (totalParcels === 0)
 *   -> Advance = 0, Product = COD
 * Case 2: Success Rate > 80%
 *   -> Advance = 0, Product = COD
 * Case 3: Success Rate <= 80%
 *   -> Dhaka: ৳80 Advance Delivery, Outside: ৳150 Advance Delivery, Product = COD
 */
function calculateHeuristicTrustScore(totalParcels, delivered, cancelled) {
  if (totalParcels === 0) {
    return {
      level: 'new_customer',
      label: 'নতুন কাস্টমার (No Delivery History)',
      success_rate: 100,
      requires_advance: false,
      advance_amount: 0,
      advance_delivery_dhaka: 0,
      advance_delivery_outside: 0,
      product_payment: 'COD',
      methodology: 'Internal Heuristic Assessment: Case 1: No previous courier parcel records found. 100% COD eligible.'
    };
  }

  const successRate = Math.round((delivered / totalParcels) * 100);

  if (successRate > 80) {
    return {
      level: 'safe',
      label: 'বিশ্বস্ত কাস্টমার (High Delivery Trust)',
      success_rate: successRate,
      requires_advance: false,
      advance_amount: 0,
      advance_delivery_dhaka: 0,
      advance_delivery_outside: 0,
      product_payment: 'COD',
      methodology: `Internal Heuristic Assessment: Case 2: Delivery success rate is ${successRate}% (> 80%). 100% COD eligible.`
    };
  }

  // Case 3: Success Rate <= 80% (requires advance delivery charge, product price remains COD)
  const isMedium = successRate >= 60;
  return {
    level: isMedium ? 'medium' : 'high_risk',
    label: isMedium 
      ? 'মাঝারি রিস্ক কাস্টমার (Advance Delivery Charge Required)' 
      : 'উচ্চ রিস্ক কাস্টমার (Advance Delivery Charge Required)',
    success_rate: successRate,
    requires_advance: true,
    advance_amount: 80, // Default to Dhaka, adjusts by zone
    advance_delivery_dhaka: 80,
    advance_delivery_outside: 150,
    product_payment: 'COD',
    methodology: `Internal Heuristic Assessment: Case 3: Delivery success rate is ${successRate}% (<= 80%). Advance delivery charge mandatory (Dhaka: ৳80, Outside: ৳150). Product price is COD.`
  };
}


/**
 * Calculate final delivery and advance decision for a specific zone
 */
function calculateDeliveryDecision(totalParcels, delivered, cancelled, deliveryZone = 'inside') {
  const trust = calculateHeuristicTrustScore(totalParcels, delivered, cancelled);
  const isInsideDhaka = deliveryZone === 'inside' || deliveryZone === 'inside_dhaka' || deliveryZone === 'dhaka';
  const advanceAmount = trust.requires_advance ? (isInsideDhaka ? 80 : 150) : 0;

  return {
    ...trust,
    delivery_zone: isInsideDhaka ? 'inside_dhaka' : 'outside_dhaka',
    advance_amount: advanceAmount,
    delivery_charge: isInsideDhaka ? 80 : 150,
    product_payment: 'COD'
  };
}


/**
 * Normalize upstream BD Courier API response
 */
function normalizeCourierResponse(phone, rawJson) {
  if (!rawJson || typeof rawJson !== 'object') {
    return null;
  }

  // Extract totals across possible API response schema variations
  const totalParcels = parseInt(rawJson.total_parcel ?? rawJson.total ?? rawJson.total_parcels ?? 0, 10) || 0;
  const delivered = parseInt(rawJson.success_parcel ?? rawJson.delivered ?? rawJson.success ?? 0, 10) || 0;
  const cancelled = parseInt(rawJson.cancelled_parcel ?? rawJson.cancelled ?? rawJson.returned ?? 0, 10) || 0;

  const successRate = totalParcels > 0 ? Math.round((delivered / totalParcels) * 100) : (delivered > 0 ? 100 : 0);

  // Extract courier breakdown if available
  const courierBreakdown = [];
  if (rawJson.steadfast) courierBreakdown.push({ name: 'Steadfast', status: rawJson.steadfast });
  if (rawJson.pathao) courierBreakdown.push({ name: 'Pathao', status: rawJson.pathao });
  if (rawJson.redx) courierBreakdown.push({ name: 'RedX', status: rawJson.redx });
  if (rawJson.paperfly) courierBreakdown.push({ name: 'Paperfly', status: rawJson.paperfly });

  const heuristic = calculateHeuristicTrustScore(totalParcels, delivered, cancelled);

  return {
    phone: maskPhone(phone),
    phone_raw: phone,
    total_parcels: totalParcels,
    delivered: delivered,
    cancelled_or_returned: cancelled,
    success_rate: successRate,
    courier_breakdown: courierBreakdown,
    heuristic_trust_score: heuristic,
    cached: false
  };
}

/**
 * Perform secure, timeout-protected courier check against BD Courier API
 * 
 * @param {string} phone - Normalized 11-digit phone number
 * @param {string} [overrideKey] - Optional server override key
 * @returns {Promise<Object>}
 */
function checkBdCourier(phone, overrideKey) {
  return new Promise((resolve) => {
    // 1. Check in-memory short-lived cache
    const cached = courierCache.get(phone);
    if (cached && Date.now() < cached.expiresAt) {
      return resolve({
        success: true,
        data: Object.assign({}, cached.data, { cached: true })
      });
    }

    // 2. Resolve server API key from environment (Never expose in response)
    const apiKey = resolveApiKey(overrideKey);

    if (!apiKey) {
      return resolve({
        success: false,
        message: 'BD Courier API Key is not configured on the server.'
      });
    }

    const payload = JSON.stringify({ phone: phone });
    let isSettled = false;

    const req = https.request({
      hostname: 'api.bdcourier.com',
      path: '/courier-check',
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${apiKey}`,
        'Content-Length': Buffer.byteLength(payload)
      },
      timeout: COURIER_TIMEOUT_MS
    }, (res) => {
      let responseBody = '';
      res.on('data', chunk => responseBody += chunk);
      res.on('end', () => {
        if (isSettled) return;
        isSettled = true;

        let json = null;
        try {
          json = JSON.parse(responseBody);
        } catch (e) {
          json = null;
        }

        // HTTP 200 OK
        if (res.statusCode === 200 && json) {
          const normalized = normalizeCourierResponse(phone, json);
          if (normalized) {
            // Store in short-lived cache
            if (courierCache.size >= CACHE_MAX_ENTRIES) {
              const firstKey = courierCache.keys().next().value;
              courierCache.delete(firstKey);
            }
            courierCache.set(phone, {
              data: normalized,
              expiresAt: Date.now() + CACHE_TTL_MS
            });

            return resolve({
              success: true,
              data: normalized
            });
          }
        }

        // Upstream 401 Unauthorized
        if (res.statusCode === 401) {
          return resolve({
            success: false,
            status_code: 401,
            message: 'BD Courier API authentication failed. Please verify BD_COURIER_API_KEY in server configuration.'
          });
        }

        // Upstream 429 Rate Limit
        if (res.statusCode === 429) {
          return resolve({
            success: false,
            status_code: 429,
            message: 'BD Courier API rate limit reached. Please try again in a few moments.'
          });
        }

        // Upstream 4xx / 5xx error
        const upstreamMsg = (json && typeof json.message === 'string') ? json.message : 'Courier service response error';
        return resolve({
          success: false,
          status_code: res.statusCode,
          message: upstreamMsg
        });
      });
    });

    req.on('timeout', () => {
      if (isSettled) return;
      isSettled = true;
      req.destroy();
      return resolve({
        success: false,
        status_code: 504,
        message: 'Courier service is temporarily unavailable (Request timed out).'
      });
    });

    req.on('error', (err) => {
      if (isSettled) return;
      isSettled = true;
      return resolve({
        success: false,
        status_code: 502,
        message: `Courier service connection error: ${err.message}`
      });
    });

    req.write(payload);
    req.end();
  });
}

module.exports = {
  validateBdPhone,
  checkCourierRateLimit,
  checkBdCourier,
  calculateHeuristicTrustScore,
  calculateDeliveryDecision,
  normalizeCourierResponse,
  maskPhone
};

