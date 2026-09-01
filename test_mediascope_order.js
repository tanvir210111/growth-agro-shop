const http = require('http');
const { DatabaseSync } = require('node:sqlite');
const path = require('path');
const assert = require('node:assert');

// 1. Seed mediascope-it with packages ['pkg-1', 'pkg-2'] (price 500 & 950) and free delivery above 1500
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

console.log('✅ 1. Seeded mediascope-it in DB with packages [pkg-1: 500, pkg-2: 950]');

function postJson(pathUrl, payload) {
  return new Promise((resolve, reject) => {
    const data = JSON.stringify(payload);
    const req = http.request({
      hostname: '127.0.0.1',
      port: 3000,
      path: pathUrl,
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
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

(async () => {
  // Test Scenario: Frontend sends 'pkg-1pc' x2 and 'pkg-2pcs' x1 with outside delivery
  const payload = {
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
    customerName: 'Real Order Verification',
    customer_name: 'Real Order Verification',
    name: 'Real Order Verification',
    phone: '01711' + String(Date.now()).slice(-6),
    customer_phone: '01711' + String(Date.now()).slice(-6),
    address: 'Chittagong Port, Chittagong, Bangladesh',
    shipping_address: 'Chittagong Port, Chittagong, Bangladesh',
    source: 'LANDING_PAGE',
    advance_paid: 0,
    paymentMethod: 'Cash on Delivery',
    notes: 'Landing Page (mediascope-it)'
  };

  const res = await postJson('/api/orders', payload);
  console.log('Status Code:', res.status);
  console.log('Order Output:', res.data);

  const ord = res.data?.order;
  assert.strictEqual(res.status, 201, 'Status code must be 201');
  assert.strictEqual(ord.subtotal, 1950, 'Subtotal must be 1950 (500*2 + 950*1)');
  assert.strictEqual(ord.delivery_charge, 0, 'Outside delivery must be 0 because subtotal 1950 >= threshold 1500');
  assert.strictEqual(ord.total, 1950, 'Total must be 1950');
  assert.strictEqual(ord.quantity, 3, 'Quantity must be 3');

  console.log('\n🎉 SUCCESS: 1pc x2 (৳1000) + 2pcs x1 (৳950) = Subtotal ৳1950, Delivery ৳0, Total ৳1950!');
})();
