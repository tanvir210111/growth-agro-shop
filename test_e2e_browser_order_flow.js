const http = require('http');
const { DatabaseSync } = require('node:sqlite');
const path = require('path');
const assert = require('node:assert');

console.log('====================================================');
console.log('🧪 RUNNING END-TO-END BROWSER ORDER SUBMISSION FLOW');
console.log('====================================================\n');

// 1. Seed mediascope-it in Laravel database
const laravelDb = new DatabaseSync(path.join(__dirname, 'E Commerce Baby', 'database', 'database.sqlite'));
laravelDb.prepare('DELETE FROM landing_pages WHERE slug = ?').run('mediascope-it');
laravelDb.prepare(`
  INSERT INTO landing_pages (
    name, slug, status, theme, product_name, content, delivery_config, created_at, updated_at
  ) VALUES (?, ?, 'published', 'universal', 'MediaScope IT Smart Device', ?, ?, datetime('now'), datetime('now'))
`).run(
  'MediaScope IT Smart Device',
  'mediascope-it',
  JSON.stringify({
    packages: [
      { id: 'pkg-1', name: '১ পিস স্মার্ট ডিভাইস', price: 500, old_price: 700, weight: '1 Pc', is_active: true },
      { id: 'pkg-2', name: '২ পিস কম্বো প্যাক', price: 950, old_price: 1400, weight: '2 Pcs', is_active: true }
    ]
  }),
  JSON.stringify({
    delivery_type: 'paid',
    charge_inside_dhaka: 60,
    charge_outside_dhaka: 120,
    same_charge_everywhere: false,
    free_delivery_above: true,
    free_delivery_threshold: 1500
  })
);

console.log('✅ 1. Seeded /product/mediascope-it in Laravel DB');

function sendPost(port, pathUrl, payload) {
  return new Promise((resolve, reject) => {
    const data = JSON.stringify(payload);
    const req = http.request({
      hostname: '127.0.0.1',
      port: port,
      path: pathUrl,
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Content-Length': Buffer.byteLength(data)
      }
    }, (res) => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        try {
          resolve({ status: res.statusCode, data: JSON.parse(body) });
        } catch (e) {
          resolve({ status: res.statusCode, raw: body });
        }
      });
    });
    req.on('error', reject);
    req.write(data);
    req.end();
  });
}

function sendGet(port, pathUrl, headers = {}) {
  return new Promise((resolve, reject) => {
    const req = http.request({
      hostname: '127.0.0.1',
      port: port,
      path: pathUrl,
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        ...headers
      }
    }, (res) => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        try {
          resolve({ status: res.statusCode, data: JSON.parse(body) });
        } catch (e) {
          resolve({ status: res.statusCode, raw: body });
        }
      });
    });
    req.on('error', reject);
    req.end();
  });
}

(async () => {
  // Test A: Browser payload submitted to Laravel Port 8000
  console.log('\n--- Test A: Browser Order Payload via Laravel Port 8000 ---');
  const browserPayload = {
    productId: 'mediascope-it',
    product_id: 'mediascope-it',
    variantId: 'pkg-1pc',
    variant_id: 'pkg-1pc',
    quantity: 3,
    items: [
      { productId: 'mediascope-it', variantId: 'pkg-1pc', quantity: 2, name: '১ পিস স্মার্ট ডিভাইস', price: 500 },
      { productId: 'mediascope-it', variantId: 'pkg-2pcs', quantity: 1, name: '২ পিস কম্বো প্যাক', price: 950 }
    ],
    deliveryZone: 'outside',
    delivery_zone: 'outside',
    customerName: 'Browser Flow Customer',
    customer_name: 'Browser Flow Customer',
    name: 'Browser Flow Customer',
    phone: '01711998877',
    customer_phone: '01711998877',
    address: 'Halishahar, Chittagong, Bangladesh',
    shipping_address: 'Halishahar, Chittagong, Bangladesh',
    source: 'LANDING_PAGE',
    advance_paid: 0,
    customer: { name: 'Browser Flow Customer', phone: '01711998877', address: 'Halishahar, Chittagong, Bangladesh', delivery_zone: 'outside' },
    paymentMethod: 'Cash on Delivery',
    notes: 'Landing Page (mediascope-it)'
  };

  const resLaravel = await sendPost(8000, '/api/orders', browserPayload);
  console.log('Laravel Port 8000 Status:', resLaravel.status);
  assert.strictEqual(resLaravel.status, 201, 'Laravel /api/orders must return 201 Created');
  assert.strictEqual(resLaravel.data.success, true, 'Success flag must be true');
  assert.strictEqual(resLaravel.data.order.quantity, 3, 'Total quantity must be 3');
  assert.strictEqual(resLaravel.data.order.subtotal, 1950, 'Subtotal must be 1950 (500*2 + 950*1)');
  assert.strictEqual(resLaravel.data.order.delivery_charge, 0, 'Outside delivery charge must be 0 (Free Delivery above 1500)');
  assert.strictEqual(resLaravel.data.order.total, 1950, 'Total must be 1950');
  console.log('✅ Passed Test A: Order created via Laravel endpoint: ' + resLaravel.data.order.order_number);

  // Test B: Browser payload submitted directly to Node Port 3000
  console.log('\n--- Test B: Browser Order Payload via Node Port 3000 ---');
  const resNode = await sendPost(3000, '/api/orders', browserPayload);
  console.log('Node Port 3000 Status:', resNode.status);
  assert.strictEqual(resNode.status, 201, 'Node /api/orders must return 201 Created');
  assert.strictEqual(resNode.data.success, true, 'Success flag must be true');
  assert.strictEqual(resNode.data.order.quantity, 3, 'Total quantity must be 3');
  assert.strictEqual(resNode.data.order.subtotal, 1950, 'Subtotal must be 1950');
  assert.strictEqual(resNode.data.order.delivery_charge, 0, 'Delivery charge must be 0');
  assert.strictEqual(resNode.data.order.total, 1950, 'Total must be 1950');
  console.log('✅ Passed Test B: Order created via Node endpoint: ' + resNode.data.order.order_number);

  // Test C: Courier check via Laravel Port 8000
  console.log('\n--- Test C: Courier Risk Check via Laravel Port 8000 ---');
  const resCourier = await sendPost(8000, '/api/checkout/courier-check', { phone: '01711998877', deliveryZone: 'outside' });
  console.log('Courier Check Status:', resCourier.status);
  assert.strictEqual(resCourier.status, 200, 'Courier check must return 200');
  assert.strictEqual(resCourier.data.success, true);
  console.log('✅ Passed Test C: Courier check returned successfully');

  // Test D: Verify Order in Admin SQLite Orders database
  console.log('\n--- Test D: Verify Order Persistence & Attribution ---');
  const savedOrder = laravelDb.prepare('SELECT * FROM orders WHERE invoice_no = ?').get(resLaravel.data.order.order_number);
  assert.ok(savedOrder, 'Order must exist in Laravel orders table');
  assert.strictEqual(savedOrder.total_amount, 1950, 'Persisted total amount must be 1950');
  assert.strictEqual(savedOrder.source_type, 'landing_page', 'Source type must be landing_page');
  console.log('✅ Passed Test D: Order accurately persisted in Laravel SQLite DB with invoice: ' + savedOrder.invoice_no);

  console.log('\n====================================================');
  console.log('🎉 ALL END-TO-END BROWSER ORDER FLOW TESTS PASSED!');
  console.log('====================================================\n');
})();
