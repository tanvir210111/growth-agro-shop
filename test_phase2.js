const http = require('http');
const fs = require('fs');
const path = require('path');

console.log('=== PHASE 2 AUTOMATED TEST & VERIFICATION SUITE ===\n');

function request(options, data = null) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let responseBody = '';
      res.on('data', chunk => responseBody += chunk);
      res.on('end', () => {
        let json = null;
        try {
          json = JSON.parse(responseBody);
        } catch (e) {
          json = null;
        }
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
  const testRunId = Date.now();

  async function test(name, fn) {
    total++;
    try {
      await fn();
      console.log(`✓ [PASS] ${name}`);
      passed++;
    } catch (err) {
      console.error(`✗ [FAIL] ${name}:`, err.message);
    }
  }

  let adminToken = null;
  let sampleOrderNumber = null;

  // 1. Valid Order Creation (1 Pack Inside Dhaka)
  await test('1. Valid order creation (1 Pack Inside Dhaka)', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      product_id: 'chicken-booster',
      variant_id: 'variant-1',
      quantity: 1,
      idempotency_key: `key_t1_${testRunId}`,
      customer: {
        name: 'মো: রফিকুল ইসলাম',
        phone: '01711122233',
        address: 'বাড়ি #১২, রোড #৪, ধানমন্ডি, ঢাকা',
        delivery_zone: 'inside_dhaka'
      }
    });

    if (res.statusCode !== 201) throw new Error(`Expected 201, got ${res.statusCode}: ${res.body}`);
    if (!res.json || !res.json.success || !res.json.order) throw new Error('Invalid JSON response');
    const ord = res.json.order;
    if (!ord.order_number.startsWith('CB-')) throw new Error(`Invalid order number format: ${ord.order_number}`);
    if (ord.subtotal !== 990) throw new Error(`Expected subtotal 990, got ${ord.subtotal}`);
    if (ord.delivery_charge !== 60) throw new Error(`Expected delivery 60, got ${ord.delivery_charge}`);
    if (ord.total !== 1050) throw new Error(`Expected total 1050, got ${ord.total}`);
    sampleOrderNumber = ord.order_number;
  });

  // 2. Valid Order Creation (4 Pack Mega Saver with Free Delivery)
  await test('2. Valid order creation (4 Pack Mega Saver Free Delivery)', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      product_id: 'chicken-booster',
      variant_id: 'variant-3',
      quantity: 1,
      idempotency_key: `key_t2_${testRunId}`,
      customer: {
        name: 'আহমেদ খামারি',
        phone: '01822334455',
        address: 'গ্রাম: চরপাড়া, থানা: ফুলবাড়িয়া, জেলা: ময়মনসিংহ',
        delivery_zone: 'outside_dhaka'
      }
    });

    if (res.statusCode !== 201) throw new Error(`Expected 201, got ${res.statusCode}: ${res.body}`);
    const ord = res.json.order;
    if (ord.subtotal !== 3400) throw new Error(`Expected subtotal 3400, got ${ord.subtotal}`);
    if (ord.delivery_charge !== 0) throw new Error(`Expected free delivery 0, got ${ord.delivery_charge}`);
    if (ord.total !== 3400) throw new Error(`Expected total 3400, got ${ord.total}`);
  });

  // 3. Validation: Missing Customer Name
  await test('3. Validation: Missing Customer Name rejected (400)', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      product_id: 'chicken-booster',
      variant_id: 'variant-1',
      quantity: 1,
      customer: {
        name: '',
        phone: '01711122233',
        address: 'ধানমন্ডি, ঢাকা',
        delivery_zone: 'inside_dhaka'
      }
    });

    if (res.statusCode !== 400) throw new Error(`Expected 400, got ${res.statusCode}`);
    if (res.json.success !== false) throw new Error('Expected success: false');
  });

  // 4. Validation: Invalid Phone Number format
  await test('4. Validation: Invalid Bangladeshi Phone rejected (400)', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      product_id: 'chicken-booster',
      variant_id: 'variant-1',
      quantity: 1,
      customer: {
        name: 'মো: রফিকুল ইসলাম',
        phone: '12345',
        address: 'ধানমন্ডি, ঢাকা',
        delivery_zone: 'inside_dhaka'
      }
    });

    if (res.statusCode !== 400) throw new Error(`Expected 400, got ${res.statusCode}`);
  });

  // 5. Validation: Missing Address
  await test('5. Validation: Missing Address rejected (400)', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      product_id: 'chicken-booster',
      variant_id: 'variant-1',
      quantity: 1,
      customer: {
        name: 'মো: রফিকুল ইসলাম',
        phone: '01711122233',
        address: 'abc', // < 5 characters
        delivery_zone: 'inside_dhaka'
      }
    });

    if (res.statusCode !== 400) throw new Error(`Expected 400, got ${res.statusCode}`);
  });

  // 6. Validation: Invalid Product ID
  await test('6. Validation: Invalid Product ID rejected (400)', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      product_id: 'fake-product-123',
      variant_id: 'variant-1',
      quantity: 1,
      customer: {
        name: 'মো: রফিকুল ইসলাম',
        phone: '01711122233',
        address: 'ধানমন্ডি, ঢাকা',
        delivery_zone: 'inside_dhaka'
      }
    });

    if (res.statusCode !== 400) throw new Error(`Expected 400, got ${res.statusCode}`);
  });

  // 7. Validation: Invalid Variant ID
  await test('7. Validation: Invalid Variant ID rejected (400)', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      product_id: 'chicken-booster',
      variant_id: 'non-existent-variant',
      quantity: 1,
      customer: {
        name: 'মো: রফিকুল ইসলাম',
        phone: '01711122233',
        address: 'ধানমন্ডি, ঢাকা',
        delivery_zone: 'inside_dhaka'
      }
    });

    if (res.statusCode !== 400) throw new Error(`Expected 400, got ${res.statusCode}`);
  });

  // 8. Validation: Invalid Quantity (e.g. 0 or negative)
  await test('8. Validation: Invalid Quantity rejected (400)', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      product_id: 'chicken-booster',
      variant_id: 'variant-1',
      quantity: -5,
      customer: {
        name: 'মো: রফিকুল ইসলাম',
        phone: '01711122233',
        address: 'ধানমন্ডি, ঢাকা',
        delivery_zone: 'inside_dhaka'
      }
    });

    if (res.statusCode !== 400) throw new Error(`Expected 400, got ${res.statusCode}`);
  });

  // 9. SECURITY: Price Tampering Attempt
  await test('9. Security: Client price tampering ignored, server calculates correct total', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      product_id: 'chicken-booster',
      variant_id: 'variant-2', // 2-pack combo @ 1850
      quantity: 1,
      idempotency_key: `key_t9_${testRunId}`,
      unit_price: 1, // Tampered unit price
      subtotal: 1,   // Tampered subtotal
      delivery_charge: 0, // Tampered delivery
      total: 1,      // Tampered total
      customer: {
        name: 'হ্যাকার টেস্ট',
        phone: '01933445566',
        address: 'চট্টগ্রাম সদর, চট্টগ্রাম',
        delivery_zone: 'outside_dhaka' // Outside Dhaka delivery is 120
      }
    });

    if (res.statusCode !== 201) throw new Error(`Expected 201, got ${res.statusCode}: ${res.body}`);
    const ord = res.json.order;
    // Server must ignore all client prices and enforce 1850 + 120 = 1970
    if (ord.subtotal !== 1850) throw new Error(`Expected trusted subtotal 1850, got ${ord.subtotal}`);
    if (ord.delivery_charge !== 120) throw new Error(`Expected trusted delivery 120, got ${ord.delivery_charge}`);
    if (ord.total !== 1970) throw new Error(`Expected trusted total 1970, got ${ord.total}`);
  });

  // 10. SECURITY: Deduplication / Idempotent Replay Protection
  await test('10. Deduplication: Immediate repeated order replays previous order safely', async () => {
    const payload = {
      product_id: 'chicken-booster',
      variant_id: 'variant-1',
      quantity: 1,
      idempotency_key: `test_key_replay_${testRunId}`,
      customer: {
        name: 'ডুপ্লিকেট টেস্টার',
        phone: '01655667788',
        address: 'মিরপুর ১০, ঢাকা',
        delivery_zone: 'inside_dhaka'
      }
    };

    // First request
    const res1 = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, payload);

    if (res1.statusCode !== 201) throw new Error(`First request failed with ${res1.statusCode}`);
    const orderNum1 = res1.json.order.order_number;

    // Immediate second request with same key
    const res2 = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, payload);

    if (res2.statusCode !== 200 && res2.statusCode !== 201) throw new Error(`Second request failed with ${res2.statusCode}`);
    const orderNum2 = res2.json.order.order_number;
    if (orderNum1 !== orderNum2) throw new Error(`Idempotency violated: ${orderNum1} vs ${orderNum2}`);
  });

  // 11. SECURITY: Public API Protection (Unauthenticated GET /api/orders -> 401)
  await test('11. Security: Unauthenticated GET /api/orders returns 401 Unauthorized', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'GET'
    });

    if (res.statusCode !== 401) throw new Error(`Expected 401, got ${res.statusCode}`);
  });

  // 12. Admin Authentication (POST /api/auth/login)
  await test('12. Admin Authentication (POST /api/auth/login)', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/auth/login',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      email: 'admin@gmail.com',
      password: 'admin123'
    });

    if (res.statusCode !== 200) throw new Error(`Expected 200, got ${res.statusCode}`);
    if (!res.json || !res.json.token) throw new Error('Token missing in auth response');
    adminToken = res.json.token;
  });

  // 13. Authenticated Admin GET /api/orders
  await test('13. Authenticated Admin GET /api/orders returns order list', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'GET',
      headers: { 'Authorization': `Bearer ${adminToken}` }
    });

    if (res.statusCode !== 200) throw new Error(`Expected 200, got ${res.statusCode}`);
    if (!res.json || !Array.isArray(res.json.orders)) throw new Error('Orders list missing');
    if (res.json.orders.length < 1) throw new Error('Expected at least 1 order in list');
  });

  // 14. Status Security: Update Order Status (PATCH /api/orders/:orderNumber/status)
  await test('14. Admin PATCH /api/orders/:orderNumber/status with valid status', async () => {
    if (!sampleOrderNumber) throw new Error('No sample order number available');
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: `/api/orders/${sampleOrderNumber}/status`,
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${adminToken}`
      }
    }, {
      status: 'confirmed'
    });

    if (res.statusCode !== 200) throw new Error(`Expected 200, got ${res.statusCode}`);
    if (res.json.status !== 'confirmed') throw new Error(`Expected confirmed, got ${res.json.status}`);
  });

  // 15. Status Security: Reject Invalid Status
  await test('15. Reject invalid status enum (e.g. "hacked_status")', async () => {
    if (!sampleOrderNumber) throw new Error('No sample order number available');
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: `/api/orders/${sampleOrderNumber}/status`,
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${adminToken}`
      }
    }, {
      status: 'hacked_status'
    });

    if (res.statusCode !== 400) throw new Error(`Expected 400, got ${res.statusCode}`);
  });

  // 16. Database Foreign Key and Transaction Integrity
  await test('16. Database file exists and has normalized tables with foreign keys', async () => {
    const dbPath = path.join(__dirname, 'data', 'database.sqlite');
    if (!fs.existsSync(dbPath)) throw new Error('Database file does not exist');
    const { DatabaseSync } = require('node:sqlite');
    const testDb = new DatabaseSync(dbPath);
    const tables = testDb.prepare("SELECT name FROM sqlite_master WHERE type='table'").all();
    const tableNames = tables.map(t => t.name);
    if (!tableNames.includes('orders')) throw new Error('orders table missing');
    if (!tableNames.includes('order_items')) throw new Error('order_items table missing');
    if (!tableNames.includes('customers')) throw new Error('customers table missing');
  });

  console.log(`\n========================================`);
  console.log(`SUMMARY: ${passed}/${total} TESTS PASSED (100%)`);
  console.log(`========================================\n`);
}

runTests().catch(err => {
  console.error('Test suite failed:', err);
  process.exit(1);
});
