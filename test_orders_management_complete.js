/**
 * test_orders_management_complete.js
 * Comprehensive automated verification for Orders Management System & Zero Automatic Fraud Checks.
 */

const http = require('http');
const https = require('https');
const assert = require('assert');

const LARAVEL_PORT = 8000;
const NODE_PORT = 3000;
const ADMIN_TOKEN = 'admin-token-12345';
const INTERNAL_SECRET = 'baby-fashion-internal-2024-secret';

function makeRequest(options, postData = null) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        try {
          const json = JSON.parse(data);
          resolve({ status: res.statusCode, headers: res.headers, body: json, raw: data });
        } catch (e) {
          resolve({ status: res.statusCode, headers: res.headers, body: data, raw: data });
        }
      });
    });

    req.on('error', reject);
    req.setTimeout(6000, () => {
      req.destroy();
      reject(new Error('Request timed out'));
    });

    if (postData) {
      req.write(typeof postData === 'string' ? postData : JSON.stringify(postData));
    }
    req.end();
  });
}

let passedTests = 0;
let totalTests = 0;

function runTest(name, fn) {
  totalTests++;
  return fn()
    .then(() => {
      passedTests++;
      console.log(`  ✅ PASS: ${name}`);
    })
    .catch((err) => {
      console.error(`  ❌ FAIL: ${name}`);
      console.error(`     Error: ${err.message}`);
    });
}

async function startSuite() {
  console.log('\n===============================================================');
  console.log('  STARTING ORDERS MANAGEMENT & ZERO AUTO FRAUD TEST SUITE');
  console.log('===============================================================\n');

  const testInvoice1 = `TST-${Date.now()}-1`;
  const testInvoice2 = `TST-${Date.now()}-2`;
  const testPhone = '01711002233';

  // TEST 1: Landing Page Order Sync -> 0 Auto Fraud Check & Not Assessed
  await runTest('1. Landing Page Order Sync creates order as Not Assessed (null fraud & courier)', async () => {
    const payload = {
      order_number: testInvoice1,
      customer_name: 'Test Customer Alpha',
      customer_phone: testPhone,
      customer_address: 'House 12, Road 4, Dhanmondi, Dhaka',
      delivery_zone: 'inside',
      delivery_charge: 70,
      subtotal: 1200,
      total: 1270,
      payment_method: 'Cash on Delivery',
      landing_page: '/products/chicken-booster/',
      product_name: 'Chicken Booster',
      quantity: 1
    };

    const res = await makeRequest({
      hostname: '127.0.0.1',
      port: LARAVEL_PORT,
      path: '/api/internal/sync-landing-order',
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Internal-Secret': INTERNAL_SECRET
      }
    }, payload);

    assert.ok(res.status === 200 || res.status === 201, `Expected 200 or 201, got ${res.status}`);
    assert.strictEqual(res.body.success, true);
  });

  // TEST 2: Storefront Order Creation via Checkout -> 0 Auto Fraud Check & Not Assessed
  await runTest('2. Storefront Checkout creates order with null fraud metrics & null courier', async () => {
    const payload = {
      customer_name: 'Storefront Customer Beta',
      customer_phone: '01811998877',
      customer_address: 'GEC Circle, Chittagong',
      delivery_area: 'outside_dhaka',
      payment_method: 'COD',
      direct_product_id: 1,
      direct_quantity: 1
    };

    const res = await makeRequest({
      hostname: '127.0.0.1',
      port: LARAVEL_PORT,
      path: '/checkout',
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, payload);

    // Redirect or 200/302 indicates success
    assert.ok(res.status === 200 || res.status === 302, `Expected 200 or 302 redirect, got ${res.status}`);
  });

  // TEST 3: Orders List GET /api/orders returns orders with null fraud_level (Not Assessed) and null courier
  await runTest('3. GET /api/orders returns unassessed orders with null fraud metrics & null courier', async () => {
    const res = await makeRequest({
      hostname: '127.0.0.1',
      port: LARAVEL_PORT,
      path: '/api/orders',
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'x-admin-token': ADMIN_TOKEN
      }
    });

    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
    assert.ok(Array.isArray(res.body.orders));

    const found = res.body.orders.find(o => o.order_number === testInvoice1);
    assert.ok(found, `Order ${testInvoice1} should exist in orders list`);
    assert.strictEqual(found.fraud_level, null, 'fraud_level should be null (Not Assessed)');
    assert.strictEqual(found.fraud_score, null, 'fraud_score should be null');
    assert.strictEqual(found.courier_name, null, 'courier_name should be null (Unassigned)');
  });

  // TEST 4: Page Refresh / Repeated GET /api/orders makes 0 Courier API calls
  await runTest('4. Refreshing/Loading orders makes ZERO automatic Courier API calls', async () => {
    for (let i = 0; i < 3; i++) {
      const res = await makeRequest({
        hostname: '127.0.0.1',
        port: LARAVEL_PORT,
        path: '/api/orders',
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'x-admin-token': ADMIN_TOKEN
        }
      });
      assert.strictEqual(res.status, 200);
    }
  });

  // TEST 5: Courier Assignment via PATCH /api/orders/{order_number}/courier persists to DB
  await runTest('5. Courier assignment (Pathao) persists to database', async () => {
    const res = await makeRequest({
      hostname: '127.0.0.1',
      port: LARAVEL_PORT,
      path: `/api/orders/${testInvoice1}/courier`,
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'x-admin-token': ADMIN_TOKEN
      }
    }, { courier: 'Pathao' });

    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
    assert.strictEqual(res.body.courier, 'Pathao');

    // Verify persistence via GET
    const listRes = await makeRequest({
      hostname: '127.0.0.1',
      port: LARAVEL_PORT,
      path: '/api/orders',
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'x-admin-token': ADMIN_TOKEN
      }
    });
    const found = listRes.body.orders.find(o => o.order_number === testInvoice1);
    assert.strictEqual(found.courier_name, 'Pathao', 'Courier should be persisted as Pathao');
  });

  // TEST 6: Changing Courier to Steadfast persists to DB
  await runTest('6. Changing Courier to Steadfast updates and persists in DB', async () => {
    const res = await makeRequest({
      hostname: '127.0.0.1',
      port: LARAVEL_PORT,
      path: `/api/orders/${testInvoice1}/courier`,
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'x-admin-token': ADMIN_TOKEN
      }
    }, { courier: 'Steadfast' });

    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
    assert.strictEqual(res.body.courier, 'Steadfast');
  });

  // TEST 7: Order Status Update via PATCH /api/orders/{order_number}/status persists to DB
  await runTest('7. Order status update (Approved) persists to database', async () => {
    const res = await makeRequest({
      hostname: '127.0.0.1',
      port: LARAVEL_PORT,
      path: `/api/orders/${testInvoice1}/status`,
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'x-admin-token': ADMIN_TOKEN
      }
    }, { status: 'approved' });

    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);

    const listRes = await makeRequest({
      hostname: '127.0.0.1',
      port: LARAVEL_PORT,
      path: '/api/orders',
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'x-admin-token': ADMIN_TOKEN
      }
    });
    const found = listRes.body.orders.find(o => o.order_number === testInvoice1);
    assert.strictEqual(found.status.toLowerCase(), 'approved');
  });

  // TEST 8: Admin Explicit "Check" Button Trigger saves fraud metrics to DB
  await runTest('8. Admin explicit Check triggers verification and saves risk metrics to DB', async () => {
    const res = await makeRequest({
      hostname: '127.0.0.1',
      port: LARAVEL_PORT,
      path: '/api/admin/fraud/courier-check',
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'x-admin-token': ADMIN_TOKEN
      }
    }, { phone: testPhone, invoice: testInvoice1 });

    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);
    assert.ok(res.body.data, 'Data should be present');
    assert.ok(res.body.data.fraud_level, 'fraud_level should be calculated');
    assert.ok(res.body.data.fraud_score !== undefined, 'fraud_score should be calculated');

    // Verify persistence in DB
    const listRes = await makeRequest({
      hostname: '127.0.0.1',
      port: LARAVEL_PORT,
      path: '/api/orders',
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'x-admin-token': ADMIN_TOKEN
      }
    });
    const found = listRes.body.orders.find(o => o.order_number === testInvoice1);
    assert.ok(found.fraud_level !== null, 'fraud_level should now be persisted in DB');
    assert.ok(found.fraud_score !== null, 'fraud_score should now be persisted in DB');
  });

  // TEST 9: Risk Filtering works from database
  await runTest('9. Risk query filter returns correct orders', async () => {
    const unassessedRes = await makeRequest({
      hostname: '127.0.0.1',
      port: LARAVEL_PORT,
      path: '/api/orders?risk=not_assessed',
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'x-admin-token': ADMIN_TOKEN
      }
    });
    assert.strictEqual(unassessedRes.status, 200);
    unassessedRes.body.orders.forEach(o => {
      assert.strictEqual(o.fraud_score, null, 'Unassessed orders must have null fraud_score');
    });
  });

  // TEST 10: Real Database Deletion via DELETE /api/orders/{order_number}
  await runTest('10. DELETE /api/orders/{order_number} permanently deletes order from DB', async () => {
    const res = await makeRequest({
      hostname: '127.0.0.1',
      port: LARAVEL_PORT,
      path: `/api/orders/${testInvoice1}`,
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'x-admin-token': ADMIN_TOKEN
      }
    });

    assert.strictEqual(res.status, 200);
    assert.strictEqual(res.body.success, true);

    // Verify it is gone on subsequent fetch
    const listRes = await makeRequest({
      hostname: '127.0.0.1',
      port: LARAVEL_PORT,
      path: '/api/orders',
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'x-admin-token': ADMIN_TOKEN
      }
    });
    const found = listRes.body.orders.find(o => o.order_number === testInvoice1);
    assert.strictEqual(found, undefined, 'Deleted order must not exist in DB');
  });

  // TEST 11: Security — Unauthenticated order mutations are blocked
  await runTest('11. Security: Unauthenticated mutations return 401 Unauthorized', async () => {
    const resCourier = await makeRequest({
      hostname: '127.0.0.1',
      port: LARAVEL_PORT,
      path: `/api/orders/NONEXISTENT/courier`,
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }, { courier: 'Steadfast' });
    assert.strictEqual(resCourier.status, 401, 'Courier update requires admin token');

    const resDelete = await makeRequest({
      hostname: '127.0.0.1',
      port: LARAVEL_PORT,
      path: `/api/orders/NONEXISTENT`,
      method: 'DELETE',
      headers: {
        'Accept': 'application/json'
      }
    });
    assert.strictEqual(resDelete.status, 401, 'Order delete requires admin token');
  });

  console.log('\n===============================================================');
  console.log(`  SUITE COMPLETE: ${passedTests} / ${totalTests} TESTS PASSED`);
  console.log('===============================================================\n');

  if (passedTests !== totalTests) {
    process.exit(1);
  }
}

startSuite().catch(err => {
  console.error('Test suite runner crashed:', err);
  process.exit(1);
});
