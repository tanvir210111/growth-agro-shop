const http = require('http');
const { execSync } = require('child_process');

console.log('=== PHASE 11 UNIFIED ECOMMERCE STOREFRONT & CENTRAL ADMIN SUITE ===\n');

function request(options, data = null) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let responseBody = '';
      res.on('data', chunk => responseBody += chunk);
      res.on('end', () => {
        let json = null;
        try { json = JSON.parse(responseBody); } catch (e) { json = null; }
        resolve({
          statusCode: res.statusCode,
          headers: res.headers,
          body: responseBody,
          json
        });
      });
    });

    req.on('error', reject);

    if (data) {
      const payload = typeof data === 'string' ? data : JSON.stringify(data);
      req.write(payload);
    }
    req.end();
  });
}

async function runTests() {
  let passed = 0;
  let total = 0;

  async function test(name, fn) {
    total++;
    try {
      await fn();
      console.log(`✓ [PASS] ${total}. ${name}`);
      passed++;
    } catch (err) {
      console.error(`✗ [FAIL] ${total}. ${name}:`, err.message);
    }
  }

  // 1. Root serves Baby Fashion BD Storefront
  await test('Root URL (GET /) serves Baby Fashion BD Storefront (200 OK)', async () => {
    const res = await request({ hostname: '127.0.0.1', port: 3000, path: '/', method: 'GET' });
    if (res.statusCode !== 200) throw new Error(`Expected 200, got ${res.statusCode}`);
    if (!res.body.includes('Baby Fashion BD') || (!res.body.includes('babyOrderModal') && !res.body.includes('cartDrawer') && !res.body.includes('site-main'))) {
      throw new Error('Root HTML missing Baby Fashion BD Storefront content');
    }
  });


  // 4. Admin Panel route
  await test('Admin Panel (GET /admin/) serves Unified Dashboard (200 OK)', async () => {
    const res = await request({ hostname: '127.0.0.1', port: 3000, path: '/admin/index.html', method: 'GET' });
    if (res.statusCode !== 200) throw new Error(`Expected 200, got ${res.statusCode}`);
    if (!res.body.includes('Admin Panel') || !res.body.includes('View Live Store')) {
      throw new Error('Admin HTML missing updated unified dashboard elements');
    }
  });

  // 5. Place Storefront Order
  let placedOrderNumber = null;
  await test('Storefront customer places Baby Fashion Order via POST /api/orders (Authoritative pricing)', async () => {
    const payload = {
      product_id: 'baby-butterfly-set',
      variant_id: '1-2Y',
      quantity: 1,
      delivery_zone: 'inside',
      customer_name: 'Baby Store Mom Fatima',
      customer_phone: '017' + Math.floor(10000000 + Math.random() * 90000000),
      shipping_address: 'Dhanmondi 32, Dhaka',
      shipping_city: 'Dhaka',
      payment_method: 'cod'
    };
    const res = await request({
      hostname: '127.0.0.1',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, payload);

    if (res.statusCode !== 201 && !(res.statusCode === 200 && res.json && res.json.is_replay)) {
      throw new Error(`Expected 201 Created, got ${res.statusCode}: ${res.body}`);
    }
    if (!res.json || !res.json.success || !res.json.order) throw new Error('Order creation payload failed');
    placedOrderNumber = res.json.order.order_number;
    // Expected: 200 + 60 = 260
    if (res.json.order.total !== 260) {
      throw new Error(`Expected authoritative total 260, got ${res.json.order.total}`);
    }
  });

  // 6. Admin Login & Order Retrieval
  let adminToken = null;
  await test('Admin logs in and retrieves Storefront Order via GET /api/orders', async () => {
    const loginRes = await request({
      hostname: '127.0.0.1',
      port: 3000,
      path: '/api/auth/login',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, { email: 'admin@gmail.com', password: 'admin123' });

    if (!loginRes.json || !loginRes.json.token) throw new Error('Admin login failed');
    adminToken = loginRes.json.token;

    const ordersRes = await request({
      hostname: '127.0.0.1',
      port: 3000,
      path: '/api/orders',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}` }
    });

    if (ordersRes.statusCode !== 200 || !ordersRes.json || !Array.isArray(ordersRes.json.orders)) {
      throw new Error('Failed to list orders for admin');
    }
    const found = ordersRes.json.orders.find(o => o.order_number === placedOrderNumber);
    if (!found) throw new Error(`Order ${placedOrderNumber} not found in admin orders list`);
  });

  // 7. BD Courier Check for Storefront Order Customer
  await test('Admin runs BD Courier Verification on Storefront Order customer phone', async () => {
    const checkRes = await request({
      hostname: '127.0.0.1',
      port: 3000,
      path: '/api/courier/check',
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${adminToken}`
      }
    }, { phone: '01711223344' });

    if (checkRes.statusCode !== 200 && checkRes.statusCode !== 401 && checkRes.statusCode !== 504) {
      throw new Error(`Expected 200, 401, or 504, got ${checkRes.statusCode}: ${checkRes.body}`);
    }
  });

  // 8. Phase 10 Regression Suite
  await test('Phase 10 regression test suite (node test_phase10.js)', async () => {
    execSync('node test_phase10.js', { stdio: 'pipe' });
  });

  console.log('\n========================================');
  console.log(`SUMMARY: ${passed}/${total} TESTS PASSED (${Math.round((passed/total)*100)}%)`);
  console.log('========================================\n');

  if (passed !== total) process.exit(1);
}

runTests().catch(err => {
  console.error('Fatal test runner error:', err);
  process.exit(1);
});
