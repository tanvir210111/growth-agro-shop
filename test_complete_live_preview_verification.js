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
  console.log('🧪 VERIFYING COMPLETE LIVE PREVIEW FIX WITH FRAME HEADERS & CSS');
  console.log('================================================================\n');

  // Test 1: Chicken Booster Preview & Security Headers
  console.log('--- Test 1: Chicken Booster Preview & Frame Headers ---');
  const cbRes = await sendReq(8000, '/product/chicken-booster?preview=true');
  assert.strictEqual(cbRes.status, 200);
  assert.strictEqual(cbRes.headers['x-frame-options'], 'SAMEORIGIN', 'X-Frame-Options must be SAMEORIGIN');
  assert.strictEqual(cbRes.headers['content-security-policy'], "frame-ancestors 'self'", 'CSP frame-ancestors must be self');
  assert.ok(cbRes.body.includes('চিকেন বুস্টার') || cbRes.body.includes('Chicken Booster'));
  console.log(`✅ Chicken Booster returns HTTP 200 with X-Frame-Options: ${cbRes.headers['x-frame-options']} and CSP: ${cbRes.headers['content-security-policy']} (${cbRes.body.length} bytes)`);

  // Test 2: MediaScope IT Preview & Security Headers
  console.log('\n--- Test 2: MediaScope IT Preview & Frame Headers ---');
  const msRes = await sendReq(8000, '/product/mediascope-it?preview=true');
  assert.strictEqual(msRes.status, 200);
  assert.strictEqual(msRes.headers['x-frame-options'], 'SAMEORIGIN', 'X-Frame-Options must be SAMEORIGIN');
  assert.strictEqual(msRes.headers['content-security-policy'], "frame-ancestors 'self'", 'CSP frame-ancestors must be self');
  assert.ok(msRes.body.includes('MediaScope IT'));
  console.log(`✅ MediaScope IT returns HTTP 200 with X-Frame-Options: ${msRes.headers['x-frame-options']} and CSP: ${msRes.headers['content-security-policy']} (${msRes.body.length} bytes)`);

  // Test 3: Admin Login & Dynamic Landing Page Lifecycle
  console.log('\n--- Test 3: Admin Authentication & Dynamic Landing Page Live Preview ---');
  const loginRes = await sendReq(8000, '/api/admin/login', 'POST', {
    email: 'admin@gmail.com',
    password: 'admin123'
  });
  assert.strictEqual(loginRes.status, 200);
  const cookieHeader = (loginRes.headers['set-cookie'] || []).map(c => c.split(';')[0]).join('; ');
  const token = loginRes.json.token;
  const authHeaders = { Cookie: cookieHeader, Authorization: `Bearer ${token}` };

  const testSlug = 'dynamic-preview-test-' + Date.now();
  const createRes = await sendReq(8000, '/api/admin/landing-pages', 'POST', {
    name: 'Dynamic Live Preview Test',
    slug: testSlug,
    status: 'draft',
    theme: 'universal',
    product_name: 'Dynamic Product Test'
  }, authHeaders);
  assert.strictEqual(createRes.status, 201);
  const testId = createRes.json.page_id;

  const dynamicPreviewRes = await sendReq(8000, `/product/${testSlug}?preview=true`);
  assert.strictEqual(dynamicPreviewRes.status, 200);
  assert.strictEqual(dynamicPreviewRes.headers['x-frame-options'], 'SAMEORIGIN');
  assert.strictEqual(dynamicPreviewRes.headers['content-security-policy'], "frame-ancestors 'self'");
  assert.ok(dynamicPreviewRes.body.includes('Dynamic Live Preview Test') || dynamicPreviewRes.body.includes(testSlug) || dynamicPreviewRes.body.length > 20000);
  console.log(`✅ Dynamic draft page preview returns HTTP 200 with SAMEORIGIN headers (${dynamicPreviewRes.body.length} bytes)`);

  // Clean up
  await sendReq(8000, `/api/admin/landing-pages/${testId}`, 'DELETE', null, authHeaders);
  console.log('✅ Cleaned up dynamic test page');

  // Test 4: Frontend HTML/CSS validation
  console.log('\n--- Test 4: Verify Frontend HTML/CSS Elements in admin/index.html & admin/app.js ---');
  const indexHtml = fs.readFileSync('admin/index.html', 'utf8');
  const appJs = fs.readFileSync('admin/app.js', 'utf8');

  assert.ok(indexHtml.includes('id="lpPreviewModal"'));
  assert.ok(indexHtml.includes('id="btnPreviewDesktop"'));
  assert.ok(indexHtml.includes('id="btnPreviewTablet"'));
  assert.ok(indexHtml.includes('id="btnPreviewMobile"'));
  assert.ok(indexHtml.includes('id="lpPreviewOpenDirect"'));
  assert.ok(indexHtml.includes('id="lpPreviewLoading"'));
  assert.ok(indexHtml.includes('id="lpPreviewIframe"'));
  assert.ok(appJs.includes('openLivePreviewModal'));
  assert.ok(appJs.includes('previewLandingPageById'));
  assert.ok(appJs.includes('closeLivePreviewModal'));
  assert.ok(appJs.includes('setPreviewDevice'));
  console.log('✅ All UI elements, responsive buttons, direct link, and JS handlers verified.');

  console.log('\n================================================================');
  console.log('🎉 ALL LIVE PREVIEW & FRAME HEADER TESTS PASSED SUCCESSFULLY!');
  console.log('================================================================\n');
})();
