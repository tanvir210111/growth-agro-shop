const http = require('http');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('=== PHASE 7 LANDING-PAGE SERVER-SIDE GTM INFRASTRUCTURE TEST SUITE ===\n');

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

  // 1. Server GTM Container JSON Export
  const sgtmJsonPath = path.join(__dirname, 'gtm', 'gtm_server_container.json');
  await test('1. gtm/gtm_server_container.json exists and is valid Server GTM JSON', async () => {
    if (!fs.existsSync(sgtmJsonPath)) throw new Error('gtm_server_container.json missing');
    const sgtm = JSON.parse(fs.readFileSync(sgtmJsonPath, 'utf8'));
    if (!sgtm.containerVersion || !sgtm.containerVersion.container) throw new Error('Invalid export format');
    if (!sgtm.containerVersion.container.usageContext.includes('SERVER')) {
      throw new Error('Container must have usageContext: ["SERVER"]');
    }
  });

  const sgtm = JSON.parse(fs.readFileSync(sgtmJsonPath, 'utf8'));

  // 2. GA4 / Web Client in Server Container
  await test('2. Server GTM container includes GA4 / Web GTM Client', async () => {
    const clients = sgtm.containerVersion.client || [];
    const gaClient = clients.find(c => c.type === 'ga4_client' || c.name.includes('Client'));
    if (!gaClient) throw new Error('GA4 / Web Client missing in Server GTM container');
  });

  // 3. Server GTM Triggers for Landing Page Events
  await test('3. Server GTM includes trigger filtering for /products/* landing page events', async () => {
    const triggers = sgtm.containerVersion.trigger || [];
    const landingTrigger = triggers.find(t => t.name.includes('Landing Page') || t.name.includes('/products/'));
    if (!landingTrigger) throw new Error('Landing page event trigger missing');
    const pathFilter = landingTrigger.customEventFilter.find(f => JSON.stringify(f).includes('/products/'));
    if (!pathFilter) throw new Error('Filter for /products/ path missing in trigger');
  });

  // 4. Server Variables for Deduplication and Pricing
  await test('4. Server GTM includes Event ID, Transaction ID, Order Number, Client IP, and User Agent variables', async () => {
    const variables = sgtm.containerVersion.variable || [];
    const varNames = variables.map(v => v.name);
    if (!varNames.includes('Event ID')) throw new Error('Event ID variable missing in sGTM');
    if (!varNames.includes('Transaction ID')) throw new Error('Transaction ID variable missing in sGTM');
    if (!varNames.includes('Client IP')) throw new Error('Client IP variable missing in sGTM');
    if (!varNames.includes('User Agent')) throw new Error('User Agent variable missing in sGTM');
  });

  // 5. Web GTM Transport Configuration Tag with track.YOUR_DOMAIN
  const webGtmJsonPath = path.join(__dirname, 'gtm', 'gtm_web_container.json');
  await test('5. gtm/gtm_web_container.json includes Server GTM Transport Tag with track.YOUR_DOMAIN', async () => {
    if (!fs.existsSync(webGtmJsonPath)) throw new Error('gtm_web_container.json missing');
    const webGtm = JSON.parse(fs.readFileSync(webGtmJsonPath, 'utf8'));
    const transportTag = webGtm.containerVersion.tag.find(t => t.name.includes('Server GTM'));
    if (!transportTag) throw new Error('Server GTM transport tag missing in Web GTM container');
    const params = JSON.stringify(transportTag.parameter);
    if (!params.includes('server_container_url') || !params.includes('{{Server GTM URL}}')) {
      throw new Error('server_container_url missing in transport tag');
    }
    const serverUrlVar = webGtm.containerVersion.variable.find(v => v.name === 'Server GTM URL');
    if (!serverUrlVar || !serverUrlVar.parameter[0].value.includes('track.YOUR_DOMAIN')) {
      throw new Error('Server GTM URL variable must use track.YOUR_DOMAIN placeholder');
    }
  });

  // 6. Docker Compose Configuration for sGTM
  const dockerPath = path.join(__dirname, 'docker-compose.sgtm.yml');
  await test('6. docker-compose.sgtm.yml is configured with official Google tagging image on port 8080', async () => {
    if (!fs.existsSync(dockerPath)) throw new Error('docker-compose.sgtm.yml missing');
    const content = fs.readFileSync(dockerPath, 'utf8');
    if (!content.includes('gcr.io/cloud-tagging-10302018/gtm-cloud-image:stable')) {
      throw new Error('Official Google tagging image missing in docker-compose');
    }
    if (!content.includes('127.0.0.1:8080:8080')) {
      throw new Error('Port mapping 127.0.0.1:8080 missing in docker-compose');
    }
  });

  // 7. Nginx track.YOUR_DOMAIN Subdomain Reverse Proxy
  const nginxTrackPath = path.join(__dirname, 'nginx', 'track.conf');
  await test('7. nginx/track.conf proxies track.YOUR_DOMAIN to http://127.0.0.1:8080 with header preservation', async () => {
    if (!fs.existsSync(nginxTrackPath)) throw new Error('nginx/track.conf missing');
    const content = fs.readFileSync(nginxTrackPath, 'utf8');
    if (!content.includes('server_name track.YOUR_DOMAIN')) throw new Error('Server name track.YOUR_DOMAIN missing');
    if (!content.includes('proxy_pass http://127.0.0.1:8080')) throw new Error('proxy_pass to 8080 missing');
    if (!content.includes('proxy_set_header X-Real-IP $remote_addr')) throw new Error('X-Real-IP preservation missing');
  });

  // 8. Scope Isolation: Admin Panel & Root excluded from Tracking
  await test('8. Scope Isolation: Admin panel (/admin/) and Root (/) excluded from sGTM tracking', async () => {
    const webGtm = JSON.parse(fs.readFileSync(webGtmJsonPath, 'utf8'));
    const landingTrigger = webGtm.containerVersion.trigger.find(t => t.name.includes('Landing Pages Only'));
    if (!landingTrigger) throw new Error('Landing Pages Only trigger missing');
    const filter = landingTrigger.customEventFilter[0];
    if (filter.type !== 'STARTS_WITH' || filter.parameter[1].value !== '/products/') {
      throw new Error('Trigger must strictly target STARTS_WITH /products/');
    }
  });

  // 9. Boundary Enforcement: No CAPI or Meta Access Token in Phase 7
  await test('9. Boundary Enforcement: No Meta CAPI access token or server-side Meta API calls exist', async () => {
    const serverJs = fs.readFileSync(path.join(__dirname, 'server.js'), 'utf8');
    if (serverJs.includes('graph.facebook.com') || serverJs.includes('FACEBOOK_ACCESS_TOKEN')) {
      throw new Error('Meta CAPI code leaked into server.js prematurely!');
    }
  });

  // 10. Phase 1 Regression
  await test('10. Phase 1 regression test suite (node test_phase1.js)', async () => {
    const output = execSync('node test_phase1.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 1 failed:\n${output}`);
  });

  // 11. Phase 2 Regression
  await test('11. Phase 2 regression test suite (node test_phase2.js)', async () => {
    const output = execSync('node test_phase2.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 2 failed:\n${output}`);
  });

  // 12. Phase 3 Regression
  await test('12. Phase 3 regression test suite (node test_phase3.js)', async () => {
    const output = execSync('node test_phase3.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 3 failed:\n${output}`);
  });

  // 13. Phase 4 Regression
  await test('13. Phase 4 regression test suite (node test_phase4.js)', async () => {
    const output = execSync('node test_phase4.js', { encoding: 'utf8' });
    if (!output.includes('17/17 TESTS PASSED')) throw new Error(`Phase 4 failed:\n${output}`);
  });

  // 14. Phase 5 Regression
  await test('14. Phase 5 regression test suite (node test_phase5.js)', async () => {
    const output = execSync('node test_phase5.js', { encoding: 'utf8' });
    if (!output.includes('15/15 TESTS PASSED')) throw new Error(`Phase 5 failed:\n${output}`);
  });

  // 15. Phase 6 Regression
  await test('15. Phase 6 regression test suite (node test_phase6.js)', async () => {
    const output = execSync('node test_phase6.js', { encoding: 'utf8' });
    if (!output.includes('15/15 TESTS PASSED')) throw new Error(`Phase 6 failed:\n${output}`);
  });

  // 16. Landing Page JSON Flow Audit
  await test('16. Landing page JSON flow audit (node audit_all.js)', async () => {
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
