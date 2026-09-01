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
  console.log('🧪 TESTING LANDING PAGE MANAGEMENT ACTION COLUMN & DELETE BUTTON');
  console.log('================================================================\n');

  // Test 1: Check admin/app.js source code
  console.log('--- Test 1: Source code verification ---');
  const appJsCode = fs.readFileSync('admin/app.js', 'utf8');
  const publicAppJsCode = fs.readFileSync('E Commerce Baby/public/admin/app.js', 'utf8');

  assert.ok(!appJsCode.includes('${!isMaster ?'), 'isMaster delete restriction must not exist in admin/app.js');
  assert.ok(!publicAppJsCode.includes('${!isMaster ?'), 'isMaster delete restriction must not exist in public/admin/app.js');
  assert.ok(appJsCode.includes('promptDeleteLandingPage'), 'promptDeleteLandingPage must exist in admin/app.js');
  console.log('✅ Source code check passed: No special-case restrictions on Delete action.');

  // Test 2: Admin Login & Fetch Landing Pages
  console.log('\n--- Test 2: Admin Login & API Inspection ---');
  const loginRes = await sendReq(8000, '/api/admin/login', 'POST', {
    email: 'admin@gmail.com',
    password: 'admin123'
  });
  assert.strictEqual(loginRes.status, 200);
  const cookieHeader = (loginRes.headers['set-cookie'] || []).map(c => c.split(';')[0]).join('; ');
  const token = loginRes.json.token;
  const authHeaders = { Cookie: cookieHeader, Authorization: `Bearer ${token}` };

  const listRes = await sendReq(8000, '/api/admin/landing-pages', 'GET', null, authHeaders);
  assert.strictEqual(listRes.status, 200);
  assert.strictEqual(listRes.json.success, true);
  assert.ok(Array.isArray(listRes.json.pages));
  console.log(`✅ Loaded ${listRes.json.pages.length} pages from API:`);
  listRes.json.pages.forEach(p => console.log(`   - ID: ${p.id}, Slug: ${p.slug}, Theme: ${p.theme}, Status: ${p.status}`));

  // Test 3: Create temporary page, test delete handler API, verify removal
  console.log('\n--- Test 3: Verify existing Delete API handler ---');
  const tempSlug = 'temp-delete-test-' + Date.now();
  const createRes = await sendReq(8000, '/api/admin/landing-pages', 'POST', {
    name: 'Temporary Delete Test Page',
    slug: tempSlug,
    status: 'published',
    theme: 'universal'
  }, authHeaders);
  assert.strictEqual(createRes.status, 201);
  const tempId = createRes.json.page_id;
  console.log(`✅ Created temporary landing page (ID: ${tempId}, Slug: ${tempSlug})`);

  // Delete it using DELETE /api/admin/landing-pages/{id}
  const delRes = await sendReq(8000, `/api/admin/landing-pages/${tempId}`, 'DELETE', null, authHeaders);
  assert.strictEqual(delRes.status, 200);
  assert.strictEqual(delRes.json.success, true);
  console.log(`✅ Successfully deleted temporary landing page using DELETE API handler`);

  // Verify it is gone
  const afterListRes = await sendReq(8000, '/api/admin/landing-pages', 'GET', null, authHeaders);
  const slugsAfter = afterListRes.json.pages.map(p => p.slug);
  assert.ok(!slugsAfter.includes(tempSlug), 'Deleted page must no longer be in list');
  console.log('✅ Verified page was deleted and no longer appears in API response');

  console.log('\n================================================================');
  console.log('🎉 ACTION COLUMN DELETE BUTTON TEST PASSED SUCCESSFULLY!');
  console.log('================================================================\n');
})();
