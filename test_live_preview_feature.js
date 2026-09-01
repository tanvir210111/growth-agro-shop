const http = require('http');
const assert = require('assert');
const fs = require('fs');

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
  console.log('🧪 TESTING LANDING PAGE LIVE PREVIEW FEATURE & MODAL RENDERING');
  console.log('================================================================\n');

  // Test 1: Admin Login
  console.log('--- Test 1: Admin Login ---');
  const loginRes = await sendReq(8000, '/api/admin/login', 'POST', {
    email: 'admin@gmail.com',
    password: 'admin123'
  });
  assert.strictEqual(loginRes.status, 200);
  const cookieHeader = (loginRes.headers['set-cookie'] || []).map(c => c.split(';')[0]).join('; ');
  const token = loginRes.json.token;
  const authHeaders = { Cookie: cookieHeader, Authorization: `Bearer ${token}` };
  console.log('✅ Admin authenticated successfully');

  // Test 2: Chicken Booster Live Preview URL
  console.log('\n--- Test 2: Chicken Booster Preview URL (/product/chicken-booster?preview=true) ---');
  const cbRes = await sendReq(8000, '/product/chicken-booster?preview=true');
  assert.strictEqual(cbRes.status, 200);
  assert.ok(cbRes.body.includes('চিকেন বুস্টার') || cbRes.body.includes('Chicken Booster'), 'Should contain Chicken Booster title/text');
  assert.ok(cbRes.body.length > 20000, 'Should contain complete landing page HTML payload');
  console.log(`✅ Chicken Booster preview loaded successfully (${cbRes.body.length} bytes)`);

  // Test 3: MediaScope IT Live Preview URL
  console.log('\n--- Test 3: MediaScope IT Preview URL (/product/mediascope-it?preview=true) ---');
  const msRes = await sendReq(8000, '/product/mediascope-it?preview=true');
  assert.strictEqual(msRes.status, 200);
  assert.ok(msRes.body.includes('MediaScope IT'), 'Should contain MediaScope IT title/text');
  assert.ok(msRes.body.length > 20000, 'Should contain complete landing page HTML payload');
  console.log(`✅ MediaScope IT preview loaded successfully (${msRes.body.length} bytes)`);

  // Test 4: Dynamic Future Landing Page Preview
  console.log('\n--- Test 4: Dynamic Future Landing Page Creation & Live Preview ---');
  const futureSlug = 'future-preview-test-' + Date.now();
  const createRes = await sendReq(8000, '/api/admin/landing-pages', 'POST', {
    name: 'Future Preview Test Product',
    slug: futureSlug,
    status: 'draft',
    theme: 'universal',
    product_name: 'Future Preview Test Product'
  }, authHeaders);
  assert.strictEqual(createRes.status, 201);
  const futureId = createRes.json.page_id;
  console.log(`✅ Created new landing page with ID ${futureId} and slug "${futureSlug}"`);

  // Test Live Preview URL for this newly created page (even in draft status with ?preview=true)
  const futurePreviewRes = await sendReq(8000, `/product/${futureSlug}?preview=true`);
  assert.strictEqual(futurePreviewRes.status, 200);
  assert.ok(futurePreviewRes.body.includes('Future Preview Test Product'), 'Should contain new product name');
  console.log(`✅ Dynamic future page preview loaded successfully (${futurePreviewRes.body.length} bytes)`);

  // Clean up
  await sendReq(8000, `/api/admin/landing-pages/${futureId}`, 'DELETE', null, authHeaders);
  console.log('✅ Cleaned up dynamic test page');

  // Test 5: Verify Source Code & JS logic
  console.log('\n--- Test 5: Verify JS Live Preview Implementation in admin/app.js & index.html ---');
  const appJs = fs.readFileSync('admin/app.js', 'utf8');
  const indexHtml = fs.readFileSync('admin/index.html', 'utf8');

  assert.ok(appJs.includes('openLivePreviewModal'), 'openLivePreviewModal must exist in app.js');
  assert.ok(appJs.includes('previewLandingPageById'), 'previewLandingPageById must exist in app.js');
  assert.ok(appJs.includes('closeLivePreviewModal'), 'closeLivePreviewModal must exist in app.js');
  assert.ok(appJs.includes('setPreviewDevice'), 'setPreviewDevice must exist in app.js');
  assert.ok(appJs.includes('/product/${encodeURIComponent(slug)}?preview=true'), 'Must use canonical /product/{slug}?preview=true');
  assert.ok(indexHtml.includes('id="lpPreviewLoading"'), 'lpPreviewLoading must exist in index.html');
  assert.ok(indexHtml.includes('id="lpPreviewIframe"'), 'lpPreviewIframe must exist in index.html');
  console.log('✅ Code inspection passed: Preview modal, dynamic slug encoding, responsive devices & loader in place.');

  console.log('\n================================================================');
  console.log('🎉 ALL LIVE PREVIEW TESTS PASSED SUCCESSFULLY!');
  console.log('================================================================\n');
})();
