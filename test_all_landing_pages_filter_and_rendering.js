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
  console.log('🧪 TESTING ADMIN LANDING PAGES: ALL / FILTERS / DYNAMIC PAGES');
  console.log('================================================================\n');

  // Step 1: Admin Login
  console.log('--- Step 1: Admin Login ---');
  const loginRes = await sendReq(8000, '/api/admin/login', 'POST', {
    email: 'admin@gmail.com',
    password: 'admin123'
  });
  assert.strictEqual(loginRes.status, 200);
  assert.strictEqual(loginRes.json.success, true);
  const cookieHeader = (loginRes.headers['set-cookie'] || []).map(c => c.split(';')[0]).join('; ');
  const token = loginRes.json.token;
  console.log('✅ Admin login succeeded');

  const authHeaders = {
    Cookie: cookieHeader,
    Authorization: `Bearer ${token}`
  };

  // Step 2: Fetch All Landing Pages (Filter: All)
  console.log('\n--- Step 2: GET /api/admin/landing-pages (Filter: All) ---');
  const allRes = await sendReq(8000, '/api/admin/landing-pages', 'GET', null, authHeaders);
  assert.strictEqual(allRes.status, 200);
  assert.strictEqual(allRes.json.success, true);
  assert.ok(Array.isArray(allRes.json.pages));

  const allSlugs = allRes.json.pages.map(p => p.slug);
  console.log('Total landing pages found in All:', allRes.json.pages.length);
  console.log('Slugs in All:', allSlugs);

  // Requirement 1: chicken-booster must appear in All
  assert.ok(allSlugs.includes('chicken-booster'), 'chicken-booster MUST appear in All list');
  console.log('✅ chicken-booster is present in All list');

  // Requirement 2: mediascope-it must remain unchanged
  assert.ok(allSlugs.includes('mediascope-it'), 'mediascope-it MUST remain in All list');
  console.log('✅ mediascope-it is present in All list');

  // Step 3: Test Status Filters
  console.log('\n--- Step 3: Test Status Filters ---');
  const pubRes = await sendReq(8000, '/api/admin/landing-pages?status=published', 'GET', null, authHeaders);
  assert.strictEqual(pubRes.status, 200);
  assert.ok(pubRes.json.pages.every(p => p.status === 'published'));
  console.log(`✅ Filter "Published" returned ${pubRes.json.pages.length} published pages`);

  const draftRes = await sendReq(8000, '/api/admin/landing-pages?status=draft', 'GET', null, authHeaders);
  assert.strictEqual(draftRes.status, 200);
  assert.ok(draftRes.json.pages.every(p => p.status === 'draft'));
  console.log(`✅ Filter "Draft" returned ${draftRes.json.pages.length} draft pages`);

  // Step 4: Requirement 3: All future landing pages must automatically appear in All
  console.log('\n--- Step 4: Create a New Landing Page & Verify It Automatically Appears in All ---');
  const testSlug = 'auto-test-future-page-' + Date.now();
  const createRes = await sendReq(8000, '/api/admin/landing-pages', 'POST', {
    name: 'Future Test Product Page',
    slug: testSlug,
    status: 'published',
    theme: 'universal',
    product_name: 'Future Test Product'
  }, authHeaders);
  assert.strictEqual(createRes.status, 201);
  assert.strictEqual(createRes.json.success, true);
  const createdId = createRes.json.page_id || (createRes.json.page && createRes.json.page.id);
  console.log(`✅ Created new landing page (ID: ${createdId}, Slug: ${testSlug})`);

  // Re-fetch All
  const afterCreateRes = await sendReq(8000, '/api/admin/landing-pages', 'GET', null, authHeaders);
  const afterSlugs = afterCreateRes.json.pages.map(p => p.slug);
  assert.ok(afterSlugs.includes(testSlug), 'Future/new landing pages MUST automatically appear in All');
  assert.ok(afterSlugs.includes('chicken-booster'), 'chicken-booster still in All');
  assert.ok(afterSlugs.includes('mediascope-it'), 'mediascope-it still in All');
  console.log('✅ Newly created page automatically appears in All list alongside existing pages!');

  // Cleanup test page
  await sendReq(8000, `/api/admin/landing-pages/${createdId}`, 'DELETE', null, authHeaders);
  console.log('✅ Cleaned up temporary test landing page');

  // Step 5: Verify /product/chicken-booster still works (HTTP 200)
  console.log('\n--- Step 5: Verify /product/chicken-booster ---');
  const cbPublicRes = await sendReq(8000, '/product/chicken-booster');
  assert.strictEqual(cbPublicRes.status, 200);
  console.log('✅ GET /product/chicken-booster returns HTTP 200');

  // Step 6: Verify /product/mediascope-it still works (HTTP 200)
  console.log('\n--- Step 6: Verify /product/mediascope-it ---');
  const msPublicRes = await sendReq(8000, '/product/mediascope-it');
  assert.strictEqual(msPublicRes.status, 200);
  console.log('✅ GET /product/mediascope-it returns HTTP 200');

  console.log('\n================================================================');
  console.log('🎉 ALL REQUIREMENTS VERIFIED AND VALIDATED SUCCESSFULLY!');
  console.log('================================================================\n');
})();
