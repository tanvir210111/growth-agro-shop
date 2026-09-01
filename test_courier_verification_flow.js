const http = require('http');
const assert = require('node:assert');

console.log('======================================================');
console.log('🧪 TESTING ADMIN BD COURIER VERIFICATION / CHECK FLOW');
console.log('======================================================\n');

function sendPost(port, pathUrl, payload, headers = {}) {
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
        'Content-Length': Buffer.byteLength(data),
        ...headers
      }
    }, (res) => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        try {
          resolve({ status: res.statusCode, data: JSON.parse(body), headers: res.headers });
        } catch (e) {
          resolve({ status: res.statusCode, raw: body, headers: res.headers });
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
          resolve({ status: res.statusCode, data: JSON.parse(body), headers: res.headers });
        } catch (e) {
          resolve({ status: res.statusCode, raw: body, headers: res.headers });
        }
      });
    });
    req.on('error', reject);
    req.end();
  });
}

(async () => {
  // Step 1: Admin Login to obtain session cookie
  console.log('--- Step 1: Admin Login ---');
  const loginRes = await sendPost(8000, '/api/admin/login', {
    email: 'admin@gmail.com',
    password: 'admin123'
  });
  console.log('Login Status:', loginRes.status);
  assert.strictEqual(loginRes.status, 200, 'Admin login should return 200 OK');
  const cookies = loginRes.headers['set-cookie'] || [];
  const cookieHeader = cookies.map(c => c.split(';')[0]).join('; ');
  console.log('✅ Admin authenticated successfully');

  // Step 2: Create a Landing Page Order with real customer data
  console.log('\n--- Step 2: Create Landing Page Order with Real Phone & Customer ---');
  const testPhone = '01712345678';
  const testCustomer = 'Tanvir Ahmed';
  const orderRes = await sendPost(8000, '/api/orders', {
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
    customerName: testCustomer,
    customer_name: testCustomer,
    name: testCustomer,
    phone: testPhone,
    customer_phone: testPhone,
    address: 'House 12, Road 4, Dhanmondi, Dhaka',
    shipping_address: 'House 12, Road 4, Dhanmondi, Dhaka',
    source: 'LANDING_PAGE',
    paymentMethod: 'Cash on Delivery'
  }, { Cookie: cookieHeader });

  console.log('Order Creation Status:', orderRes.status);
  assert.strictEqual(orderRes.status, 201);
  const orderNumber = orderRes.data.order.order_number;
  console.log('✅ Created Order:', orderNumber);

  // Give internal sync a short moment to settle
  await new Promise(r => setTimeout(r, 600));

  // Step 3: Fetch Admin Orders and verify phone and customer name are preserved
  console.log('\n--- Step 3: Fetch Admin Orders List ---');
  const ordersListRes = await sendGet(8000, '/api/orders', { Cookie: cookieHeader });
  assert.strictEqual(ordersListRes.status, 200);
  const foundOrder = ordersListRes.data.orders.find(o => o.order_number === orderNumber);
  assert.ok(foundOrder, 'Order must exist in /api/orders');
  console.log('Found Order details:', {
    order_number: foundOrder.order_number,
    customer_name: foundOrder.customer_name,
    phone: foundOrder.phone,
    address: foundOrder.address,
    total: foundOrder.total
  });
  assert.strictEqual(foundOrder.customer_name, testCustomer, 'Customer name must be Tanvir Ahmed');
  assert.strictEqual(foundOrder.phone, testPhone, 'Phone number must be 01712345678');
  console.log('✅ Admin order list returns valid customer phone and name');

  // Step 4: Test Courier Check with explicit phone
  console.log('\n--- Step 4: Test POST /api/admin/fraud/courier-check with phone ---');
  const courierRes1 = await sendPost(8000, '/api/admin/fraud/courier-check', {
    phone: testPhone
  }, { Cookie: cookieHeader });
  console.log('Courier Check Status (Direct Phone):', courierRes1.status, courierRes1.data);
  assert.strictEqual(courierRes1.status, 200);
  assert.notStrictEqual(courierRes1.data.message, 'Phone number is required.');
  console.log('✅ Courier check received phone and queried BD Courier API.');

  // Step 5: Test Courier Check with invoice fallback (resilient lookup)
  console.log('\n--- Step 5: Test POST /api/admin/fraud/courier-check with invoice fallback ---');
  const courierRes2 = await sendPost(8000, '/api/admin/fraud/courier-check', {
    invoice: orderNumber
  }, { Cookie: cookieHeader });
  console.log('Courier Check Status (Invoice Fallback):', courierRes2.status, courierRes2.data);
  assert.strictEqual(courierRes2.status, 200);
  assert.notStrictEqual(courierRes2.data.message, 'Phone number is required.');
  console.log('✅ Courier check succeeded using order invoice fallback.');

  console.log('\n======================================================');
  console.log('🎉 ALL BD COURIER CHECK & ADMIN FLOW TESTS PASSED!');
  console.log('======================================================\n');
})();
