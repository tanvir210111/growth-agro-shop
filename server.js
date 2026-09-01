const http = require('http');
const https = require('https');
const fs = require('fs');
const path = require('path');

// Load environment variables from Laravel .env if available
function loadLocalEnv() {
  const envPaths = [
    path.join(__dirname, 'E Commerce Baby', '.env'),
    path.join(__dirname, '.env')
  ];
  for (const envPath of envPaths) {
    if (fs.existsSync(envPath)) {
      try {
        const content = fs.readFileSync(envPath, 'utf8');
        for (const line of content.split(/\r?\n/)) {
          const trimmed = line.trim();
          if (!trimmed || trimmed.startsWith('#')) continue;
          const idx = trimmed.indexOf('=');
          if (idx > 0) {
            const k = trimmed.slice(0, idx).trim();
            let v = trimmed.slice(idx + 1).trim();
            if ((v.startsWith('"') && v.endsWith('"')) || (v.startsWith("'") && v.endsWith("'"))) {
              v = v.slice(1, -1);
            }
            if (!process.env[k]) {
              process.env[k] = v;
            }
          }
        }
      } catch (e) {}
    }
  }
}
loadLocalEnv();

const { spawn } = require('child_process');
const { calculateOrderTotals } = require('./server/products');
const { createOrder, listOrders, getOrderByNumber, updateOrderStatus, findDuplicateOrder, ALLOWED_ORDER_STATUSES } = require('./server/db');
const { authenticateAdmin, verifyAdminToken } = require('./server/auth');
const { getHealthStatus } = require('./server/health');
const { validateBdPhone, checkCourierRateLimit, checkBdCourier, calculateDeliveryDecision } = require('./server/courier');

const PORT = parseInt(process.env.PORT, 10) || 3000;
const HOST = process.env.HOST || '127.0.0.1';
const IS_PROD = process.env.NODE_ENV === 'production';

const LARAVEL_PORT = parseInt(process.env.LARAVEL_PORT, 10) || 8000;
const LARAVEL_HOST = process.env.LARAVEL_HOST || '127.0.0.1';
let laravelProcess = null;

function isStorefrontRoute(pathname) {
  if (pathname === '/' || pathname === '') return true;
  // Allow landing page preview route through to Laravel
  if (pathname.startsWith('/admin/landing-pages/')) return true;
  // Exclude Landing Pages, Admin, APIs, and Node assets
  if (pathname.startsWith('/products/')) return false;
  if (pathname.startsWith('/admin')) return false;
  if (pathname.startsWith('/api/')) return false;
  if (pathname.startsWith('/assets/')) return false;
  if (pathname.startsWith('/pic/')) return false;
  if (pathname.startsWith('/extracted_html/')) return false;

  const storePrefixes = [
    '/shop',
    '/collections',
    '/product/',
    '/cart',
    '/checkout',
    '/order',
    '/about-us',
    '/contact-us',
    '/policy',
    '/css/',
    '/images/',
    '/js/',
    '/uploads/'
  ];
  return storePrefixes.some(p => pathname === p || pathname.startsWith(p));
}

function proxyToLaravel(req, res, reqPath, search) {
  return new Promise((resolve) => {
    const options = {
      hostname: LARAVEL_HOST,
      port: LARAVEL_PORT,
      path: reqPath + (search || ''),
      method: req.method,
      headers: {
        ...req.headers,
        host: `127.0.0.1:${LARAVEL_PORT}`,
        'x-forwarded-for': getClientIp(req),
        'x-forwarded-host': req.headers.host || `127.0.0.1:${PORT}`,
        'x-forwarded-proto': 'http'
      }
    };

    const proxyReq = http.request(options, (proxyRes) => {
      res.writeHead(proxyRes.statusCode, proxyRes.headers);
      proxyRes.pipe(res);
      proxyRes.on('end', () => resolve(true));
    });

    proxyReq.on('error', () => {
      resolve(false);
    });

    if (['POST', 'PUT', 'PATCH'].includes(req.method)) {
      req.pipe(proxyReq);
    } else {
      proxyReq.end();
    }
  });
}

function startLaravelServer() {
  const laravelDir = path.join(__dirname, 'E Commerce Baby');
  if (!fs.existsSync(laravelDir)) return;

  const testReq = http.request({ hostname: LARAVEL_HOST, port: LARAVEL_PORT, path: '/', method: 'HEAD', timeout: 800 }, () => {
    console.log(`[Laravel] E Commerce Baby is active on http://${LARAVEL_HOST}:${LARAVEL_PORT}`);
  });
  testReq.on('error', () => {
    console.log(`[Laravel] Auto-launching E Commerce Baby on http://${LARAVEL_HOST}:${LARAVEL_PORT}...`);
    try {
      laravelProcess = spawn('php', ['-S', `${LARAVEL_HOST}:${LARAVEL_PORT}`, '-t', 'public'], {
        cwd: laravelDir,
        stdio: 'ignore',
        detached: false
      });
      laravelProcess.on('error', (err) => {
        console.warn(`[Laravel] Could not start PHP server: ${err.message}`);
      });
    } catch (e) {
      console.warn(`[Laravel] Spawning PHP server failed: ${e.message}`);
    }
  });
  testReq.end();
}

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

    // 1.5. Public Fast Checkout Courier & Risk Check (POST /api/checkout/courier-check)
    if (reqPath === '/api/checkout/courier-check' && method === 'POST') {
      const limit = checkRateLimit(clientIp, 'checkout_courier_check', 30, 60000);
      if (!limit.allowed) {
        return sendJson(res, 429, {
          success: false,
          error: `অনুরোধের মাত্রা ছাড়িয়েছে। দয়া করে ${limit.retryAfterSec} সেকেন্ড অপেক্ষা করুন।`
        });
      }

      try {
        const body = await parseJsonBody(req);
        const rawPhone = body && typeof body === 'object' ? (body.phone || '') : '';
        const deliveryZone = body && typeof body === 'object' ? (body.deliveryZone || body.delivery_zone || 'inside') : 'inside';
        const validation = validateBdPhone(rawPhone);

        if (!validation.valid) {
          return sendJson(res, 400, { success: false, error: validation.error });
        }

        const phone = validation.normalized;
        let courierResult = null;
        try {
          courierResult = await checkBdCourier(phone);
        } catch (e) {
          courierResult = null;
        }

        let totalParcels = 0;
        let delivered = 0;
        let cancelled = 0;

        if (courierResult && courierResult.success && courierResult.data) {
          totalParcels = courierResult.data.total_parcels || 0;
          delivered = courierResult.data.delivered || 0;
          cancelled = courierResult.data.cancelled_or_returned || 0;
        }

        const decision = calculateDeliveryDecision(totalParcels, delivered, cancelled, deliveryZone);

        return sendJson(res, 200, {
          success: true,
          phone: phone.slice(0, 3) + '****' + phone.slice(-4),
          total_parcels: totalParcels,
          delivered: delivered,
          cancelled: cancelled,
          success_rate: decision.success_rate,
          risk_level: decision.level,
          risk_label: decision.label,
          requires_advance: decision.requires_advance,
          advance_amount: decision.advance_amount,
          advance_delivery_dhaka: decision.advance_delivery_dhaka,
          advance_delivery_outside: decision.advance_delivery_outside,
          delivery_charge: decision.delivery_charge,
          product_payment: decision.product_payment,
          payment_message: decision.message
        });
      } catch (err) {
        return sendJson(res, 500, { success: false, error: 'সার্ভার ভেরিফিকেশনে ত্রুটি হয়েছে।' });
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
        const address = (customer.address || body.customerAddress || body.shippingAddress || body.address || body.shipping_address || '').trim();
        const deliveryZone = (customer.delivery_zone || body.deliveryZone || body.delivery_zone || (body.shipping_city === 'Dhaka' ? 'inside' : 'outside')).trim();
        const productId = (body.productId || body.product_id || 'chicken-booster').trim();
        const variantId = (body.variantId || body.variant_id || body.package_id || 'variant-2').trim();
        const quantity = parseInt(body.quantity || 1, 10);
        const idempotencyKey = body.idempotency_key ? String(body.idempotency_key).trim() : null;
        const source = (body.source || 'LANDING_PAGE').trim();

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
          calculated = await calculateOrderTotals(productId, variantId, quantity, deliveryZone, body.items, {
            source,
            slug: body.slug || body.landingPage || body.landing_page || productId
          });
        } catch (err) {
          return sendJson(res, 400, { success: false, error: err.message });
        }

        // Evaluate customer risk via Courier check
        let fraudLevel = 'new_customer';
        let fraudScore = 100;
        let advanceAmount = 0;
        try {
          const courierCheck = await checkBdCourier(phone);
          if (courierCheck && courierCheck.success && courierCheck.data) {
            const dec = calculateDeliveryDecision(
              courierCheck.data.total_parcels,
              courierCheck.data.delivered,
              courierCheck.data.cancelled_or_returned,
              calculated.deliveryZone
            );
            fraudLevel = dec.level;
            fraudScore = dec.success_rate;
            advanceAmount = dec.advance_amount;
          }
        } catch (e) {}

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
              payment_method: existingOrder.payment_method,
              source: existingOrder.source,
              fraud_level: existingOrder.fraud_level,
              advance_amount: existingOrder.advance_amount
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
          landingPage: calculated.product.landingPage || (source === 'LANDING_PAGE' ? `/product/${calculated.product.id}` : '/products/chicken-booster/'),
          source,
          fraudLevel,
          fraudScore,
          advanceAmount,
          advancePaid: advanceAmount > 0 ? (body.advance_paid ? 1 : 0) : 0
        });

        // Server-to-Server Bridge: Synchronize landing page order to Laravel with attribution (Non-blocking fail-open)
        try {
          const cookieHeader = req.headers.cookie || '';
          const visitorMatch = cookieHeader.match(/growth_agro_visitor_id=([a-f0-9\-]+)/i);
          const sessionMatch = cookieHeader.match(/growth_agro_session_id=([a-f0-9\-]+)/i);
          const visitorUuid = visitorMatch ? visitorMatch[1] : null;
          const sessionUuid = sessionMatch ? sessionMatch[1] : null;

          const syncPayload = JSON.stringify({
            order_number: newOrder.order_number,
            customer_name: newOrder.customer_name,
            customer_phone: newOrder.phone,
            customer_address: newOrder.address,
            delivery_zone: newOrder.delivery_zone,
            delivery_charge: newOrder.delivery_charge,
            subtotal: newOrder.subtotal,
            total: newOrder.total,
            payment_method: newOrder.payment_method,
            product_name: newOrder.product_name,
            variant_name: newOrder.variant_name,
            quantity: newOrder.quantity,
            unit_price: newOrder.unit_price,
            landing_page: newOrder.landing_page,
            items: calculated.items || [],
            visitor_uuid: visitorUuid,
            session_uuid: sessionUuid,
            idempotency_key: newOrder.idempotency_key
          });

          const syncReq = http.request({
            hostname: LARAVEL_HOST,
            port: LARAVEL_PORT,
            path: '/api/internal/sync-landing-order',
            method: 'POST',
            timeout: 3000,
            headers: {
              'Content-Type': 'application/json',
              'Content-Length': Buffer.byteLength(syncPayload),
              'X-Internal-Secret': INTERNAL_API_SECRET,
              'Cookie': cookieHeader,
              'User-Agent': req.headers['user-agent'] || '',
              'Referer': req.headers.referer || '',
              'X-Forwarded-For': clientIp
            }
          }, (syncRes) => {
            syncRes.resume();
          });

          syncReq.on('error', (syncErr) => {
            console.warn('[Bridge] Laravel sync failed (fail-open):', syncErr.message);
          });
          syncReq.on('timeout', () => {
            syncReq.destroy();
            console.warn('[Bridge] Laravel sync timed out (fail-open)');
          });
          syncReq.write(syncPayload);
          syncReq.end();
        } catch (bridgeErr) {
          console.warn('[Bridge Error]', bridgeErr.message);
        }

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
            source: newOrder.source,
            fraud_level: newOrder.fraud_level,
            advance_amount: newOrder.advance_amount,
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
      const sourceFilter = parsedUrl.searchParams.get('source') || null;
      const orders = listOrders(limit, offset, sourceFilter);

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

        const decision = calculateDeliveryDecision(
          courierResult.data.total_parcels,
          courierResult.data.delivered,
          courierResult.data.cancelled_or_returned,
          deliveryZone
        );

        return sendJson(res, 200, {
          success: true,
          phone: courierResult.data.phone,
          success_rate: decision.success_rate,
          trust_level: decision.level,
          trust_label: decision.label,
          total_parcels: courierResult.data.total_parcels,
          delivered: courierResult.data.delivered,
          cancelled: courierResult.data.cancelled_or_returned,
          requires_advance: decision.requires_advance,
          advance_amount: decision.advance_amount,
          advance_delivery_dhaka: decision.advance_delivery_dhaka,
          advance_delivery_outside: decision.advance_delivery_outside,
          payment_decision: decision.requires_advance ? 'advance_required' : 'cod',
          payment_message: decision.message
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
        const paymentMethod = body.payment_method || 'Cash on Delivery';
        const notes        = body.note || '';
        const source       = body.source || 'MAIN_WEBSITE';
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

    // Forward any Laravel-backed admin, auth, or tracking APIs to Laravel
    if (reqPath.startsWith('/api/admin/') || reqPath.startsWith('/api/tracking/') || reqPath.startsWith('/api/auth/')) {
      const proxied = await proxyToLaravel(req, res, reqPath, parsedUrl.search);
      if (proxied) return;
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


  // Dynamic Storefront Proxy: Forward to E Commerce Baby (Laravel)
  if (isStorefrontRoute(reqPath)) {
    const proxied = await proxyToLaravel(req, res, reqPath, parsedUrl.search);
    if (proxied) return;
    // Fallback: If Laravel is offline
    res.writeHead(503, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end('<h2>Storefront Service Unavailable</h2><p>The backend application is currently starting or offline.</p>');
  }

  let filePath = path.join(__dirname, decodeURIComponent(localReqPath));

  // If path is a directory, redirect if missing trailing slash, then look for index.html inside
  if (fs.existsSync(filePath) && fs.statSync(filePath).isDirectory()) {
    if (!reqPath.endsWith('/')) {
      res.writeHead(301, { 'Location': reqPath + '/' + (parsedUrl.search || '') });
      return res.end();
    }
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
  startLaravelServer();
});

// Graceful Shutdown for PM2 / CloudPanel
function handleShutdown(signal) {
  console.log(`[Server] Received ${signal}. Closing server gracefully...`);
  if (laravelProcess) {
    try { laravelProcess.kill(); } catch (e) {}
  }
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
