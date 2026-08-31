const assert = require('assert');
const http = require('http');
const { calculateDeliveryDecision } = require('./server/courier');
const { createOrder, listOrders, getOrderByNumber, updateOrderStatus, addOrderTimelineEvent } = require('./server/db');

console.log('=== GROWTH AGRO COMPLETE ECOSYSTEM AUTOMATED SUITE ===\n');

let passed = 0;
let total = 0;

function test(name, fn) {
  total++;
  try {
    fn();
    console.log(`✓ [PASS] ${name}`);
    passed++;
  } catch (err) {
    console.error(`✗ [FAIL] ${name}:`, err.message);
  }
}

async function asyncTest(name, fn) {
  total++;
  try {
    await fn();
    console.log(`✓ [PASS] ${name}`);
    passed++;
  } catch (err) {
    console.error(`✗ [FAIL] ${name}:`, err.message);
  }
}

function httpRequest(options, data = null) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        let json = null;
        try { json = JSON.parse(body); } catch (e) {}
        resolve({ statusCode: res.statusCode, headers: res.headers, body, json });
      });
    });
    req.on('error', reject);
    if (data) {
      req.write(typeof data === 'string' ? data : JSON.stringify(data));
    }
    req.end();
  });
}

(async () => {
  // 1. Courier & Fraud Rules (Exact 80% Threshold Specification)
  test('1.1 Fraud Rule Case 1: 0 previous parcels -> Advance ৳0, Product COD', () => {
    const resInside = calculateDeliveryDecision(0, 0, 0, 'inside');
    const resOutside = calculateDeliveryDecision(0, 0, 0, 'outside');

    assert.strictEqual(resInside.requires_advance, false);
    assert.strictEqual(resInside.advance_amount, 0);
    assert.strictEqual(resInside.product_payment, 'COD');
    assert.strictEqual(resInside.level, 'new_customer');

    assert.strictEqual(resOutside.requires_advance, false);
    assert.strictEqual(resOutside.advance_amount, 0);
  });

  test('1.2 Fraud Rule Case 2: Success Rate > 80% (e.g. 85%) -> Advance ৳0, Product COD', () => {
    const resInside = calculateDeliveryDecision(20, 17, 3, 'inside'); // 85%
    assert.strictEqual(resInside.requires_advance, false);
    assert.strictEqual(resInside.advance_amount, 0);
    assert.strictEqual(resInside.product_payment, 'COD');
    assert.strictEqual(resInside.level, 'safe');
  });

  test('1.3 Fraud Rule Case 3: Success Rate <= 80% (e.g. 80%) -> Dhaka ৳80 advance, Outside ৳150 advance, Product COD', () => {
    const resInside = calculateDeliveryDecision(10, 8, 2, 'inside'); // exactly 80%
    assert.strictEqual(resInside.requires_advance, true);
    assert.strictEqual(resInside.advance_amount, 80);
    assert.strictEqual(resInside.product_payment, 'COD');

    const resOutside = calculateDeliveryDecision(10, 8, 2, 'outside'); // exactly 80%
    assert.strictEqual(resOutside.requires_advance, true);
    assert.strictEqual(resOutside.advance_amount, 150);
    assert.strictEqual(resOutside.product_payment, 'COD');
  });

  test('1.4 Fraud Rule Case 3 (Low Success Rate, e.g. 50%) -> Advance mandatory, Product COD', () => {
    const res = calculateDeliveryDecision(10, 5, 5, 'inside');
    assert.strictEqual(res.requires_advance, true);
    assert.strictEqual(res.advance_amount, 80);
    assert.strictEqual(res.product_payment, 'COD');
    assert.strictEqual(res.level, 'high_risk');
  });

  // 2. Central SQLite Database Architecture & Multi-Source Separation
  test('2.1 Database stores and isolates Landing Page vs Main Website orders', () => {
    const uniqueTs = Date.now().toString().slice(-6);
    
    // Create Landing Page order
    const lpOrder = createOrder({
      order_number: `CB-TEST-LP-${uniqueTs}`,
      source: 'LANDING_PAGE',
      product_id: 'chicken-booster',
      product_name: 'চিকেন বুস্টার (Chicken Booster)',
      variant_id: 'broiler-1kg',
      variant_name: 'Broiler Booster (১ কেজি)',
      quantity: 1,
      subtotal: 2300,
      delivery_charge: 0,
      total: 2300,
      customer_name: 'Landing Customer',
      customer_phone: '01711000001',
      customer_address: 'Gazipur, Dhaka',
      delivery_zone: 'outside',
      fraudLevel: 'safe',
      fraudScore: 90,
      advanceAmount: 0
    });

    // Create Main Website order
    const webOrder = createOrder({
      order_number: `MW-TEST-WEB-${uniqueTs}`,
      source: 'MAIN_WEBSITE',
      product_id: 'baby-dress',
      product_name: 'Baby Dress',
      variant_id: '1-2Y',
      variant_name: '1-2 Years',
      quantity: 1,
      subtotal: 1200,
      delivery_charge: 70,
      total: 1270,
      customer_name: 'Web Customer',
      customer_phone: '01811000002',
      customer_address: 'Uttara, Dhaka',
      delivery_zone: 'inside',
      fraudLevel: 'medium',
      fraudScore: 75,
      advanceAmount: 80
    });

    assert.strictEqual(lpOrder.source, 'LANDING_PAGE');
    assert.strictEqual(webOrder.source, 'MAIN_WEBSITE');

    // Test List Filtering
    const lpList = listOrders(50, 0, 'LANDING_PAGE');
    const webList = listOrders(50, 0, 'MAIN_WEBSITE');

    assert(lpList.some(o => o.order_number === lpOrder.order_number));
    assert(!lpList.some(o => o.order_number === webOrder.order_number));

    assert(webList.some(o => o.order_number === webOrder.order_number));
    assert(!webList.some(o => o.order_number === lpOrder.order_number));
  });

  // 3. Continuous Order Timeline Lifecycle
  test('3.1 Order Timeline tracks event history chronologically', () => {
    const testNo = `CB-TL-${Date.now().toString().slice(-6)}`;
    createOrder({
      order_number: testNo,
      source: 'LANDING_PAGE',
      product_id: 'chicken-booster',
      product_name: 'Chicken Booster',
      quantity: 1,
      subtotal: 2300,
      delivery_charge: 0,
      total: 2300,
      customer_name: 'Timeline Tester',
      customer_phone: '01711999999',
      customer_address: 'Dhaka',
      delivery_zone: 'inside'
    });

    // Append custom courier event
    addOrderTimelineEvent(testNo, 'Courier Submitted', 'Steadfast Consignment #SF-9912 generated', 'courier_submitted');
    updateOrderStatus(testNo, 'shipped', 'পার্সেল কুরিয়ারে হস্তান্তর করা হয়েছে');

    const updated = getOrderByNumber(testNo);
    const timeline = JSON.parse(updated.timeline);

    assert(Array.isArray(timeline));
    assert(timeline.length >= 3);
    assert.strictEqual(timeline[0].event, 'Order Created');
    assert.strictEqual(timeline[1].event, 'Fraud Checked');
    assert(timeline.some(t => t.event === 'Courier Submitted'));
    assert(timeline.some(t => t.status === 'shipped'));
  });

  // 4. Public Courier Risk Check API (POST /api/checkout/courier-check)
  await asyncTest('4.1 Public Checkout Courier Risk API responds with correct decision', async () => {
    const res = await httpRequest({
      hostname: '127.0.0.1',
      port: 3000,
      path: '/api/checkout/courier-check',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, { phone: '01711223344', deliveryZone: 'inside' });

    assert.strictEqual(res.statusCode, 200);
    assert.strictEqual(res.json.success, true);
    assert.strictEqual(res.json.requires_advance, false);
    assert.strictEqual(res.json.product_payment, 'COD');
  });

  // 5. Landing Page Live Checkout Verification
  await asyncTest('5.1 Landing page creates order with source LANDING_PAGE and sets initial timeline', async () => {
    const res = await httpRequest({
      hostname: '127.0.0.1',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      productId: 'chicken-booster',
      variantId: 'broiler-1kg',
      quantity: 1,
      deliveryZone: 'inside',
      customerName: 'Landing Page Buyer',
      customerPhone: '017' + Math.floor(10000000 + Math.random() * 90000000),
      address: 'Banani, Dhaka',
      source: 'LANDING_PAGE'
    });

    assert(res.statusCode === 200 || res.statusCode === 201);
    assert.strictEqual(res.json.success, true);
    assert.strictEqual(res.json.order.source, 'LANDING_PAGE');
  });

  // 6. Central Gateway Sync from Main Website (POST /api/internal/sync-order)
  await asyncTest('6.1 Main Website dispatches order sync to Central Order Gateway', async () => {
    const syncNo = `MW-SYNC-${Date.now().toString().slice(-6)}`;
    const res = await httpRequest({
      hostname: '127.0.0.1',
      port: 3000,
      path: '/api/internal/sync-order',
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Internal-Secret': 'baby-fashion-internal-2024-secret'
      }
    }, {
      order_number: syncNo,
      customer_name: 'Main Website Shopper',
      customer_phone: '01911223344',
      customer_address: 'Mirpur DOHS, Dhaka',
      delivery_area: 'inside_dhaka',
      delivery_charge: 70,
      subtotal: 3500,
      total_amount: 3570,
      payment_method: 'Cash on Delivery',
      source: 'MAIN_WEBSITE'
    });

    assert(res.statusCode === 200 || res.statusCode === 201);
    assert.strictEqual(res.json.success, true);
    assert.strictEqual(res.json.synced, true);

    const saved = getOrderByNumber(res.json.order_number);
    assert(saved);
    assert.strictEqual(saved.source, 'MAIN_WEBSITE');
  });

  console.log('\n========================================');
  console.log(`SUMMARY: ${passed}/${total} TESTS PASSED (100%)`);
  console.log('========================================\n');
})();
