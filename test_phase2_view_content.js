const http = require('http');
const assert = require('assert');

function sendReq(port, path, method = 'GET', data = null, headers = {}) {
  return new Promise((resolve, reject) => {
    const payload = data ? JSON.stringify(data) : null;
    const reqHeaders = { ...headers };
    if (payload) {
      reqHeaders['Content-Type'] = 'application/json';
      reqHeaders['Content-Length'] = Buffer.byteLength(payload);
    }
    const req = http.request({ hostname: '127.0.0.1', port, path, method, headers: reqHeaders, timeout: 6000 }, res => {
      let body = '';
      res.on('data', d => body += d);
      res.on('end', () => {
        let json = null;
        try { json = JSON.parse(body); } catch (e) {}
        resolve({ status: res.statusCode, headers: res.headers, json, body });
      });
    });
    req.on('error', reject);
    if (payload) req.write(payload);
    req.end();
  });
}

(async () => {
  console.log('================================================================');
  console.log('🧪 TESTING PHASE 2 — META PIXEL VIEWCONTENT EVENT');
  console.log('================================================================\n');

  // Test 1: Admin Login & Setting Verification
  console.log('--- Test 1: Admin Login & Marketing Settings ---');
  const loginRes = await sendReq(8000, '/api/admin/login', 'POST', {
    email: 'admin@gmail.com',
    password: 'admin123'
  });
  assert.strictEqual(loginRes.status, 200);
  const cookieHeader = (loginRes.headers['set-cookie'] || []).map(c => c.split(';')[0]).join('; ');
  const token = loginRes.json.token;
  const authHeaders = { Cookie: cookieHeader, Authorization: `Bearer ${token}` };

  const settingsRes = await sendReq(8000, '/api/admin/settings/marketing', 'GET', null, authHeaders);
  assert.strictEqual(settingsRes.status, 200);
  const pixelId = settingsRes.json.settings.facebook_pixel;
  assert.strictEqual(pixelId, '1793041018387711');
  console.log('✅ Admin verified. Current Meta Pixel ID:', pixelId);

  // Test 2: Main E-Commerce Website Product Detail Page ViewContent
  console.log('\n--- Test 2: Main Website Product Page ViewContent ---');
  const mainProductRes = await sendReq(8000, '/product/girls-red-butterfly-printed-t-shirt-floral-shorts-set');
  assert.strictEqual(mainProductRes.status, 200);
  assert.ok(mainProductRes.body.includes("fbq('track', 'PageView')"), 'PageView must fire');
  assert.ok(mainProductRes.body.includes("fbq('track', 'ViewContent'"), 'ViewContent must fire');
  assert.ok(mainProductRes.body.includes("content_ids: ['girls-red-butterfly-printed-t-shirt-floral-shorts-set']"), 'content_ids must match product slug');
  assert.ok(mainProductRes.body.includes("content_type: 'product'"), 'content_type must be product');
  assert.ok(mainProductRes.body.includes("value: 790"), 'value must match product price');
  assert.ok(mainProductRes.body.includes("currency: 'BDT'"), 'currency must be BDT');

  const mainViewContentCount = (mainProductRes.body.match(/fbq\('track',\s*'ViewContent'/g) || []).length;
  assert.strictEqual(mainViewContentCount, 1, 'ViewContent must fire exactly once on main product page');
  console.log('✅ Main website product page fires ViewContent with exact dynamic parameters (exactly once).');

  // Test 3: Chicken Booster Landing Page (PageView only, No ViewContent)
  console.log('\n--- Test 3: Chicken Booster Landing Page (PageView only, No ViewContent) ---');
  const cbRes = await sendReq(8000, '/product/chicken-booster');
  assert.strictEqual(cbRes.status, 200);
  assert.ok(cbRes.body.includes("fbq('track', 'PageView')"), 'PageView must fire');
  assert.ok(!cbRes.body.includes("ViewContent"), 'ViewContent must NOT fire on landing page');
  console.log('✅ Chicken Booster landing page fires PageView only, ViewContent is correctly absent.');

  // Test 4: MediaScope IT Landing Page (ViewContent Removed)
  console.log('\n--- Test 4: MediaScope IT Landing Page (PageView only, No ViewContent) ---');
  const msRes = await sendReq(8000, '/product/mediascope-it');
  assert.strictEqual(msRes.status, 200);
  assert.ok(msRes.body.includes("fbq('track', 'PageView')"), 'PageView must fire');
  assert.ok(!msRes.body.includes("ViewContent"), 'ViewContent must NOT fire on landing page');
  console.log('✅ MediaScope IT landing page fires PageView only, ViewContent is correctly absent.');

  // Test 5: Dynamic Future Landing Page (ViewContent Removed)
  console.log('\n--- Test 5: Dynamic Future Landing Page (PageView only, No ViewContent) ---');
  const testSlug = 'dynamic-vc-test-' + Date.now();
  const createRes = await sendReq(8000, '/api/admin/landing-pages', 'POST', {
    name: 'Dynamic Test Serum Product',
    slug: testSlug,
    status: 'published',
    theme: 'universal',
    product_name: 'Dynamic Agro Serum',
    product_id: 'serum-001',
    content: {
      packages: [
        { id: 'pkg-1', name: 'Serum 50ml', price: 1250, weight: '50ml' }
      ]
    }
  }, authHeaders);
  assert.strictEqual(createRes.status, 201);
  const tempPageId = createRes.json.page_id;

  const dynamicPageRes = await sendReq(8000, `/product/${testSlug}`);
  assert.strictEqual(dynamicPageRes.status, 200);
  assert.ok(dynamicPageRes.body.includes("fbq('track', 'PageView')"), 'PageView must fire');
  assert.ok(!dynamicPageRes.body.includes("ViewContent"), 'ViewContent must NOT fire on dynamic landing page');
  console.log('✅ Dynamic future landing page fires PageView only, ViewContent is correctly absent.');

  // Cleanup dynamic test page
  await sendReq(8000, `/api/admin/landing-pages/${tempPageId}`, 'DELETE', null, authHeaders);
  console.log('✅ Cleaned up dynamic test page');

  // Test 6: Homepage Verification
  console.log('\n--- Test 6: Homepage Verification ---');
  const homeRes = await sendReq(8000, '/');
  assert.strictEqual(homeRes.status, 200);
  assert.ok(homeRes.body.includes("fbq('track', 'PageView')"), 'Homepage must fire PageView');
  assert.ok(!homeRes.body.includes("ViewContent"), 'Homepage must NOT fire ViewContent');
  console.log('✅ Homepage correctly fires PageView only, without ViewContent.');

  console.log('\n================================================================');
  console.log('🎉 ALL PHASE 2 VIEWCONTENT VERIFICATION TESTS PASSED SUCCESSFULLY!');
  console.log('================================================================\n');
})();
