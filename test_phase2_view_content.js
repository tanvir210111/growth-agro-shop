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

  // Test 3: Chicken Booster Landing Page ViewContent
  console.log('\n--- Test 3: Chicken Booster Landing Page ViewContent ---');
  const cbRes = await sendReq(8000, '/product/chicken-booster');
  assert.strictEqual(cbRes.status, 200);
  assert.ok(cbRes.body.includes("fbq('track', 'PageView')"), 'PageView must fire');
  assert.ok(cbRes.body.includes("fbq('track', 'ViewContent'"), 'ViewContent must fire');
  assert.ok(cbRes.body.includes("content_ids: ['chicken-booster']"), 'content_ids must match chicken-booster');
  assert.ok(cbRes.body.includes("content_type: 'product'"), 'content_type must be product');
  assert.ok(cbRes.body.includes("currency: 'BDT'"), 'currency must be BDT');

  const cbViewContentCount = (cbRes.body.match(/fbq\('track',\s*'ViewContent'/g) || []).length;
  assert.strictEqual(cbViewContentCount, 1, 'ViewContent must fire exactly once on Chicken Booster');
  console.log('✅ Chicken Booster landing page fires ViewContent with dynamic parameters (exactly once).');

  // Test 4: MediaScope IT Landing Page ViewContent
  console.log('\n--- Test 4: MediaScope IT Landing Page ViewContent ---');
  const msRes = await sendReq(8000, '/product/mediascope-it');
  assert.strictEqual(msRes.status, 200);
  assert.ok(msRes.body.includes("fbq('track', 'PageView')"), 'PageView must fire');
  assert.ok(msRes.body.includes("fbq('track', 'ViewContent'"), 'ViewContent must fire');
  assert.ok(msRes.body.includes("content_ids: ['mediascope-it']"), 'content_ids must match mediascope-it');

  const msViewContentCount = (msRes.body.match(/fbq\('track',\s*'ViewContent'/g) || []).length;
  assert.strictEqual(msViewContentCount, 1, 'ViewContent must fire exactly once on MediaScope IT');
  console.log('✅ MediaScope IT landing page fires ViewContent with dynamic parameters (exactly once).');

  // Test 5: Dynamic Future Landing Page ViewContent
  console.log('\n--- Test 5: Dynamic Future Landing Page ViewContent ---');
  const testSlug = 'dynamic-vc-test-' + Date.now();
  const createRes = await sendReq(8000, '/api/admin/landing-pages', 'POST', {
    name: 'Dynamic ViewContent Product',
    slug: testSlug,
    status: 'published',
    theme: 'universal',
    product_name: 'Dynamic ViewContent Serum',
    product_id: 'serum-001',
    content: {
      packages: [
        { id: 'pkg-1', name: '1 Bottle', price: 1250, weight: '50ml' }
      ]
    }
  }, authHeaders);
  assert.strictEqual(createRes.status, 201);
  const tempPageId = createRes.json.page_id;

  const dynamicPageRes = await sendReq(8000, `/product/${testSlug}`);
  assert.strictEqual(dynamicPageRes.status, 200);
  assert.ok(dynamicPageRes.body.includes("fbq('track', 'PageView')"), 'PageView must fire on dynamic page');
  assert.ok(dynamicPageRes.body.includes("fbq('track', 'ViewContent'"), 'ViewContent must fire on dynamic page');
  assert.ok(dynamicPageRes.body.includes("content_ids: ['serum-001']"), 'content_ids must match dynamic product_id');
  assert.ok(dynamicPageRes.body.includes("content_name: 'Dynamic ViewContent Serum'"), 'content_name must match dynamic product_name');
  assert.ok(dynamicPageRes.body.includes("value: 1250"), 'value must match first package price 1250');
  assert.ok(dynamicPageRes.body.includes("currency: 'BDT'"), 'currency must be BDT');

  const dynamicViewContentCount = (dynamicPageRes.body.match(/fbq\('track',\s*'ViewContent'/g) || []).length;
  assert.strictEqual(dynamicViewContentCount, 1, 'ViewContent must fire exactly once on dynamic page');
  console.log('✅ Dynamic future landing page automatically fires ViewContent with custom package price and product info.');

  // Clean up dynamic test page
  await sendReq(8000, `/api/admin/landing-pages/${tempPageId}`, 'DELETE', null, authHeaders);
  console.log('✅ Cleaned up dynamic test page');

  // Test 6: Homepage Verification (PageView fires, ViewContent DOES NOT fire)
  console.log('\n--- Test 6: Homepage Verification ---');
  const homeRes = await sendReq(8000, '/');
  assert.strictEqual(homeRes.status, 200);
  assert.ok(homeRes.body.includes("fbq('track', 'PageView')"), 'PageView must fire on home');
  assert.strictEqual(homeRes.body.includes("ViewContent"), false, 'ViewContent must NOT fire on home');
  console.log('✅ Homepage correctly fires PageView only, without ViewContent.');

  console.log('\n================================================================');
  console.log('🎉 ALL PHASE 2 VIEWCONTENT INTEGRATION TESTS PASSED SUCCESSFULLY!');
  console.log('================================================================\n');
})();
