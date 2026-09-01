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
  console.log('🧪 TESTING PHASE 1 — CENTRALIZED META PIXEL FOUNDATION');
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
  console.log('✅ Admin login succeeded');

  // Test 2: GET Marketing Settings
  console.log('\n--- Test 2: GET /api/admin/settings/marketing ---');
  const getInitialRes = await sendReq(8000, '/api/admin/settings/marketing', 'GET', null, authHeaders);
  assert.strictEqual(getInitialRes.status, 200);
  assert.ok(getInitialRes.json && getInitialRes.json.success);
  console.log('✅ Current marketing settings retrieved:', getInitialRes.json.settings);

  // Test 3: POST Valid Pixel ID
  console.log('\n--- Test 3: POST /api/admin/settings/marketing (Raw ID) ---');
  const testPixelId = '1793041018387711';
  const postRes = await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: testPixelId
  }, authHeaders);
  assert.strictEqual(postRes.status, 200);
  assert.strictEqual(postRes.json.settings.facebook_pixel, testPixelId);
  console.log('✅ Meta Pixel ID saved successfully:', postRes.json.settings.facebook_pixel);

  // Test 4: Verify Full Snippet Normalization
  console.log('\n--- Test 4: POST /api/admin/settings/marketing (Full Snippet Normalization) ---');
  const snippet = `<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init', '${testPixelId}');fbq('track', 'PageView');</script>`;
  const postSnippetRes = await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: snippet
  }, authHeaders);
  assert.strictEqual(postSnippetRes.status, 200);
  assert.strictEqual(postSnippetRes.json.settings.facebook_pixel, testPixelId);
  console.log('✅ Full script snippet properly normalized to Pixel ID:', postSnippetRes.json.settings.facebook_pixel);

  // Test 5: Main E-Commerce Website Rendering
  console.log('\n--- Test 5: Main Website HTML Meta Pixel Rendering ---');
  const mainRes = await sendReq(8000, '/');
  assert.strictEqual(mainRes.status, 200);
  assert.ok(mainRes.body.includes('connect.facebook.net/en_US/fbevents.js'), 'Must load fbevents.js');
  assert.ok(mainRes.body.includes(`fbq('init', '${testPixelId}')`), 'Must initialize configured Pixel ID');
  assert.ok(mainRes.body.includes("fbq('track', 'PageView')"), 'Must fire PageView');
  const fbqCountMain = (mainRes.body.match(/connect\.facebook\.net\/en_US\/fbevents\.js/g) || []).length;
  assert.strictEqual(fbqCountMain, 1, 'fbevents.js must be injected exactly once on main website');
  console.log('✅ Main E-Commerce website contains exactly one Meta Pixel initialization with PageView.');

  // Test 6: Chicken Booster Landing Page Rendering
  console.log('\n--- Test 6: /product/chicken-booster HTML Meta Pixel Rendering ---');
  const cbRes = await sendReq(8000, '/product/chicken-booster');
  assert.strictEqual(cbRes.status, 200);
  assert.ok(cbRes.body.includes('connect.facebook.net/en_US/fbevents.js'));
  assert.ok(cbRes.body.includes(`fbq('init', '${testPixelId}')`));
  assert.ok(cbRes.body.includes("fbq('track', 'PageView')"));
  const fbqCountCb = (cbRes.body.match(/connect\.facebook\.net\/en_US\/fbevents\.js/g) || []).length;
  assert.strictEqual(fbqCountCb, 1, 'fbevents.js must be injected exactly once on chicken-booster');
  console.log('✅ Chicken Booster landing page contains exactly one Meta Pixel initialization with PageView.');

  // Test 7: MediaScope IT Landing Page Rendering
  console.log('\n--- Test 7: /product/mediascope-it HTML Meta Pixel Rendering ---');
  const msRes = await sendReq(8000, '/product/mediascope-it');
  assert.strictEqual(msRes.status, 200);
  assert.ok(msRes.body.includes('connect.facebook.net/en_US/fbevents.js'));
  assert.ok(msRes.body.includes(`fbq('init', '${testPixelId}')`));
  assert.ok(msRes.body.includes("fbq('track', 'PageView')"));
  console.log('✅ MediaScope IT landing page contains Meta Pixel initialization with PageView.');

  // Test 8: Dynamic Future Landing Page Rendering
  console.log('\n--- Test 8: Dynamic Future Landing Page Automatic Meta Pixel Inclusion ---');
  const futureSlug = 'future-pixel-test-' + Date.now();
  const createRes = await sendReq(8000, '/api/admin/landing-pages', 'POST', {
    name: 'Future Pixel Test Page',
    slug: futureSlug,
    status: 'published',
    theme: 'universal',
    product_name: 'Future Pixel Product'
  }, authHeaders);
  assert.strictEqual(createRes.status, 201);
  const futureId = createRes.json.page_id;

  const futureRes = await sendReq(8000, `/product/${futureSlug}`);
  assert.strictEqual(futureRes.status, 200);
  assert.ok(futureRes.body.includes('connect.facebook.net/en_US/fbevents.js'));
  assert.ok(futureRes.body.includes(`fbq('init', '${testPixelId}')`));
  assert.ok(futureRes.body.includes("fbq('track', 'PageView')"));
  console.log('✅ Newly created future landing page automatically includes Meta Pixel!');

  // Cleanup future test page
  await sendReq(8000, `/api/admin/landing-pages/${futureId}`, 'DELETE', null, authHeaders);
  console.log('✅ Cleaned up dynamic test page');

  // Test 9: Disable Pixel (Empty Configuration) & Verify Removal
  console.log('\n--- Test 9: Disable Pixel & Verify No Pixel Script is Rendered ---');
  await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: ''
  }, authHeaders);

  const mainNoPixel = await sendReq(8000, '/');
  assert.strictEqual(mainNoPixel.body.includes('connect.facebook.net/en_US/fbevents.js'), false);
  assert.strictEqual(mainNoPixel.body.includes("fbq('init'"), false);

  const cbNoPixel = await sendReq(8000, '/product/chicken-booster');
  assert.strictEqual(cbNoPixel.body.includes('connect.facebook.net/en_US/fbevents.js'), false);
  assert.strictEqual(cbNoPixel.body.includes("fbq('init'"), false);
  console.log('✅ When Pixel is cleared in admin, no Pixel scripts are rendered.');

  // Test 10: Restore Pixel ID
  console.log('\n--- Test 10: Restore Meta Pixel ID in Database ---');
  await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: testPixelId
  }, authHeaders);
  const getFinalRes = await sendReq(8000, '/api/admin/settings/marketing', 'GET', null, authHeaders);
  assert.strictEqual(getFinalRes.json.settings.facebook_pixel, testPixelId);
  console.log('✅ Meta Pixel ID restored in database:', getFinalRes.json.settings.facebook_pixel);

  console.log('\n================================================================');
  console.log('🎉 ALL PHASE 1 META PIXEL FOUNDATION TESTS PASSED SUCCESSFULLY!');
  console.log('================================================================\n');
})();
