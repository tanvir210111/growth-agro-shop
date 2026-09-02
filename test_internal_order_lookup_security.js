/**
 * test_internal_order_lookup_security.js
 *
 * Focused integration & security test for:
 * GET /api/internal/order-lookup/:orderNumber
 *
 * Verifies:
 * 1. Correct X-Internal-Secret -> returns the requested order (HTTP 200)
 * 2. Missing X-Internal-Secret -> returns HTTP 403 Forbidden
 * 3. Wrong X-Internal-Secret -> returns HTTP 403 Forbidden
 * 4. Unknown order number -> returns HTTP 404
 * 5. Returns only the requested order (exact match on order_number)
 * 6. Performs zero BD Courier / fraud / external API calls
 * 7. Existing admin-token auth on GET /api/orders/:orderNumber remains unchanged (401 without admin token)
 * 8. FrontendController syncOrderFromNode uses /api/internal/order-lookup/{orderNumber}
 */

const http = require('http');
const assert = require('assert');
const fs = require('fs');
const path = require('path');
const { createOrder, getOrderByNumber } = require('./server/db');

const PORT = 3000;
const INTERNAL_SECRET = process.env.INTERNAL_API_SECRET || 'baby-fashion-internal-2024-secret';

function request(options) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        let json = null;
        try {
          json = JSON.parse(data);
        } catch (e) {
          json = data;
        }
        resolve({ status: res.statusCode, headers: res.headers, data: json });
      });
    });
    req.on('error', reject);
    req.setTimeout(5000, () => {
      req.destroy();
      reject(new Error('Request timed out'));
    });
    req.end();
  });
}

async function runTests() {
  console.log('======================================================');
  console.log('🧪 TESTING /api/internal/order-lookup/:orderNumber SECURITY');
  console.log('======================================================\n');

  // Seed a known test order directly in SQLite
  const testOrderNumber = 'CB-LOOKUP-TEST-' + Date.now().toString().slice(-6);
  const seeded = createOrder({
    customerName: 'Internal Security Test',
    phone: '01799887766',
    address: 'Gulshan 1, Dhaka',
    productId: 'chicken-booster',
    productName: 'Chicken Booster',
    variantId: 'variant-1',
    variantName: '1 Pack',
    quantity: 1,
    unitPrice: 990,
    subtotal: 990,
    deliveryZone: 'inside',
    deliveryCharge: 60,
    total: 1050,
    currency: 'BDT',
    landingPage: '/product/chicken-booster',
    source: 'LANDING_PAGE',
    orderNumber: testOrderNumber
  });
  const actualOrderNumber = seeded.order_number;

  // 1. Correct secret -> 200 with order payload
  const resValid = await request({
    hostname: '127.0.0.1',
    port: PORT,
    path: `/api/internal/order-lookup/${encodeURIComponent(actualOrderNumber)}`,
    method: 'GET',
    headers: {
      'X-Internal-Secret': INTERNAL_SECRET,
      'Accept': 'application/json'
    }
  });
  assert.strictEqual(resValid.status, 200, `Expected 200, got ${resValid.status}`);
  assert.strictEqual(resValid.data.success, true);
  assert.strictEqual(resValid.data.order.order_number, actualOrderNumber);
  assert.strictEqual(resValid.data.order.customer_name, 'Internal Security Test');
  console.log('  ✅ PASS: 1. Correct X-Internal-Secret returns requested order (HTTP 200)');

  // 2. Missing secret -> 403
  const resMissing = await request({
    hostname: '127.0.0.1',
    port: PORT,
    path: `/api/internal/order-lookup/${encodeURIComponent(actualOrderNumber)}`,
    method: 'GET',
    headers: {
      'Accept': 'application/json'
    }
  });
  assert.strictEqual(resMissing.status, 403, `Expected 403 for missing secret, got ${resMissing.status}`);
  assert.strictEqual(resMissing.data.success, false);
  console.log('  ✅ PASS: 2. Missing X-Internal-Secret returns HTTP 403');

  // 3. Wrong secret -> 403
  const resWrong = await request({
    hostname: '127.0.0.1',
    port: PORT,
    path: `/api/internal/order-lookup/${encodeURIComponent(actualOrderNumber)}`,
    method: 'GET',
    headers: {
      'X-Internal-Secret': 'completely-wrong-token-abc',
      'Accept': 'application/json'
    }
  });
  assert.strictEqual(resWrong.status, 403, `Expected 403 for wrong secret, got ${resWrong.status}`);
  assert.strictEqual(resWrong.data.success, false);
  console.log('  ✅ PASS: 3. Wrong X-Internal-Secret returns HTTP 403');

  // 4. Unknown order number -> 404
  const resUnknown = await request({
    hostname: '127.0.0.1',
    port: PORT,
    path: '/api/internal/order-lookup/CB-NONEXISTENT-9999999',
    method: 'GET',
    headers: {
      'X-Internal-Secret': INTERNAL_SECRET,
      'Accept': 'application/json'
    }
  });
  assert.strictEqual(resUnknown.status, 404, `Expected 404 for unknown order, got ${resUnknown.status}`);
  assert.strictEqual(resUnknown.data.success, false);
  console.log('  ✅ PASS: 4. Unknown order number returns HTTP 404');

  // 5. Returns only requested order (not collection or unintended order)
  assert.strictEqual(typeof resValid.data.order, 'object');
  assert.strictEqual(Array.isArray(resValid.data.order), false);
  assert.strictEqual(resValid.data.order.order_number, actualOrderNumber);
  console.log('  ✅ PASS: 5. Endpoint returns only single requested order object');

  // 6. Zero BD Courier / external API call
  // Check code in server.js around internalOrderLookupMatch: does not invoke checkBdCourier, courier api or fraud
  const serverCode = fs.readFileSync(path.join(__dirname, 'server.js'), 'utf8');
  const lookupSectionMatch = serverCode.match(/const internalOrderLookupMatch[\s\S]*?return sendJson\(res, 200, \{ success: true, order: foundOrder \}\);\s*\}/);
  assert.ok(lookupSectionMatch, 'Internal order lookup block must exist in server.js');
  const lookupCode = lookupSectionMatch[0];
  assert.ok(!lookupCode.includes('checkBdCourier'), 'Must not call checkBdCourier');
  assert.ok(!lookupCode.includes('api.bdcourier.com'), 'Must not reference BD Courier API');
  assert.ok(!lookupCode.includes('calculateDeliveryDecision'), 'Must not calculate delivery decision');
  console.log('  ✅ PASS: 6. Verified pure SQLite read: zero BD Courier / fraud API calls');

  // 7. Existing admin-token auth on GET /api/orders/:orderNumber remains unchanged
  const resAdminUnauth = await request({
    hostname: '127.0.0.1',
    port: PORT,
    path: `/api/orders/${encodeURIComponent(actualOrderNumber)}`,
    method: 'GET',
    headers: {
      'X-Internal-Secret': INTERNAL_SECRET, // Should NOT bypass admin auth
      'Accept': 'application/json'
    }
  });
  assert.strictEqual(resAdminUnauth.status, 401, `Expected 401 for admin endpoint without admin token, got ${resAdminUnauth.status}`);
  console.log('  ✅ PASS: 7. GET /api/orders/:orderNumber preserves strict admin JWT authentication (HTTP 401)');

  // 8. FrontendController uses /api/internal/order-lookup/{orderNumber}
  const frontendControllerCode = fs.readFileSync(
    path.join(__dirname, 'E Commerce Baby', 'app', 'Http', 'Controllers', 'FrontendController.php'),
    'utf8'
  );
  assert.ok(
    frontendControllerCode.includes('/api/internal/order-lookup/{$orderNumber}'),
    'FrontendController must call /api/internal/order-lookup/{$orderNumber}'
  );
  console.log('  ✅ PASS: 8. FrontendController syncOrderFromNode() targets /api/internal/order-lookup/{$orderNumber}');

  console.log('\n======================================================');
  console.log('🎉 ALL 8 / 8 INTERNAL ORDER LOOKUP CHECKS PASSED!');
  console.log('======================================================\n');
}

runTests().catch(err => {
  console.error('❌ Test failed:', err);
  process.exit(1);
});
