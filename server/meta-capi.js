/**
 * Meta Conversions API (CAPI) Client for Node.js Landing Page Server
 * 
 * Centralized service for:
 * - Dynamic runtime configuration retrieval from Laravel internal endpoint
 * - Event validation & PII normalization / SHA-256 hashing
 * - Non-hashed identifier preservation (fbp, fbc, IP, User Agent)
 * - Meta Graph API v20.0 dispatch with bounded timeout & fail-open resilience
 * - Persistent SQLite idempotency preventing duplicate sends
 * - Sanitized logging (zero token or raw PII leakage)
 */

const https = require('https');
const http = require('http');
const crypto = require('crypto');
const { URL } = require('url');

const { findMetaCapiEvent, recordMetaCapiEvent } = require('./db');

const LARAVEL_INTERNAL_BASE_URL = process.env.LARAVEL_INTERNAL_BASE_URL || 
  `http://${process.env.LARAVEL_HOST || '127.0.0.1'}:${process.env.LARAVEL_PORT || 8000}`;
const INTERNAL_API_SECRET = process.env.INTERNAL_API_SECRET || 'baby-fashion-internal-2024-secret';

const ALLOWED_EVENTS = ['PageView', 'AddToCart', 'InitiateCheckout', 'Purchase'];
const CONFIG_CACHE_TTL_MS = 60 * 1000; // 60 seconds in-memory TTL

let cachedConfig = null;
let configCacheExpiry = 0;
let mockConfig = null;
let mockHttpHandler = null;

/**
 * Invalidate in-memory config cache (useful during testing or immediate admin changes)
 */
function invalidateConfigCache() {
  cachedConfig = null;
  configCacheExpiry = 0;
}

/**
 * Set mock config (for testing without live Laravel instance)
 */
function setMockConfig(cfg) {
  mockConfig = cfg;
}

/**
 * Set mock HTTP handler for Meta Graph API calls (for testing without external network)
 */
function setMockHttpHandler(handler) {
  mockHttpHandler = handler;
}

function resetMockHttpHandler() {
  mockHttpHandler = null;
}

/**
 * Fetch authoritative Meta tracking configuration from protected Laravel internal bridge.
 */
async function fetchMetaConfig(forceRefresh = false) {
  if (mockConfig) {
    return mockConfig;
  }

  const now = Date.now();
  if (!forceRefresh && cachedConfig && now < configCacheExpiry) {
    return cachedConfig;
  }

  return new Promise((resolve) => {
    try {
      const configUrl = new URL('/api/internal/meta-tracking-config', LARAVEL_INTERNAL_BASE_URL);
      const isHttps = configUrl.protocol === 'https:';
      const client = isHttps ? https : http;

      const req = client.request({
        hostname: configUrl.hostname,
        port: configUrl.port || (isHttps ? 443 : 80),
        path: configUrl.pathname,
        method: 'GET',
        timeout: 3000,
        headers: {
          'Accept': 'application/json',
          'X-Internal-Secret': INTERNAL_API_SECRET
        }
      }, (res) => {
        let body = '';
        res.on('data', (chunk) => body += chunk);
        res.on('end', () => {
          if (res.statusCode === 200) {
            try {
              const data = JSON.parse(body);
              if (data && data.success) {
                cachedConfig = data;
                configCacheExpiry = Date.now() + CONFIG_CACHE_TTL_MS;
                return resolve(data);
              }
            } catch (e) {}
          }
          // On non-200 or parse error, fall back to cached config or default disabled
          resolve(cachedConfig || { success: false, is_enabled: false });
        });
      });

      req.on('error', (err) => {
        // Fail-open: return cached config if available
        resolve(cachedConfig || { success: false, is_enabled: false });
      });

      req.on('timeout', () => {
        req.destroy();
        resolve(cachedConfig || { success: false, is_enabled: false });
      });

      req.end();
    } catch (err) {
      resolve(cachedConfig || { success: false, is_enabled: false });
    }
  });
}

/**
 * Check if a string is already a 64-character lowercase hexadecimal SHA-256 hash.
 */
function isSha256(val) {
  if (typeof val !== 'string') return false;
  return /^[a-f0-9]{64}$/i.test(val.trim());
}

/**
 * Deterministic one-time SHA-256 hashing.
 * If already hashed, preserves it to prevent duplicate hashing.
 */
function hashSha256(val) {
  if (!val || typeof val !== 'string') return null;
  const trimmed = val.trim();
  if (!trimmed) return null;
  if (isSha256(trimmed)) return trimmed.toLowerCase();
  return crypto.createHash('sha256').update(trimmed).digest('hex');
}

/**
 * Normalize an email address:
 * - Trim, lowercase, validate standard format
 */
function normalizeEmail(email) {
  if (!email || typeof email !== 'string') return null;
  const trimmed = email.trim().toLowerCase();
  if (!trimmed || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
    return null;
  }
  return trimmed;
}

/**
 * Normalize phone number:
 * - Remove spaces, dashes, plus signs, brackets
 * - Preserves digits cleanly without altering the number (minimum 7 digits)
 */
function normalizePhone(phone) {
  if (!phone || typeof phone !== 'string') return null;
  const trimmed = phone.trim();
  if (!trimmed) return null;
  if (isSha256(trimmed)) return trimmed.toLowerCase();

  const digits = trimmed.replace(/\D+/g, '');
  if (digits.length < 7) {
    return null;
  }
  return digits;
}

/**
 * Normalize first or last name:
 * - Trim, lowercase, strip punctuation, collapse whitespace
 */
function normalizeName(name) {
  if (!name || typeof name !== 'string') return null;
  const trimmed = name.trim();
  if (!trimmed) return null;
  if (isSha256(trimmed)) return trimmed.toLowerCase();

  let clean = trimmed.toLowerCase();
  // Strip punctuation while preserving letters, numbers, and spaces
  clean = clean.replace(/[^\p{L}\p{N}\s]/gu, '');
  clean = clean.replace(/\s+/g, ' ').trim();
  return clean.length > 0 ? clean : null;
}

/**
 * Normalize city:
 * - Lowercase, trimmed, recognizes 'dhaka' / 'inside' delivery areas
 */
function normalizeCity(city) {
  if (!city || typeof city !== 'string') return null;
  const trimmed = city.trim();
  if (!trimmed) return null;
  if (isSha256(trimmed)) return trimmed.toLowerCase();

  const clean = trimmed.toLowerCase();
  if (clean.includes('dhaka') || clean === 'inside') {
    return 'dhaka';
  }
  const stripped = clean.replace(/[^\p{L}\p{N}\s]/gu, '').replace(/\s+/g, ' ').trim();
  return stripped.length > 0 ? stripped : null;
}

/**
 * Normalize country:
 * - 2-letter ISO lowercase (e.g. 'bd')
 */
function normalizeCountry(country) {
  if (!country || typeof country !== 'string') return null;
  const trimmed = country.trim();
  if (!trimmed) return null;
  if (isSha256(trimmed)) return trimmed.toLowerCase();

  const clean = trimmed.toLowerCase();
  if (clean === 'bangladesh') return 'bd';
  const alphaOnly = clean.replace(/[^a-z]/g, '');
  if (alphaOnly.length === 2) return alphaOnly;
  return alphaOnly.length > 0 ? alphaOnly.slice(0, 2) : null;
}

/**
 * Normalize external ID:
 * - Stable non-secret business identifier (e.g. order number)
 * - Explicitly rejects emails, phone numbers, and secrets/tokens
 */
function normalizeExternalId(extId) {
  if (!extId || typeof extId !== 'string') return null;
  const trimmed = extId.trim();
  if (!trimmed) return null;
  if (isSha256(trimmed)) return trimmed.toLowerCase();

  if (trimmed.includes('@')) return null;
  if (/^(\+?88)?01[3-9]\d{8}$/.test(trimmed)) return null;
  if (/(?:EAAG|Bearer|token|secret|password)/i.test(trimmed)) return null;

  return trimmed.toLowerCase();
}

/**
 * Build structured CAPI user_data array according to Meta specifications.
 * Hashed fields: em, ph, fn, ln, ct, country, external_id
 * Raw fields: client_ip_address, client_user_agent, fbp, fbc
 */
function buildUserData(raw = {}) {
  const userData = {};

  // 1. Email (em)
  const rawEmail = raw.email || raw.em;
  const normEmail = normalizeEmail(rawEmail);
  if (normEmail) {
    const h = hashSha256(normEmail);
    if (h) userData.em = [h];
  }

  // 2. Phone (ph)
  const rawPhone = raw.phone || raw.ph;
  const normPhone = normalizePhone(rawPhone);
  if (normPhone) {
    const h = hashSha256(normPhone);
    if (h) userData.ph = [h];
  }

  // 3. Names (fn, ln)
  let rawFn = raw.first_name || raw.fn;
  let rawLn = raw.last_name || raw.ln;

  if (!rawFn && (raw.customer_name || raw.name)) {
    const full = String(raw.customer_name || raw.name).trim();
    const parts = full.split(/\s+/);
    rawFn = parts[0] || null;
    if (!rawLn && parts.length > 1) {
      rawLn = parts.slice(1).join(' ');
    }
  }

  const normFn = normalizeName(rawFn);
  if (normFn) {
    const h = hashSha256(normFn);
    if (h) userData.fn = [h];
  }

  const normLn = normalizeName(rawLn);
  if (normLn) {
    const h = hashSha256(normLn);
    if (h) userData.ln = [h];
  }

  // 4. City (ct)
  const rawCity = raw.city || raw.ct;
  const normCity = normalizeCity(rawCity);
  if (normCity) {
    const h = hashSha256(normCity);
    if (h) userData.ct = [h];
  }

  // 5. Country (country)
  const rawCountry = raw.country;
  const normCountry = normalizeCountry(rawCountry);
  if (normCountry) {
    const h = hashSha256(normCountry);
    if (h) userData.country = [h];
  }

  // 6. External ID (external_id)
  const rawExtId = raw.external_id;
  const normExtId = normalizeExternalId(rawExtId);
  if (normExtId) {
    const h = hashSha256(normExtId);
    if (h) userData.external_id = [h];
  }

  // 7. Non-Hashed Identifiers: IP Address
  const ip = raw.client_ip_address;
  if (ip && typeof ip === 'string' && ip.trim()) {
    const cleanIp = ip.trim();
    // Basic IP validation (IPv4 or IPv6)
    if (/^(\d{1,3}\.){3}\d{1,3}$/.test(cleanIp) || cleanIp.includes(':')) {
      userData.client_ip_address = cleanIp;
    }
  }

  // 8. Non-Hashed Identifiers: User Agent
  const ua = raw.client_user_agent;
  if (ua && typeof ua === 'string' && ua.trim()) {
    userData.client_user_agent = ua.trim();
  }

  // 9. Non-Hashed Identifiers: Meta Browser ID (_fbp)
  const fbp = raw.fbp;
  if (fbp && typeof fbp === 'string' && fbp.trim()) {
    userData.fbp = fbp.trim();
  }

  // 10. Non-Hashed Identifiers: Meta Click ID (_fbc)
  const fbc = raw.fbc;
  if (fbc && typeof fbc === 'string' && fbc.trim()) {
    userData.fbc = fbc.trim();
  }

  return userData;
}

/**
 * Dispatch an event to Meta Conversions API.
 * 
 * @param {Object} eventData - { event_name, event_id, event_source_url, user_data, custom_data }
 * @returns {Promise<Object>} { success, event_id, events_received?, error?, is_replay? }
 */
async function sendEvent(eventData = {}) {
  const eventName = eventData.event_name;
  const eventId = eventData.event_id;

  if (!eventName || !ALLOWED_EVENTS.includes(eventName)) {
    return {
      success: false,
      error: `Unsupported or invalid event_name: '${eventName}'`,
      event_id: eventId || null
    };
  }

  if (!eventId || typeof eventId !== 'string' || eventId.trim() === '') {
    return {
      success: false,
      error: 'Missing required event_id for server event deduplication.',
      event_id: null
    };
  }

  // 1. Resolve runtime Meta Tracking configuration from Laravel bridge
  const config = await fetchMetaConfig();
  if (!config || !config.is_enabled) {
    return {
      success: true,
      skipped: true,
      reason: 'Tracking globally disabled',
      event_id: eventId
    };
  }

  const pixelId = config.active_pixel_id;
  const accessToken = config.access_token;
  const testEventCode = config.test_event_code;

  if (!pixelId || !accessToken) {
    return {
      success: false,
      error: 'Active Meta Pixel or CAPI token not configured in runtime settings.',
      event_id: eventId
    };
  }

  // Check event toggle if specified in runtime config
  const serverEvents = config.server_events || {};
  const toggleKey = eventName.replace(/([a-z])([A-Z])/g, '$1_$2').toLowerCase();
  if (serverEvents[toggleKey] === false || serverEvents[eventName.toLowerCase()] === false) {
    return {
      success: true,
      skipped: true,
      reason: `Server event '${eventName}' is disabled in settings`,
      event_id: eventId
    };
  }

  // Phase 8: Purchase Event Control (Single Authoritative Queue in Laravel)
  if (eventName === 'Purchase') {
    const purchaseMode = config.purchase_event_mode || 'instant';
    if (purchaseMode !== 'instant') {
      return {
        success: true,
        deferred: true,
        purchase_mode: purchaseMode,
        reason: `Purchase event dispatch deferred to Laravel authoritative queue (mode: ${purchaseMode})`,
        event_id: eventId
      };
    }
  }

  // 2. Persistent Idempotency Check in SQLite
  try {
    const existing = findMetaCapiEvent(pixelId, eventName, eventId);
    if (existing && existing.status === 'sent') {
      return {
        success: true,
        is_replay: true,
        duplicate: true,
        event_id: eventId
      };
    }
  } catch (dbErr) {
    // If SQLite check fails, proceed fail-open
  }

  // 3. Construct CAPI payload
  const preparedUserData = buildUserData(eventData.user_data || {});
  const customData = eventData.custom_data && typeof eventData.custom_data === 'object' ? eventData.custom_data : {};

  const payloadObject = {
    event_name: eventName,
    event_time: Math.floor(Date.now() / 1000),
    event_id: eventId,
    event_source_url: eventData.event_source_url || 'https://growthagro.shop/',
    action_source: 'website',
    user_data: preparedUserData,
    custom_data: customData
  };

  const postData = {
    data: [payloadObject]
  };

  if (testEventCode) {
    postData.test_event_code = testEventCode;
  }

  // 4. Dispatch to Meta Graph API v20.0 (or mock handler in test mode)
  if (typeof mockHttpHandler === 'function') {
    try {
      const mockRes = await mockHttpHandler({
        pixel_id: pixelId,
        event_name: eventName,
        event_id: eventId,
        payload: postData
      });

      if (mockRes && mockRes.success) {
        try {
          recordMetaCapiEvent(pixelId, eventName, eventId, 'sent');
        } catch (e) {}
        return {
          success: true,
          event_id: eventId,
          events_received: mockRes.events_received || 1,
          fbtrace_id: mockRes.fbtrace_id || 'mock_trace_123'
        };
      } else {
        const errMsg = mockRes?.error || 'Mock Meta CAPI error';
        try {
          recordMetaCapiEvent(pixelId, eventName, eventId, 'failed', errMsg);
        } catch (e) {}
        return {
          success: false,
          event_id: eventId,
          error: errMsg
        };
      }
    } catch (mockErr) {
      try {
        recordMetaCapiEvent(pixelId, eventName, eventId, 'failed', mockErr.message);
      } catch (e) {}
      return {
        success: false,
        event_id: eventId,
        error: mockErr.message
      };
    }
  }

  // Live Meta Graph API Request
  return new Promise((resolve) => {
    const postJson = JSON.stringify(postData);
    const metaPath = `/v20.0/${pixelId}/events`;

    const req = https.request({
      hostname: 'graph.facebook.com',
      port: 443,
      path: metaPath,
      method: 'POST',
      timeout: 5000,
      headers: {
        'Content-Type': 'application/json',
        'Content-Length': Buffer.byteLength(postJson),
        'Authorization': `Bearer ${accessToken}`
      }
    }, (res) => {
      let resBody = '';
      res.on('data', (chunk) => resBody += chunk);
      res.on('end', () => {
        let parsed = {};
        try {
          parsed = JSON.parse(resBody);
        } catch (e) {
          parsed = { raw: resBody };
        }

        if (res.statusCode >= 200 && res.statusCode < 300) {
          try {
            recordMetaCapiEvent(pixelId, eventName, eventId, 'sent');
          } catch (e) {}
          resolve({
            success: true,
            event_id: eventId,
            events_received: parsed.events_received || 1,
            fbtrace_id: parsed.fbtrace_id
          });
        } else {
          const sanitizedErr = parsed.error ? (parsed.error.message || 'Meta API returned error') : `HTTP ${res.statusCode}`;
          try {
            recordMetaCapiEvent(pixelId, eventName, eventId, 'failed', sanitizedErr);
          } catch (e) {}
          resolve({
            success: false,
            event_id: eventId,
            error: sanitizedErr,
            status_code: res.statusCode
          });
        }
      });
    });

    req.on('error', (err) => {
      const sanitizedErr = err.message || 'Network error';
      try {
        recordMetaCapiEvent(pixelId, eventName, eventId, 'failed', sanitizedErr);
      } catch (e) {}
      resolve({
        success: false,
        event_id: eventId,
        error: sanitizedErr
      });
    });

    req.on('timeout', () => {
      req.destroy();
      try {
        recordMetaCapiEvent(pixelId, eventName, eventId, 'failed', 'Request timed out');
      } catch (e) {}
      resolve({
        success: false,
        event_id: eventId,
        error: 'Meta CAPI request timed out'
      });
    });

    req.write(postJson);
    req.end();
  });
}

module.exports = {
  fetchMetaConfig,
  invalidateConfigCache,
  setMockConfig,
  setMockHttpHandler,
  resetMockHttpHandler,
  buildUserData,
  normalizeEmail,
  normalizePhone,
  normalizeName,
  normalizeCity,
  normalizeCountry,
  normalizeExternalId,
  isSha256,
  hashSha256,
  sendEvent,
  ALLOWED_EVENTS
};
