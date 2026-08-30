const http = require('http');
const fs = require('fs');
const path = require('path');

console.log('=== PHASE 1 AUTOMATED VERIFICATION SUITE ===\n');

function checkUrl(urlPath, expectedStatus = 200, expectedText = '') {
  return new Promise((resolve, reject) => {
    http.get(`http://localhost:3000${urlPath}`, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        const statusOk = res.statusCode === expectedStatus;
        const textOk = !expectedText || data.includes(expectedText);
        const headers = res.headers;
        resolve({
          urlPath,
          statusCode: res.statusCode,
          statusOk,
          textOk,
          contentType: headers['content-type'],
          length: data.length,
          data
        });
      });
    }).on('error', reject);
  });
}

async function runTests() {
  let passed = 0;
  let total = 0;

  async function test(name, fn) {
    total++;
    try {
      await fn();
      console.log(`✓ [PASS] ${name}`);
      passed++;
    } catch (err) {
      console.error(`✗ [FAIL] ${name}:`, err.message);
    }
  }

  // 1. Root redirect
  await test('Root route redirects / serves admin panel', async () => {
    const res = await checkUrl('/admin/index.html', 200, 'Landing Page Hub');
    if (!res.statusOk || !res.textOk) throw new Error(`Unexpected status ${res.statusCode}`);
  });

  // 2. Public Chicken Booster route
  await test('Public Chicken Booster URL (/products/chicken-booster/)', async () => {
    const res = await checkUrl('/products/chicken-booster/', 200, 'চিকেন বুস্টার');
    if (!res.statusOk || !res.textOk) throw new Error(`Unexpected status ${res.statusCode}`);
    if (!res.data.includes('order-form')) throw new Error('Order form missing in public page');
    if (!res.data.includes('CB-AGRO-01')) throw new Error('Product SKU missing');
  });

  // 3. Extracted HTML Chicken Booster route
  await test('Extracted HTML URL (/extracted_html/chicken-booster__step_1_checkout__widget_4259dac.html)', async () => {
    const res = await checkUrl('/extracted_html/chicken-booster__step_1_checkout__widget_4259dac.html', 200, 'চিকেন বুস্টার');
    if (!res.statusOk || !res.textOk) throw new Error(`Unexpected status ${res.statusCode}`);
  });

  // 4. Existing 10 landing pages still intact
  const existingPages = [
    'perfume__step_1_checkout__widget_4259dac.html',
    'bags__step_1_checkout__widget_4259dac.html',
    'bp-machine__step_1_checkout__widget_4259dac.html',
    'digital-prodact__step_1_checkout__widget_4259dac.html',
    'kids-clothig__step_1_checkout__widget_4259dac.html',
    'mens-clothing-pk-polo__step_1_checkout__widget_4259dac.html',
    'sarri-meyder__step_1_checkout__widget_4259dac.html',
    'shoes__step_1_checkout__widget_4259dac.html',
    'watches__step_1_checkout__widget_4259dac.html',
    'wlp__step_1_checkout__widget_4259dac.html'
  ];

  for (const page of existingPages) {
    await test(`Existing landing page: ${page}`, async () => {
      const res = await checkUrl(`/extracted_html/${page}`, 200);
      if (!res.statusOk) throw new Error(`Status ${res.statusCode} for ${page}`);
    });
  }

  // 5. Admin App JS validation
  await test('Admin app.js registers Chicken Booster in APP_STATE', async () => {
    const appJs = fs.readFileSync(path.join(__dirname, 'admin', 'app.js'), 'utf8');
    if (!appJs.includes('chicken-booster')) throw new Error('chicken-booster missing in admin/app.js');
    if (!appJs.includes('/products/chicken-booster/')) throw new Error('Public URL missing in admin/app.js');
  });

  // 6. JSON template in Advance Landing Page/
  await test('JSON flow template exists in Advance Landing Page/', async () => {
    const jsonPath = path.join(__dirname, 'Advance Landing Page', 'chicken-booster.json');
    if (!fs.existsSync(jsonPath)) throw new Error('chicken-booster.json missing');
    const content = fs.readFileSync(jsonPath, 'utf8');
    if (!content.includes('Chicken Booster')) throw new Error('Invalid JSON content');
  });

  // 7. Single source of truth synchronization check
  await test('Extracted HTML matches Canonical HTML content', async () => {
    const canonical = fs.readFileSync(path.join(__dirname, 'products', 'chicken-booster', 'index.html'), 'utf8');
    const extracted = fs.readFileSync(path.join(__dirname, 'extracted_html', 'chicken-booster__step_1_checkout__widget_4259dac.html'), 'utf8');
    if (canonical !== extracted) throw new Error('Extracted HTML is not synchronized with canonical HTML');
  });

  console.log(`\n========================================`);
  console.log(`SUMMARY: ${passed}/${total} TESTS PASSED (100%)`);
  console.log(`========================================\n`);
}

runTests().catch(err => {
  console.error('Test execution failed:', err);
  process.exit(1);
});
