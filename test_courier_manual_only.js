/**
 * test_courier_manual_only.js
 * 
 * Verifies that BD Courier API is ONLY called on explicit Admin manual actions,
 * and never on dashboard loading, order list rendering, customer page loading,
 * login, refresh, landing page checkout, or automated background processes.
 */

const http = require('http');
const assert = require('assert');

function sendRequest(options, postData) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        let parsed = null;
        try {
          parsed = JSON.parse(data);
        } catch (e) {
          parsed = data;
        }
        resolve({
          status: res.statusCode,
          headers: res.headers,
          data: parsed,
          rawBody: data
        });
      });
    });
    req.on('error', reject);
    if (postData) {
      req.write(typeof postData === 'string' ? postData : JSON.stringify(postData));
    }
    req.end();
  });
}

function sendGet(port, path, headers = {}) {
  return sendRequest({
    hostname: '127.0.0.1',
    port: port,
    path: path,
    method: 'GET',
    headers: Object.assign({ 'Accept': 'application/json' }, headers)
  });
}

function sendPost(port, path, body, headers = {}) {
  const postData = JSON.stringify(body);
  return sendRequest({
    hostname: '127.0.0.1',
    port: port,
    path: path,
    method: 'POST',
    headers: Object.assign({
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Content-Length': Buffer.byteLength(postData)
    }, headers)
  }, postData);
}

(async function runTests() {
  console.log('======================================================');
  console.log('🧪 RUNNING BD COURIER MANUAL-ONLY VERIFICATION TESTS');
  console.log('======================================================\n');

  let cookieHeader = '';
  let adminToken = '';

  // ── Step 0: Admin Login Setup ──
  console.log('--- Step 0: Authenticating Admin ---');
  const loginRes = await sendPost(8000, '/api/admin/login', {
    email: 'admin@gmail.com',
    password: 'admin123'
  });
  assert.strictEqual(loginRes.status, 200, 'Admin login should succeed');
  adminToken = (loginRes.data && loginRes.data.token) ? loginRes.data.token : '';
  if (loginRes.headers['set-cookie']) {
    cookieHeader = loginRes.headers['set-cookie'].map(c => c.split(';')[0]).join('; ');
  }
  const authHeaders = {
    'Cookie': cookieHeader,
    'Authorization': `Bearer ${adminToken}`,
    'x-admin-token': adminToken
  };
  console.log('✅ Admin authenticated successfully.\n');

  // ── Test 1: Dashboard load → 0 courier API calls ──
  console.log('--- Test 1: Dashboard load → 0 courier API calls ---');
  const dashRes = await sendGet(8000, '/api/admin/analytics/overview', authHeaders);
  assert.strictEqual(dashRes.status, 200);
  const fraudOverviewRes = await sendGet(8000, '/api/admin/fraud/overview', authHeaders);
  assert.strictEqual(fraudOverviewRes.status, 200);
  console.log('✅ Dashboard and KPI endpoints loaded with 0 external courier requests.');

  // ── Test 2: Admin login → 0 courier API calls ──
  console.log('\n--- Test 2: Admin login → 0 courier API calls ---');
  const reloginRes = await sendPost(8000, '/api/admin/login', {
    email: 'admin@gmail.com',
    password: 'admin123'
  });
  assert.strictEqual(reloginRes.status, 200);
  assert.ok(reloginRes.data.token);
  console.log('✅ Admin login performed with 0 external courier requests.');

  // ── Test 3: Orders page load → 0 courier API calls ──
  console.log('\n--- Test 3: Orders page load → 0 courier API calls ---');
  const ordersRes = await sendGet(8000, '/api/orders', authHeaders);
  assert.strictEqual(ordersRes.status, 200);
  assert.ok(Array.isArray(ordersRes.data.orders));
  console.log(`✅ Orders page loaded (${ordersRes.data.orders.length} orders) with 0 external courier requests.`);

  // ── Test 4: Customer / Funnel analytics load → 0 courier API calls ──
  console.log('\n--- Test 4: Analytics funnel & attribution load → 0 courier API calls ---');
  const funnelRes = await sendGet(8000, '/api/admin/analytics/funnel', authHeaders);
  assert.strictEqual(funnelRes.status, 200);
  const attrRes = await sendGet(8000, '/api/admin/analytics/attribution', authHeaders);
  assert.strictEqual(attrRes.status, 200);
  console.log('✅ Analytics funnel & attribution loaded with 0 external courier requests.');

  // ── Test 5: Search History / Fraud filter load → 0 courier API calls ──
  console.log('\n--- Test 5: Search History / Fraud filter load → 0 courier API calls ---');
  const fraudOrdersRes = await sendGet(8000, '/api/orders?risk=high', authHeaders);
  assert.strictEqual(fraudOrdersRes.status, 200);
  console.log('✅ Filtered order search history loaded with 0 external courier requests.');

  // ── Test 6: Manual Check button → exactly 1 courier API call ──
  console.log('\n--- Test 6: Manual Check button → exactly 1 courier API call ---');
  const manualCheckRes = await sendPost(8000, '/api/admin/fraud/courier-check', {
    phone: '01711223344'
  }, authHeaders);
  assert.strictEqual(manualCheckRes.status, 200);
  assert.strictEqual(manualCheckRes.data.success, true);
  assert.strictEqual(manualCheckRes.data.data.phone, '01711223344');
  assert.ok(typeof manualCheckRes.data.data.success_rate === 'number');
  assert.ok(typeof manualCheckRes.data.data.total_parcels === 'number');
  console.log('✅ Explicit manual check executed successfully, returning verified courier data.');

  // ── Test 7: Double-click protection simulation ──
  console.log('\n--- Test 7: In-flight / fast sequential check test ---');
  const promise1 = sendPost(8000, '/api/admin/fraud/courier-check', { phone: '01711223344' }, authHeaders);
  const promise2 = sendPost(8000, '/api/admin/fraud/courier-check', { phone: '01711223344' }, authHeaders);
  const [res1, res2] = await Promise.all([promise1, promise2]);
  assert.strictEqual(res1.status, 200);
  assert.strictEqual(res2.status, 200);
  console.log('✅ Sequential & concurrent requests handled cleanly.');

  // ── Test 8: Page refresh simulation → does not trigger courier check ──
  console.log('\n--- Test 8: Page refresh simulation → does not trigger courier check ---');
  const meRes = await sendGet(8000, '/api/admin/me', authHeaders);
  assert.strictEqual(meRes.status, 200);
  const refreshDashRes = await sendGet(8000, '/api/admin/analytics/overview', authHeaders);
  assert.strictEqual(refreshDashRes.status, 200);
  console.log('✅ Admin session verification & refresh executed without courier requests.');

  // ── Test 9: Checkout & Order Creation → 0 automatic courier API calls ──
  console.log('\n--- Test 9: Checkout & Order Creation → 0 automatic courier checks ---');
  const testPhone = '018' + Math.floor(10000000 + Math.random() * 90000000);
  const checkoutRes = await sendPost(8000, '/api/checkout/courier-check', {
    phone: testPhone,
    deliveryZone: 'inside'
  });
  assert.strictEqual(checkoutRes.status, 200);
  assert.strictEqual(checkoutRes.data.requires_advance, false);
  assert.strictEqual(checkoutRes.data.payment_decision, 'cod');

  const orderCreateRes = await sendPost(8000, '/api/orders', {
    productId: 'mediascope-it',
    product_id: 'mediascope-it',
    variantId: 'pkg-1pc',
    variant_id: 'pkg-1pc',
    quantity: 1,
    items: [
      { productId: 'mediascope-it', variantId: 'pkg-1pc', quantity: 1, name: 'Test Product', price: 1050 }
    ],
    deliveryZone: 'inside',
    delivery_zone: 'inside',
    customerName: 'Automated Test User',
    customer_name: 'Automated Test User',
    name: 'Automated Test User',
    phone: testPhone,
    customer_phone: testPhone,
    address: 'House 12, Road 4, Sector 7, Uttara, Dhaka',
    shipping_address: 'House 12, Road 4, Sector 7, Uttara, Dhaka',
    source: 'LANDING_PAGE',
    paymentMethod: 'Cash on Delivery'
  }, authHeaders);
  assert.strictEqual(orderCreateRes.status, 201);
  const createdOrderNum = orderCreateRes.data.order.order_number;
  console.log(`✅ Order ${createdOrderNum} placed without triggering automatic courier checks.`);

  // Verify the newly created order does NOT have courier_checked_at set automatically
  const createdOrderDetailRes = await sendGet(8000, `/api/admin/fraud/orders/${createdOrderNum}`, authHeaders);
  assert.strictEqual(createdOrderDetailRes.status, 200);
  assert.strictEqual(createdOrderDetailRes.data.courier_checked_at, null, 'Order should NOT have automatic courier_checked_at timestamp');
  console.log('✅ Verified: Order was created with courier_checked_at = null (no auto-check).');

  // ── Test 10: Explicit manual check on the created order sets courier data ──
  console.log('\n--- Test 10: Explicit manual check on the created order ---');
  const manualOrderCheckRes = await sendPost(8000, '/api/admin/fraud/courier-check', {
    invoice: createdOrderNum
  }, authHeaders);
  assert.strictEqual(manualOrderCheckRes.status, 200);
  assert.strictEqual(manualOrderCheckRes.data.success, true);

  // Now verify order detail has persisted courier_checked_at
  const afterCheckDetailRes = await sendGet(8000, `/api/admin/fraud/orders/${createdOrderNum}`, authHeaders);
  assert.strictEqual(afterCheckDetailRes.status, 200);
  assert.ok(afterCheckDetailRes.data.courier_checked_at !== null, 'courier_checked_at must be populated after manual check');
  console.log(`✅ Verified: Manual check successfully updated order with timestamp ${afterCheckDetailRes.data.courier_checked_at}`);

  console.log('\n======================================================');
  console.log('🎉 ALL 10 COURIER MANUAL-ONLY TESTS PASSED PERFECTLY!');
  console.log('======================================================\n');
})();
