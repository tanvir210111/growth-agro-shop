const http = require('http');
const assert = require('assert');

function sendReq(port, path, method = 'GET', data = null, headers = {}) {
  return new Promise((resolve, reject) => {
    const payload = data ? (typeof data === 'string' ? data : JSON.stringify(data)) : null;
    const reqHeaders = { ...headers };
    if (payload) {
      reqHeaders['Content-Type'] = 'application/json';
      reqHeaders['Content-Length'] = Buffer.byteLength(payload);
    }
    const req = http.request({
      hostname: '127.0.0.1',
      port,
      path,
      method,
      headers: reqHeaders,
      timeout: 5000
    }, res => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        let json = null;
        try { json = JSON.parse(body); } catch (e) {}
        resolve({
          status: res.statusCode,
          headers: res.headers,
          body,
          json
        });
      });
    });
    req.on('timeout', () => {
      req.destroy();
      reject(new Error(`Timeout requesting ${path}`));
    });
    req.on('error', reject);
    if (payload) req.write(payload);
    req.end();
  });
}

(async () => {
  console.log('======================================================');
  console.log('🧪 TESTING ADMIN DASHBOARD PAGE LOAD & LIFECYCLE');
  console.log('======================================================\n');

  // Test 1: Fresh /admin/ load
  console.log('--- Test 1: GET /admin/ ---');
  const res1 = await sendReq(8000, '/admin/');
  assert.strictEqual(res1.status, 200);
  assert.ok(res1.body.includes('Admin Panel Login'), 'Must contain login screen markup');
  assert.ok(res1.body.includes('app.js?v=9.4'), 'Must serve latest v=9.4 script');
  console.log('✅ /admin/ loads successfully in HTTP 200');

  // Test 2: Asset Availability
  console.log('\n--- Test 2: Asset Availability & Placeholder Image ---');
  const jsRes = await sendReq(8000, '/admin/app.js?v=9.4');
  assert.strictEqual(jsRes.status, 200);
  assert.ok(jsRes.body.length > 50000);

  const cssRes = await sendReq(8000, '/admin/style.css?v=9.0');
  assert.strictEqual(cssRes.status, 200);

  const imgRes = await sendReq(8000, '/images/placeholder.webp');
  assert.strictEqual(imgRes.status, 200, 'Placeholder webp image must exist and return 200');
  console.log('✅ Core assets and /images/placeholder.webp return 200 OK');

  // Test 3: Unauthenticated /api/admin/me
  console.log('\n--- Test 3: GET /api/admin/me (Unauthenticated) ---');
  const meRes1 = await sendReq(8000, '/api/admin/me');
  assert.strictEqual(meRes1.status, 401);
  console.log('✅ Unauthenticated check returns 401 cleanly without hanging');

  // Test 4: Admin Login Flow
  console.log('\n--- Test 4: POST /api/admin/login ---');
  const loginRes = await sendReq(8000, '/api/admin/login', 'POST', {
    email: 'admin@gmail.com',
    password: 'admin123'
  });
  assert.strictEqual(loginRes.status, 200);
  assert.strictEqual(loginRes.json.success, true);
  const cookieHeader = (loginRes.headers['set-cookie'] || []).map(c => c.split(';')[0]).join('; ');
  const token = loginRes.json.token;
  console.log('✅ Admin login succeeded, session cookie and token acquired');

  // Test 5: Authenticated Session Check
  console.log('\n--- Test 5: GET /api/admin/me (Authenticated) ---');
  const meRes2 = await sendReq(8000, '/api/admin/me', 'GET', null, {
    Cookie: cookieHeader
  });
  assert.strictEqual(meRes2.status, 200);
  assert.strictEqual(meRes2.json.authenticated, true);
  assert.strictEqual(meRes2.json.user.email, 'admin@gmail.com');
  console.log('✅ Authenticated session resolved cleanly');

  // Test 6: Orders List
  console.log('\n--- Test 6: GET /api/orders ---');
  const ordersRes = await sendReq(8000, '/api/orders', 'GET', null, {
    Cookie: cookieHeader,
    Authorization: `Bearer ${token}`
  });
  assert.strictEqual(ordersRes.status, 200);
  assert.strictEqual(ordersRes.json.success, true);
  assert.ok(Array.isArray(ordersRes.json.orders));
  console.log(`✅ Orders loaded cleanly: ${ordersRes.json.orders.length} orders found`);

  // Test 7: Landing Pages
  console.log('\n--- Test 7: GET /api/admin/landing-pages ---');
  const lpRes = await sendReq(8000, '/api/admin/landing-pages', 'GET', null, {
    Cookie: cookieHeader,
    Authorization: `Bearer ${token}`
  });
  assert.strictEqual(lpRes.status, 200);
  assert.strictEqual(lpRes.json.success, true);
  console.log('✅ Landing pages loaded cleanly');

  // Test 8: Analytics Overview
  console.log('\n--- Test 8: GET /api/admin/analytics/overview ---');
  const analyticsRes = await sendReq(8000, '/api/admin/analytics/overview?range=30d', 'GET', null, {
    Cookie: cookieHeader,
    Authorization: `Bearer ${token}`
  });
  assert.strictEqual(analyticsRes.status, 200);
  assert.strictEqual(analyticsRes.json.success, true);
  console.log('✅ Analytics overview loaded cleanly');

  console.log('\n======================================================');
  console.log('🎉 ALL ADMIN DASHBOARD LIFECYCLE TESTS PASSED!');
  console.log('======================================================\n');
})();
