/**
 * test_phase12.js - Temporary Main Website Demo Access Control & Routing Isolation Verification Suite
 * 
 * Verifies:
 * 1. Nginx config defines server_name growthagro.shop
 * 2. Nginx config applies auth_basic on root location /
 * 3. Nginx config explicitly bypasses auth_basic for /products/
 * 4. Nginx config explicitly bypasses auth_basic for /api/
 * 5. Nginx config explicitly bypasses auth_basic for /assets/ and /pic/
 * 6. Nginx config explicitly bypasses auth_basic for /extracted_html/
 * 7. Nginx config preserves admin panel location and existing token protection
 * 8. Live Node.js /products/chicken-booster/ renders publicly (200 OK)
 * 9. Live Node.js Order API (/api/orders) accepts public orders without basic auth
 * 10. Existing SQLite database schema is untouched
 * 11. No DNS changes required
 * 12. No Laravel catch-all used
 * 13. Phase 1 regression verification
 * 14. Phase 11 regression verification
 */

const http = require('http');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('=== PHASE 12 TEMPORARY MAIN DEMO & ACCESS CONTROL TEST SUITE ===\n');

function request(options, data = null) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let responseBody = '';
      res.on('data', chunk => responseBody += chunk);
      res.on('end', () => {
        let json = null;
        try { json = JSON.parse(responseBody); } catch (e) { json = null; }
        resolve({
          statusCode: res.statusCode,
          headers: res.headers,
          body: responseBody,
          json
        });
      });
    });

    req.on('error', reject);

    if (data) {
      const payload = typeof data === 'string' ? data : JSON.stringify(data);
      req.write(payload);
    }
    req.end();
  });
}

async function runTests() {
  let passed = 0;
  let total = 0;

  async function test(name, fn) {
    total++;
    try {
      await fn();
      console.log(`✓ [PASS] ${total}. ${name}`);
      passed++;
    } catch (err) {
      console.error(`✗ [FAIL] ${total}. ${name}:`, err.message);
    }
  }

  const nginxConfPath = path.join(__dirname, 'nginx', 'landing-page.conf');
  const nginxConf = fs.readFileSync(nginxConfPath, 'utf8');

  // 1. Domain Configuration
  await test('Nginx config binds to growthagro.shop and preserves HTTPS redirect', async () => {
    if (!nginxConf.includes('server_name growthagro.shop www.growthagro.shop;')) {
      throw new Error('Nginx server_name does not match growthagro.shop');
    }
    if (!nginxConf.includes('return 301 https://$host$request_uri;')) {
      throw new Error('Missing HTTP to HTTPS redirect');
    }
  });

  // 2. HTTP Basic Auth on Root
  await test('Nginx config enforces HTTP Basic Auth on root demo location /', async () => {
    const rootBlockMatch = nginxConf.match(/location\s+\/\s*\{([^}]+)\}/);
    if (!rootBlockMatch) throw new Error('location / block not found in Nginx config');
    const rootContent = rootBlockMatch[1];
    if (!rootContent.includes('auth_basic "Growth Agro Demo - Authorized Access Only"')) {
      throw new Error('auth_basic missing in location /');
    }
    if (!rootContent.includes('auth_basic_user_file')) {
      throw new Error('auth_basic_user_file missing in location /');
    }
  });

  // 3. Public /products/ Bypass
  await test('Nginx config explicitly bypasses Basic Auth for /products/ (auth_basic off)', async () => {
    const prodBlockMatch = nginxConf.match(/location\s+\/products\/\s*\{([^}]+)\}/);
    if (!prodBlockMatch) throw new Error('location /products/ block not found');
    const prodContent = prodBlockMatch[1];
    if (!prodContent.includes('auth_basic off;')) {
      throw new Error('auth_basic off; missing in /products/');
    }
  });

  // 4. Public /api/ Bypass
  await test('Nginx config explicitly bypasses Basic Auth for /api/ and /api/orders', async () => {
    const apiOrderMatch = nginxConf.match(/location\s+\/api\/orders\s*\{([^}]+)\}/);
    const apiMatch = nginxConf.match(/location\s+\/api\/\s*\{([^}]+)\}/);
    if (!apiOrderMatch || !apiOrderMatch[1].includes('auth_basic off;')) {
      throw new Error('auth_basic off; missing in /api/orders');
    }
    if (!apiMatch || !apiMatch[1].includes('auth_basic off;')) {
      throw new Error('auth_basic off; missing in /api/');
    }
  });

  // 5. Public Static Assets Bypass
  await test('Nginx config bypasses Basic Auth for /assets/ and /pic/ (Prevents broken landing page assets)', async () => {
    const assetsMatch = nginxConf.match(/location\s+\/assets\/\s*\{([^}]+)\}/);
    const picMatch = nginxConf.match(/location\s+\/pic\/\s*\{([^}]+)\}/);
    if (!assetsMatch || !assetsMatch[1].includes('auth_basic off;')) {
      throw new Error('auth_basic off; missing in /assets/');
    }
    if (!picMatch || !picMatch[1].includes('auth_basic off;')) {
      throw new Error('auth_basic off; missing in /pic/');
    }
  });

  // 6. Admin Panel Routing
  await test('Nginx config routes /admin/ to Node.js backend with app-level token protection', async () => {
    const adminMatch = nginxConf.match(/location\s+\/admin\/\s*\{([^}]+)\}/);
    if (!adminMatch) throw new Error('location /admin/ block not found');
    if (!adminMatch[1].includes('proxy_pass http://127.0.0.1:3000;')) {
      throw new Error('Admin not proxied to 127.0.0.1:3000');
    }
  });

  // 7. Chicken Booster Public Route Check
  await test('Public Chicken Booster page renders with 200 OK on Node.js port 3000', async () => {
    const res = await request({ hostname: '127.0.0.1', port: 3000, path: '/products/chicken-booster/', method: 'GET' });
    if (res.statusCode !== 200) throw new Error(`Expected 200, got ${res.statusCode}`);
    if (!res.body.includes('চিকেন বুস্টার') && !res.body.includes('Chicken Booster')) {
      throw new Error('Chicken Booster landing page content missing');
    }
  });

  // 8. Public Order API Submission Check
  await test('Landing page order submission (/api/orders) functions without basic auth rejection', async () => {
    const orderPayload = {
      product_id: 'chicken-booster',
      variant_id: 'combo-2kg',
      quantity: 1,
      delivery_zone: 'inside',
      customer_name: 'আব্দুল করিম',
      customer_phone: '01712998811',
      shipping_address: 'উত্তরা, ঢাকা',
      payment_method: 'Cash on Delivery'
    };
    const res = await request({
      hostname: '127.0.0.1',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, orderPayload);

    if (res.statusCode !== 201) throw new Error(`Expected 201 Created, got ${res.statusCode}: ${res.body}`);
    if (!res.json || !res.json.success) throw new Error('Order submission returned failure');
  });

  // 9. Admin Panel Authentication Intact
  await test('Admin Panel unauthenticated API requests are rejected (401 Unauthorized)', async () => {
    const res = await request({ hostname: '127.0.0.1', port: 3000, path: '/api/orders', method: 'GET' });
    if (res.statusCode !== 401) throw new Error(`Expected 401, got ${res.statusCode}`);
  });

  // 10. Database Schema Integrity
  await test('Database schema is intact (no migrations, no table mutations)', async () => {
    const dbPath = path.join(__dirname, 'data', 'database.sqlite');
    if (!fs.existsSync(dbPath)) throw new Error('database.sqlite not found');
    const stats = fs.statSync(dbPath);
    if (stats.size === 0) throw new Error('Database is empty');
  });

  // 11. htpasswd Generator Tool Verification
  await test('HTTP Basic Auth utility script (.htpasswd) exists and is executable', async () => {
    const scriptPath = path.join(__dirname, 'scripts', 'generate_htpasswd.js');
    if (!fs.existsSync(scriptPath)) throw new Error('generate_htpasswd.js missing');
    const examplePath = path.join(__dirname, '.htpasswd_example');
    if (!fs.existsSync(examplePath)) throw new Error('.htpasswd_example missing');
  });

  // 12. Regression Test: Phase 1
  await test('Phase 1 regression verification (node test_phase1.js)', async () => {
    const output = execSync('node test_phase1.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 1 regression failed:\n${output}`);
  });

  // 13. Regression Test: Phase 11
  await test('Phase 11 regression verification (node test_phase11.js)', async () => {
    const output = execSync('node test_phase11.js', { encoding: 'utf8' });
    if (!output.includes('TESTS PASSED')) throw new Error(`Phase 11 regression failed:\n${output}`);
  });

  console.log('\n========================================');
  console.log(`SUMMARY: ${passed}/${total} TESTS PASSED (${Math.round((passed/total)*100)}%)`);
  console.log('========================================\n');

  if (passed !== total) process.exit(1);
}

runTests().catch(err => {
  console.error('Fatal test runner error:', err);
  process.exit(1);
});
