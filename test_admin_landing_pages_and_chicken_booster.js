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
      timeout: 6000
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
  console.log('================================================================');
  console.log('🧪 TESTING ISSUE 1 (LANDING PAGES API) & ISSUE 2 (/product/chicken-booster)');
  console.log('================================================================\n');

  // Step 1: Admin Login
  console.log('--- Step 1: Admin Login ---');
  const loginRes = await sendReq(8000, '/api/admin/login', 'POST', {
    email: 'admin@gmail.com',
    password: 'admin123'
  });
  assert.strictEqual(loginRes.status, 200);
  assert.strictEqual(loginRes.json.success, true);
  assert.ok(loginRes.json.token, 'Must return token');
  const cookieHeader = (loginRes.headers['set-cookie'] || []).map(c => c.split(';')[0]).join('; ');
  const token = loginRes.json.token;
  console.log('✅ Admin login succeeded, token and cookie acquired');

  // Step 2: Test GET /api/admin/landing-pages with Session Cookie
  console.log('\n--- Step 2: GET /api/admin/landing-pages (Cookie Auth) ---');
  const lpRes1 = await sendReq(8000, '/api/admin/landing-pages', 'GET', null, {
    Cookie: cookieHeader
  });
  assert.strictEqual(lpRes1.status, 200);
  assert.strictEqual(lpRes1.json.success, true);
  assert.ok(Array.isArray(lpRes1.json.pages), 'Must return array of pages');
  console.log(`✅ Loaded ${lpRes1.json.pages.length} landing pages via Cookie Auth`);

  // Step 3: Test GET /api/admin/landing-pages with Bearer Token
  console.log('\n--- Step 3: GET /api/admin/landing-pages (Bearer Token Auth) ---');
  const lpRes2 = await sendReq(8000, '/api/admin/landing-pages', 'GET', null, {
    Authorization: `Bearer ${token}`
  });
  assert.strictEqual(lpRes2.status, 200);
  assert.strictEqual(lpRes2.json.success, true);
  console.log(`✅ Loaded ${lpRes2.json.pages.length} landing pages via Bearer Token Auth`);

  // Step 4: Verify mediascope-it exists in list
  console.log('\n--- Step 4: Verify mediascope-it in Landing Pages List ---');
  const mediaScope = lpRes2.json.pages.find(p => p.slug === 'mediascope-it');
  assert.ok(mediaScope, 'mediascope-it must be present in landing pages list');
  console.log('✅ mediascope-it landing page found in admin list:', {
    name: mediaScope.name,
    slug: mediaScope.slug,
    status: mediaScope.status,
    visitors: mediaScope.visitors,
    orders: mediaScope.orders,
    revenue: mediaScope.revenue
  });

  // Step 5: Test /product/chicken-booster (Issue 2)
  console.log('\n--- Step 5: GET /product/chicken-booster (Issue 2) ---');
  const cbRes = await sendReq(8000, '/product/chicken-booster');
  console.log('Status code for /product/chicken-booster:', cbRes.status);
  assert.strictEqual(cbRes.status, 200, 'Must return HTTP 200, not 404');
  assert.ok(cbRes.body.includes('চিকেন বুস্টার') || cbRes.body.includes('Chicken Booster'), 'Must contain chicken booster content');
  console.log('✅ /product/chicken-booster returns HTTP 200 and renders landing page successfully!');

  // Step 6: Test /product/mediascope-it
  console.log('\n--- Step 6: GET /product/mediascope-it ---');
  const msRes = await sendReq(8000, '/product/mediascope-it');
  assert.strictEqual(msRes.status, 200);
  console.log('✅ /product/mediascope-it returns HTTP 200');

  // Step 7: Test /products/chicken-booster (Legacy static campaign URL)
  console.log('\n--- Step 7: GET /products/chicken-booster (Legacy static campaign URL) ---');
  const staticCbRes = await sendReq(8000, '/products/chicken-booster');
  assert.strictEqual(staticCbRes.status, 200);
  console.log('✅ /products/chicken-booster returns HTTP 200');

  console.log('\n================================================================');
  console.log('🎉 BOTH ISSUE 1 AND ISSUE 2 VERIFIED AND FULLY RESOLVED!');
  console.log('================================================================\n');
})();
