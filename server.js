const http = require('http');
const https = require('https');
const fs = require('fs');
const path = require('path');
const { calculateOrderTotals } = require('./server/products');
const { createOrder, listOrders, getOrderByNumber, updateOrderStatus, findDuplicateOrder, ALLOWED_ORDER_STATUSES } = require('./server/db');
const { authenticateAdmin, verifyAdminToken } = require('./server/auth');
const { getHealthStatus } = require('./server/health');
const { validateBdPhone, checkCourierRateLimit, checkBdCourier } = require('./server/courier');

const PORT = parseInt(process.env.PORT, 10) || 3000;
const HOST = process.env.HOST || '127.0.0.1';
const IS_PROD = process.env.NODE_ENV === 'production';

// Internal API secret shared between Laravel (port 8000) and Node.js (port 3000)
// Laravel must send this in X-Internal-Secret header for /api/internal/* routes
const INTERNAL_API_SECRET = process.env.INTERNAL_API_SECRET || 'baby-fashion-internal-2024-secret';

const MIME_TYPES = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.webp': 'image/webp',
  '.svg': 'image/svg+xml',
  '.woff2': 'font/woff2',
  '.woff': 'font/woff',
  '.ico': 'image/x-icon'
};

// Blocked private directories and patterns that must NEVER be served statically
const BLOCKED_STATIC_PREFIXES = ['/data', '/server', '/scripts', '/logs', '/node_modules', '/.env', '/.git'];

// In-Memory Rate Limiter (Lightweight, zero-dependency)
const rateLimitMap = new Map();
const RATE_LIMIT_CLEANUP_MS = 60 * 1000; // 1 minute
const RATE_LIMIT_MAX_ORDERS = parseInt(process.env.RATE_LIMIT_ORDERS || '120', 10);
const RATE_LIMIT_MAX_LOGIN = parseInt(process.env.RATE_LIMIT_LOGIN || '30', 10);

setInterval(() => {
  const now = Date.now();
  for (const [key, record] of rateLimitMap.entries()) {
    if (now > record.resetTime) {
      rateLimitMap.delete(key);
    }
  }
}, RATE_LIMIT_CLEANUP_MS).unref();

function checkRateLimit(ip, routeKey, maxRequests = 30, windowMs = 60000) {
  if (process.env.DISABLE_RATE_LIMIT === 'true' || ip === '127.0.0.1' || ip === '::1' || ip === 'localhost') {
    return { allowed: true, remaining: 999 };
  }

  const key = `${ip}:${routeKey}`;
  const now = Date.now();
  let record = rateLimitMap.get(key);

  if (!record || now > record.resetTime) {
    record = { count: 1, resetTime: now + windowMs };
    rateLimitMap.set(key, record);
    return { allowed: true, remaining: maxRequests - 1 };
  }

  record.count++;
  if (record.count > maxRequests) {
    return { allowed: false, remaining: 0, retryAfterSec: Math.ceil((record.resetTime - now) / 1000) };
  }

  return { allowed: true, remaining: maxRequests - record.count };
}

function getClientIp(req) {
  const xForwardedFor = req.headers['x-forwarded-for'];
  if (xForwardedFor) {
    return xForwardedFor.split(',')[0].trim();
  }
  return req.socket.remoteAddress || '127.0.0.1';
}

/**
 * Apply Standard Security Headers
 */
function setSecurityHeaders(res) {
  res.setHeader('X-Content-Type-Options', 'nosniff');
  res.setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
  res.setHeader('X-Frame-Options', 'SAMEORIGIN'); // Allows Admin preview iframe, prevents external clickjacking
  res.setHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
  
  if (IS_PROD) {
    res.setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
  }
}

/**
 * Helper to parse JSON request body with 64KB limit
 */
function parseJsonBody(req) {
  return new Promise((resolve, reject) => {
    let body = '';
    const maxBytes = 64 * 1024; // 64 KB

    req.on('data', chunk => {
      body += chunk;
      if (body.length > maxBytes) {
        reject(new Error('Payload too large'));
      }
    });

    req.on('end', () => {
      if (!body.trim()) {
        resolve({});
        return;
      }
      try {
        const json = JSON.parse(body);
        resolve(json);
      } catch (err) {
        reject(new Error('Invalid JSON'));
      }
    });

    req.on('error', reject);
  });
}

/**
 * Helper to send JSON response with security headers
 */
function sendJson(res, statusCode, data) {
  setSecurityHeaders(res);
  res.writeHead(statusCode, {
    'Content-Type': 'application/json; charset=utf-8',
    'Cache-Control': 'no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0'
  });
  res.end(JSON.stringify(data));
}

/**
 * Validate 11-digit Bangladeshi mobile number
 */
function isValidBdPhone(phone) {
  if (typeof phone !== 'string') return false;
  const clean = phone.replace(/[\s\-]/g, '');
  return /^(?:\+88|88)?(01[3-9]\d{8})$/.test(clean);
}

function normalizeBdPhone(phone) {
  const clean = phone.replace(/[\s\-]/g, '');
  const match = clean.match(/(01[3-9]\d{8})$/);
  return match ? match[1] : clean;
}

const server = http.createServer(async (req, res) => {
  const parsedUrl = new URL(req.url, `http://${req.headers.host || 'localhost:3000'}`);
  const reqPath = parsedUrl.pathname;
  const method = req.method.toUpperCase();
  const clientIp = getClientIp(req);

  // Apply security headers
  setSecurityHeaders(res);

  // CORS: Allow Laravel (port 8000) to call internal APIs
  const origin = req.headers['origin'] || '';
  if (origin === 'http://127.0.0.1:8000' || origin === 'http://localhost:8000') {
    res.setHeader('Access-Control-Allow-Origin', origin);
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, X-Internal-Secret');
    res.setHeader('Access-Control-Max-Age', '86400');
  }
  if (method === 'OPTIONS') {
    res.writeHead(204);
    return res.end();
  }

  // ==========================================
  // API ROUTING (/api/...)
  // ==========================================
  if (reqPath.startsWith('/api/')) {
    // 0. System Health Check (GET /api/health)
    if (reqPath === '/api/health' && method === 'GET') {
      const health = getHealthStatus();
      const statusCode = health.status === 'ok' ? 200 : 503;
      return sendJson(res, statusCode, health);
    }

    // 1. Admin Login (POST /api/auth/login) - Rate limited
    if (reqPath === '/api/auth/login' && method === 'POST') {
      const limit = checkRateLimit(clientIp, 'auth_login', RATE_LIMIT_MAX_LOGIN, 60000);
      if (!limit.allowed) {
        return sendJson(res, 429, {
          success: false,
          error: `Too many login attempts. Please try again in ${limit.retryAfterSec} seconds.`
        });
      }

      try {
        const body = await parseJsonBody(req);
        const auth = authenticateAdmin(body.email, body.password);
        if (!auth) {
          return sendJson(res, 401, { success: false, error: 'Invalid admin credentials' });
        }
        return sendJson(res, 200, { success: true, ...auth });
      } catch (err) {
        return sendJson(res, 400, { success: false, error: err.message });
      }
    }

    // 2. Public Order Submission (POST /api/orders) - Rate limited
    if (reqPath === '/api/orders' && method === 'POST') {
      const limit = checkRateLimit(clientIp, 'create_order', RATE_LIMIT_MAX_ORDERS, 60000);
      if (!limit.allowed) {
        return sendJson(res, 429, {
          success: false,
          error: `খুব বেশি অনুরোধ এসেছে। অনুগ্রহ করে ${limit.retryAfterSec} সেকেন্ড পর আবার চেষ্টা করুন।`
        });
      }

      try {
        const body = await parseJsonBody(req);

        // Normalize customer payload (supports both nested { customer: {...} } and flat fields)
        const customer = body.customer || {};
        const customerName = (customer.name || body.customerName || body.name || body.customer_name || '').trim();
        const rawPhone = (customer.phone || body.customerPhone || body.phone || body.customer_phone || '').trim();
        const address = (customer.address || body.customerAddress || body.address || body.shipping_address || '').trim();
        const deliveryZone = (customer.delivery_zone || body.deliveryZone || body.delivery_zone || (body.shipping_city === 'Dhaka' ? 'inside' : 'outside')).trim();
        const productId = (body.productId || body.product_id || 'chicken-booster').trim();
        const variantId = (body.variantId || body.variant_id || body.package_id || 'variant-2').trim();
        const quantity = parseInt(body.quantity || 1, 10);
        const idempotencyKey = body.idempotency_key ? String(body.idempotency_key).trim() : null;

        // Server-Side Validation
        const validationErrors = [];
        if (!customerName || customerName.length < 2 || customerName.length > 100) {
          validationErrors.push('আপনার পুরো নাম লিখুন (২ থেকে ১০০ অক্ষরের মধ্যে)');
        }

        if (!isValidBdPhone(rawPhone)) {
          validationErrors.push('সঠিক ১১ ডিজিটের মোবাইল নাম্বার লিখুন (যেমন: 017XXXXXXXX)');
        }

        if (!address || address.length < 5 || address.length > 500) {
          validationErrors.push('আপনার বিস্তারিত ঠিকানা লিখুন (যেমন: গ্রাম, থানা, জেলা)');
        }

        if (isNaN(quantity) || quantity < 1 || quantity > 50) {
          validationErrors.push('অর্ডারের পরিমাণ ১ থেকে ৫০ এর মধ্যে হতে হবে');
        }

        if (validationErrors.length > 0) {
          return sendJson(res, 400, {
            success: false,
            error: validationErrors[0],
            errors: validationErrors
          });
        }

        const phone = normalizeBdPhone(rawPhone);

        // Resolve authoritative server-side pricing & product data (NEVER trust client price)
        let calculated;
        try {
          calculated = calculateOrderTotals(productId, variantId, quantity, deliveryZone);
        } catch (err) {
          return sendJson(res, 400, { success: false, error: err.message });
        }

        // Server-Side Deduplication / Idempotency Check
        const existingOrder = findDuplicateOrder(phone, productId, variantId, idempotencyKey);
        if (existingOrder) {
          return sendJson(res, 200, {
            success: true,
            is_replay: true,
            order: {
              order_number: existingOrder.order_number,
              status: existingOrder.status,
              product: existingOrder.product_name || calculated.product.shortName,
              variant: existingOrder.variant_name || calculated.variant.name,
              quantity: existingOrder.quantity || calculated.quantity,
              subtotal: existingOrder.subtotal,
              delivery_charge: existingOrder.delivery_charge,
              total: existingOrder.total,
              currency: existingOrder.currency,
              payment_method: existingOrder.payment_method
            }
          });
        }

        // Create Order in SQLite with atomic transaction
        const newOrder = createOrder({
          customerName,
          phone,
          address,
          productId: calculated.product.id,
          productName: calculated.product.shortName || calculated.product.name,
          variantId: calculated.variant.id,
          variantName: calculated.variant.name,
          quantity: calculated.quantity,
          unitPrice: calculated.unitPrice,
          subtotal: calculated.subtotal,
          deliveryZone: calculated.deliveryZone,
          deliveryCharge: calculated.deliveryCharge,
          total: calculated.total,
          currency: calculated.currency,
          idempotencyKey,
          landingPage: calculated.product.landingPage || '/products/chicken-booster/'
        });

        // Clean public response (no internal DB IDs, no sensitive PII exposure)
        return sendJson(res, 201, {
          success: true,
          order: {
            order_number: newOrder.order_number,
            status: newOrder.status,
            product: newOrder.product_name,
            variant: newOrder.variant_name,
            quantity: newOrder.quantity,
            subtotal: newOrder.subtotal,
            delivery_charge: newOrder.delivery_charge,
            total: newOrder.total,
            currency: newOrder.currency,
            payment_method: newOrder.payment_method,
            created_at: newOrder.created_at
          }
        });
      } catch (err) {
        console.error('[API Error: POST /api/orders]', err.message);
        return sendJson(res, 500, {
          success: false,
          error: 'সার্ভারে সাময়িক সমস্যা হয়েছে। কিছুক্ষণ পর আবার চেষ্টা করুন।'
        });
      }
    }

    // 3. Admin: List Orders (GET /api/orders) - Protected
    if (reqPath === '/api/orders' && method === 'GET') {
      const adminSession = verifyAdminToken(req);
      if (!adminSession) {
        return sendJson(res, 401, { success: false, error: 'Unauthorized: Admin authorization required' });
      }

      const limit = parseInt(parsedUrl.searchParams.get('limit') || '100', 10);
      const offset = parseInt(parsedUrl.searchParams.get('offset') || '0', 10);
      const orders = listOrders(limit, offset);

      return sendJson(res, 200, { success: true, count: orders.length, orders });
    }

    // 4. Admin: Get Single Order (GET /api/orders/:orderNumber) - Protected
    const singleOrderMatch = reqPath.match(/^\/api\/orders\/([A-Za-z0-9_\-]+)$/);
    if (singleOrderMatch && method === 'GET') {
      const adminSession = verifyAdminToken(req);
      if (!adminSession) {
        return sendJson(res, 401, { success: false, error: 'Unauthorized: Admin authorization required' });
      }

      const orderNumber = singleOrderMatch[1];
      const order = getOrderByNumber(orderNumber);
      if (!order) {
        return sendJson(res, 404, { success: false, error: 'Order not found' });
      }

      return sendJson(res, 200, { success: true, order });
    }

    // 5. Admin: Update Order Status (PATCH /api/orders/:orderNumber/status) - Protected
    const statusMatch = reqPath.match(/^\/api\/orders\/([A-Za-z0-9_\-]+)\/status$/);
    if (statusMatch && method === 'PATCH') {
      const adminSession = verifyAdminToken(req);
      if (!adminSession) {
        return sendJson(res, 401, { success: false, error: 'Unauthorized: Admin authorization required' });
      }

      const orderNumber = statusMatch[1];
      try {
        const body = await parseJsonBody(req);
        const newStatus = body.status;
        if (!newStatus || !ALLOWED_ORDER_STATUSES.includes(newStatus.toLowerCase())) {
          return sendJson(res, 400, {
            success: false,
            error: `Invalid status: ${newStatus}. Allowed: ${ALLOWED_ORDER_STATUSES.join(', ')}`
          });
        }

        const updated = updateOrderStatus(orderNumber, newStatus);
        if (!updated) {
          return sendJson(res, 404, { success: false, error: 'Order not found' });
        }

        return sendJson(res, 200, { success: true, order_number: orderNumber, status: newStatus.toLowerCase() });
      } catch (err) {
        return sendJson(res, 400, { success: false, error: err.message });
      }
    }

    // 6. Admin: BD Courier Customer Fraud & Delivery Ratio Check (POST /api/courier/check)
    if (reqPath === '/api/courier/check' && method === 'POST') {
      const adminSession = verifyAdminToken(req);
      if (!adminSession) {
        return sendJson(res, 401, { success: false, error: 'Unauthorized: Admin authorization required' });
      }

      // Rate limit per IP
      const rateLimit = checkCourierRateLimit(clientIp);
      if (!rateLimit.allowed) {
        return sendJson(res, 429, {
          success: false,
          error: `Too many courier checks. Please try again in ${rateLimit.retryAfterSec} seconds.`
        });
      }

      try {
        const body = await parseJsonBody(req);
        const rawPhone = body && typeof body === 'object' ? body.phone : null;
        const validation = validateBdPhone(rawPhone);

        if (!validation.valid) {
          return sendJson(res, 400, {
            success: false,
            error: validation.error
          });
        }

        // Perform timeout-protected and normalized BD Courier check
        const courierResult = await checkBdCourier(validation.normalized);
        const statusCode = courierResult.success ? 200 : (courierResult.status_code || 200);

        return sendJson(res, statusCode, courierResult);
      } catch (err) {
        return sendJson(res, 400, { success: false, error: 'Invalid request payload.' });
      }
    }

    // ==========================================
    // INTERNAL APIs — Laravel E-Commerce Baby sync (secret key auth, no admin token needed)
    // ==========================================

    // 7. Internal: Fraud Check for Checkout (POST /api/internal/courier-check)
    // Called by Laravel during checkout — no browser session needed
    if (reqPath === '/api/internal/courier-check' && method === 'POST') {
      const incomingSecret = req.headers['x-internal-secret'];
      if (incomingSecret !== INTERNAL_API_SECRET) {
        return sendJson(res, 403, { success: false, error: 'Forbidden: Invalid internal secret.' });
      }

      const rateLimit = checkCourierRateLimit(clientIp);
      if (!rateLimit.allowed) {
        // Fail-open: allow COD if rate limit hit
        return sendJson(res, 200, {
          success: true,
          rate_limited: true,
          payment_decision: 'cod',
          message: 'Rate limit reached — defaulting to COD (fail-open).'
        });
      }

      try {
        const body = await parseJsonBody(req);
        const rawPhone = body && typeof body === 'object' ? body.phone : null;
        const validation = validateBdPhone(rawPhone);

        if (!validation.valid) {
          // Fail-open: allow COD on invalid phone
          return sendJson(res, 200, {
            success: true,
            phone_invalid: true,
            payment_decision: 'cod',
            message: 'Phone validation failed — defaulting to COD.'
          });
        }

        const courierResult = await checkBdCourier(validation.normalized);

        if (!courierResult.success) {
          // Fail-open: courier API unavailable → allow COD
          return sendJson(res, 200, {
            success: true,
            courier_unavailable: true,
            payment_decision: 'cod',
            message: 'Courier API unavailable — defaulting to COD (fail-open).'
          });
        }

        const trustScore = courierResult.data.heuristic_trust_score;
        const successRate = courierResult.data.success_rate;
        const level = trustScore.level; // 'new_customer' | 'safe' | 'medium' | 'high_risk'

        // 80% Rule: < 60% success rate or high_risk → advance payment required
        const requiresAdvance = (level === 'high_risk');

        return sendJson(res, 200, {
          success: true,
          phone: courierResult.data.phone,
          success_rate: successRate,
          trust_level: level,
          trust_label: trustScore.label,
          total_parcels: courierResult.data.total_parcels,
          delivered: courierResult.data.delivered,
          cancelled: courierResult.data.cancelled_or_returned,
          payment_decision: requiresAdvance ? 'advance_required' : 'cod',
          payment_message: requiresAdvance
            ? `আপনার ডেলিভারি সাকসেস রেট ${successRate}% (< 80%)। অর্ডার নিশ্চিত করতে অগ্রিম পেমেন্ট করতে হবে।`
            : `আপনার ডেলিভারি সাকসেস রেট ভালো (${successRate}%)। ক্যাশ অন ডেলিভারিতে অর্ডার করতে পারবেন।`
        });
      } catch (err) {
        // Fail-open on any error
        return sendJson(res, 200, {
          success: true,
          error: true,
          payment_decision: 'cod',
          message: 'Internal error during fraud check — defaulting to COD.'
        });
      }
    }

    // 8. Internal: Sync Order from Laravel to Node.js SQLite (POST /api/internal/sync-order)
    if (reqPath === '/api/internal/sync-order' && method === 'POST') {
      const incomingSecret = req.headers['x-internal-secret'];
      if (incomingSecret !== INTERNAL_API_SECRET) {
        return sendJson(res, 403, { success: false, error: 'Forbidden: Invalid internal secret.' });
      }

      try {
        const body = await parseJsonBody(req);

        const customerName = (body.customer_name || '').trim();
        const rawPhone    = (body.customer_phone || '').trim();
        const address     = (body.customer_address || '').trim();
        const deliveryZone = body.delivery_area === 'inside_dhaka' ? 'inside' : 'outside';
        const orderNumber  = (body.order_number || '').trim();
        const subtotal     = parseFloat(body.subtotal) || 0;
        const shipping     = parseFloat(body.delivery_charge) || 0;
        const total        = parseFloat(body.total_amount) || 0;
        const paymentMethod = body.payment_method || 'COD';
        const notes        = body.note || '';
        const source       = body.source || 'baby-fashion-storefront';
        const fraudLevel   = body.fraud_level || 'unknown';
        const itemsJson    = body.items ? JSON.stringify(body.items) : '[]';

        // Build a product name from items array
        const items = Array.isArray(body.items) ? body.items : [];
        const productName = items.length > 0
          ? items.map(i => i.product_name || i.title || 'Baby Outfit').join(', ')
          : 'Baby Fashion BD Order';

        const phone = normalizeBdPhone(rawPhone) || rawPhone;

        const newOrder = createOrder({
          customerName,
          phone,
          address,
          productId: 'baby-fashion',
          productName,
          variantId: 'baby-order',
          variantName: items.length > 0 ? (items[0].size || 'Standard') : 'Standard',
          quantity: items.reduce((sum, i) => sum + (parseInt(i.quantity) || 1), 0) || 1,
          unitPrice: subtotal,
          subtotal,
          deliveryZone,
          deliveryCharge: shipping,
          total,
          currency: 'BDT',
          idempotencyKey: orderNumber,
          landingPage: 'http://127.0.0.1:8000/',
          notes,
          source,
          paymentMethod,
          fraudLevel
        });

        return sendJson(res, 201, {
          success: true,
          synced: true,
          order_number: newOrder.order_number,
          status: newOrder.status
        });
      } catch (err) {
        console.error('[Internal Sync Error]', err.message);
        return sendJson(res, 500, { success: false, error: 'Order sync failed: ' + err.message });
      }
    }

    // Unmatched API route
    return sendJson(res, 404, { success: false, error: 'API route not found' });
  }

  // ==========================================
  // STATIC FILE SECURITY & ROUTING
  // ==========================================
  let localReqPath = reqPath;

  // Security: Block any attempts to download private files, sqlite database, .env, or server files
  const lowerPath = localReqPath.toLowerCase();
  for (const prefix of BLOCKED_STATIC_PREFIXES) {
    if (lowerPath.startsWith(prefix)) {
      res.writeHead(403, { 'Content-Type': 'application/json' });
      return res.end(JSON.stringify({ success: false, error: 'Access Denied' }));
    }
  }


  let filePath = path.join(__dirname, decodeURIComponent(localReqPath));

  // If path is a directory, look for index.html inside
  if (fs.existsSync(filePath) && fs.statSync(filePath).isDirectory()) {
    filePath = path.join(filePath, 'index.html');
  }

  if (fs.existsSync(filePath) && fs.statSync(filePath).isFile()) {
    const ext = path.extname(filePath).toLowerCase();
    const contentType = MIME_TYPES[ext] || 'application/octet-stream';
    
    // Set appropriate cache control
    const isAsset = lowerPath.startsWith('/assets/') || ext === '.webp' || ext === '.woff2';
    const cacheControl = isAsset 
      ? 'public, max-age=2592000, immutable' 
      : 'no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0';

    res.writeHead(200, {
      'Content-Type': contentType,
      'Cache-Control': cacheControl
    });
    fs.createReadStream(filePath).pipe(res);
  } else {
    res.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' });
    res.end('404 Not Found');
  }
});

server.listen(PORT, HOST, () => {
  console.log(`[Server] Production Node server listening on http://${HOST}:${PORT} (Env: ${process.env.NODE_ENV || 'development'})`);
});

// Graceful Shutdown for PM2 / CloudPanel
function handleShutdown(signal) {
  console.log(`[Server] Received ${signal}. Closing server gracefully...`);
  server.close(() => {
    console.log('[Server] HTTP connections closed. Process exiting.');
    process.exit(0);
  });
  
  // Force exit if connections do not close in 5s
  setTimeout(() => {
    console.error('[Server] Forced shutdown after timeout.');
    process.exit(1);
  }, 5000);
}

process.on('SIGTERM', () => handleShutdown('SIGTERM'));
process.on('SIGINT', () => handleShutdown('SIGINT'));
