const fs = require('fs');
const path = require('path');
const http = require('http');

function makeRequest(options) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        try {
          resolve({ status: res.statusCode, body: JSON.parse(body), raw: body });
        } catch (e) {
          resolve({ status: res.statusCode, body: null, raw: body });
        }
      });
    });
    req.on('error', reject);
    req.end();
  });
}

async function runTests() {
  console.log('--- PHASE 14 ADMIN MANAGEMENT SEARCH AUTO-FILL TESTS ---');
  let passed = 0;
  let failed = 0;

  function assert(condition, name) {
    if (condition) {
      console.log(`✅ PASS: ${name}`);
      passed++;
    } else {
      console.error(`❌ FAIL: ${name}`);
      failed++;
    }
  }

  // 1. Inspect public/admin/index.html
  const htmlPath = path.join(__dirname, 'E Commerce Baby', 'public', 'admin', 'index.html');
  const htmlContent = fs.readFileSync(htmlPath, 'utf8');

  assert(!htmlContent.includes('value="admin@gmail.com"'), 'HTML does NOT contain default value="admin@gmail.com"');
  assert(htmlContent.includes('id="adminSearchInput"'), 'HTML contains adminSearchInput');
  assert(htmlContent.includes('autocomplete="off"'), 'adminSearchInput has autocomplete="off"');
  assert(htmlContent.includes('type="search"'), 'adminSearchInput has type="search"');
  assert(htmlContent.includes('name="admin_search_filter"'), 'adminSearchInput has non-login name attribute');
  assert(htmlContent.includes('<option value="">All Status</option>'), 'Status filter has "All Status" default option');

  // 2. Inspect public/admin/app.js
  const jsPath = path.join(__dirname, 'E Commerce Baby', 'public', 'admin', 'app.js');
  const jsContent = fs.readFileSync(jsPath, 'utf8');

  assert(!jsContent.includes("adminSearchInput.value = 'admin@gmail.com'"), 'JS does not inject admin@gmail.com into adminSearchInput');
  assert(!jsContent.includes('adminSearchInput.value = APP_STATE.currentUser'), 'JS does not inject currentUser email into adminSearchInput');
  assert(jsContent.includes('adminUsers: []'), 'APP_STATE.adminUsers is initialized to clean empty array');

  // 3. Backend API Test: Verify admin list still loads correctly from database
  const token = 'adm_' + 'a'.repeat(32);
  const resp = await makeRequest({
    hostname: '127.0.0.1',
    port: 8000,
    path: '/api/admin/admins',
    method: 'GET',
    headers: {
      'Accept': 'application/json',
      'x-admin-token': token
    }
  });

  assert(resp.status === 200, 'GET /api/admin/admins returns 200 OK');
  assert(resp.body && resp.body.success === true, 'Response indicates success: true');
  assert(resp.body.count >= 1, `Admins count is at least 1 (got ${resp.body.count})`);
  assert(resp.body.admins[0].email === 'admin@gmail.com', 'Primary admin is admin@gmail.com');
  assert(!resp.body.admins[0].hasOwnProperty('password'), 'Password is never returned in API');

  console.log(`\n========================================`);
  console.log(`TOTAL: Passed: ${passed}, Failed: ${failed}`);
  console.log(`========================================`);
  process.exit(failed > 0 ? 1 : 0);
}

runTests();
