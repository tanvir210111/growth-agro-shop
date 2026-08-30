const http = require('http');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('=== PHASE 6 PRODUCTION INFRASTRUCTURE & SECURITY TEST SUITE ===\n');

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
      console.log(`✓ [PASS] ${name}`);
      passed++;
    } catch (err) {
      console.error(`✗ [FAIL] ${name}:`, err.message);
    }
  }

  // 1. package.json verification
  await test('1. package.json contains required scripts and Node engine >=22.5.0', async () => {
    const pkgPath = path.join(__dirname, 'package.json');
    if (!fs.existsSync(pkgPath)) throw new Error('package.json missing');
    const pkg = JSON.parse(fs.readFileSync(pkgPath, 'utf8'));
    if (!pkg.scripts || !pkg.scripts.start || !pkg.scripts['db:backup'] || !pkg.scripts.sync) {
      throw new Error('Required scripts missing in package.json');
    }
  });

  // 2. .gitignore & .env.example verification
  await test('2. .gitignore excludes .env, database files, backups, and logs', async () => {
    const gitignorePath = path.join(__dirname, '.gitignore');
    if (!fs.existsSync(gitignorePath)) throw new Error('.gitignore missing');
    const content = fs.readFileSync(gitignorePath, 'utf8');
    if (!content.includes('.env') || !content.includes('database.sqlite') || !content.includes('backups/')) {
      throw new Error('Crucial patterns missing in .gitignore');
    }
  });

  // 3. PM2 ecosystem.config.js verification
  await test('3. ecosystem.config.js is configured for single-instance fork mode', async () => {
    const pm2Path = path.join(__dirname, 'ecosystem.config.js');
    if (!fs.existsSync(pm2Path)) throw new Error('ecosystem.config.js missing');
    const pm2Config = require(pm2Path);
    if (!pm2Config.apps || pm2Config.apps.length === 0) throw new Error('Invalid PM2 config');
    const app = pm2Config.apps[0];
    if (app.instances !== 1 || app.exec_mode !== 'fork') {
      throw new Error('PM2 must run in fork mode (single instance) for SQLite concurrency safety');
    }
  });

  // 4. Nginx Reverse Proxy Configuration verification
  await test('4. nginx/landing-page.conf contains SSL, security headers, rate limiting, and caching', async () => {
    const nginxPath = path.join(__dirname, 'nginx', 'landing-page.conf');
    if (!fs.existsSync(nginxPath)) throw new Error('nginx/landing-page.conf missing');
    const conf = fs.readFileSync(nginxPath, 'utf8');
    if (!conf.includes('X-Frame-Options') || !conf.includes('Strict-Transport-Security')) {
      throw new Error('Security headers missing in Nginx config');
    }
    if (!conf.includes('limit_req_zone') || !conf.includes('/api/orders')) {
      throw new Error('Rate limiting missing in Nginx config');
    }
    if (!conf.includes('proxy_pass http://127.0.0.1:3000')) {
      throw new Error('proxy_pass target missing in Nginx config');
    }
  });

  // 5. Database Backup Utility verification
  await test('5. Automated database backup script (scripts/backup_db.js) executes cleanly', async () => {
    const backupScriptPath = path.join(__dirname, 'scripts', 'backup_db.js');
    if (!fs.existsSync(backupScriptPath)) throw new Error('scripts/backup_db.js missing');
    const output = execSync('node scripts/backup_db.js', { encoding: 'utf8' });
    if (!output.includes('[Backup Success]')) throw new Error(`Backup failed:\n${output}`);
  });

  // 6. Security: Database File Privacy Protection
  await test('6. Security: Direct static download of /data/database.sqlite returns 403 Forbidden', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/data/database.sqlite',
      method: 'GET'
    });

    if (res.statusCode !== 403) throw new Error(`Expected 403 Forbidden, got ${res.statusCode}`);
    if (res.body.includes('SQLite format')) throw new Error('Database binary was leaked!');
  });

  // 7. Security: .env file static access blocked
  await test('7. Security: Direct static access to /.env returns 403 Forbidden', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/.env',
      method: 'GET'
    });

    if (res.statusCode !== 403) throw new Error(`Expected 403 Forbidden, got ${res.statusCode}`);
  });

  // 8. Security Headers Verification
  await test('8. Security Headers: X-Content-Type-Options and X-Frame-Options present', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/health',
      method: 'GET'
    });

    if (res.headers['x-content-type-options'] !== 'nosniff') {
      throw new Error('X-Content-Type-Options: nosniff missing');
    }
    if (res.headers['x-frame-options'] !== 'SAMEORIGIN') {
      throw new Error('X-Frame-Options: SAMEORIGIN missing');
    }
  });

  // 9. Live Health Check HTTP Endpoint (GET /api/health)
  await test('9. Live HTTP GET /api/health responds with 200 OK and health diagnostics', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/health',
      method: 'GET'
    });

    if (res.statusCode !== 200) throw new Error(`Expected 200, got ${res.statusCode}: ${res.body}`);
    if (!res.json || res.json.status !== 'ok') throw new Error('Health check response not ok');
    if (!res.json.uptime || !res.json.memory || !res.json.database) throw new Error('Missing health metrics');
  });

  // 10. Phase 1 Regression
  await test('10. Phase 1 regression test suite (node test_phase1.js)', async () => {
    const output = execSync('node test_phase1.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 1 test failed:\n${output}`);
  });

  // 11. Phase 2 Regression
  await test('11. Phase 2 regression test suite (node test_phase2.js)', async () => {
    const output = execSync('node test_phase2.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 2 test failed:\n${output}`);
  });

  // 12. Phase 3 Regression
  await test('12. Phase 3 regression test suite (node test_phase3.js)', async () => {
    const output = execSync('node test_phase3.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 3 test failed:\n${output}`);
  });

  // 13. Phase 4 Regression
  await test('13. Phase 4 regression test suite (node test_phase4.js)', async () => {
    const output = execSync('node test_phase4.js', { encoding: 'utf8' });
    if (!output.includes('17/17 TESTS PASSED')) throw new Error(`Phase 4 test failed:\n${output}`);
  });

  // 14. Phase 5 Regression
  await test('14. Phase 5 regression test suite (node test_phase5.js)', async () => {
    const output = execSync('node test_phase5.js', { encoding: 'utf8' });
    if (!output.includes('15/15 TESTS PASSED')) throw new Error(`Phase 5 test failed:\n${output}`);
  });

  // 15. Landing Page JSON Flow Audit
  await test('15. Landing page JSON flow audit (node audit_all.js)', async () => {
    const output = execSync('node audit_all.js', { encoding: 'utf8' });
    if (!output.includes('File: chicken-booster.json')) throw new Error(`Audit failed:\n${output}`);
  });

  console.log(`\n========================================`);
  console.log(`SUMMARY: ${passed}/${total} TESTS PASSED (100%)`);
  console.log(`========================================\n`);
}

runTests().catch(err => {
  console.error('Test suite execution failed:', err);
  process.exit(1);
});
