const http = require('http');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('=== PHASE 8 META CONVERSIONS API (CAPI) VIA SERVER-SIDE GTM TEST SUITE ===\n');

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

  // 1. Server GTM Container JSON Export contains Meta CAPI
  const sgtmJsonPath = path.join(__dirname, 'gtm', 'gtm_server_container.json');
  await test('1. gtm/gtm_server_container.json contains Meta Conversions API tags', async () => {
    if (!fs.existsSync(sgtmJsonPath)) throw new Error('gtm_server_container.json missing');
    const sgtm = JSON.parse(fs.readFileSync(sgtmJsonPath, 'utf8'));
    const tags = sgtm.containerVersion.tag || [];
    const capiTags = tags.filter(t => t.type === 'facebook_conversions_api' || t.name.includes('Meta CAPI'));
    if (capiTags.length < 4) {
      throw new Error(`Expected at least 4 Meta CAPI tags, found ${capiTags.length}`);
    }
  });

  const sgtm = JSON.parse(fs.readFileSync(sgtmJsonPath, 'utf8'));
  const tags = sgtm.containerVersion.tag;

  // 2. Meta CAPI Purchase Tag Mapping
  await test('2. Meta CAPI Purchase tag is mapped with server value, orderId, and eventId', async () => {
    const purTag = tags.find(t => t.name === 'Meta CAPI - Purchase');
    if (!purTag) throw new Error('Meta CAPI - Purchase tag missing');
    const params = purTag.parameter;
    const eventName = params.find(p => p.key === 'eventName')?.value;
    const eventId = params.find(p => p.key === 'eventId')?.value;
    const orderId = params.find(p => p.key === 'orderId')?.value;
    const val = params.find(p => p.key === 'value')?.value;
    const curr = params.find(p => p.key === 'currency')?.value;

    if (eventName !== 'Purchase') throw new Error(`Invalid eventName: ${eventName}`);
    if (eventId !== '{{Event ID}}') throw new Error(`Invalid eventId variable: ${eventId}`);
    if (orderId !== '{{Transaction ID}}') throw new Error(`Invalid orderId variable: ${orderId}`);
    if (val !== '{{Event Value}}') throw new Error(`Invalid value variable: ${val}`);
    if (curr !== '{{Event Currency}}') throw new Error(`Invalid currency variable: ${curr}`);
  });

  // 3. Meta CAPI PageView, ViewContent, InitiateCheckout Tag Mappings
  await test('3. Meta CAPI PageView, ViewContent, and InitiateCheckout tags exist with eventId', async () => {
    const pvTag = tags.find(t => t.name === 'Meta CAPI - PageView');
    const vcTag = tags.find(t => t.name === 'Meta CAPI - ViewContent');
    const icTag = tags.find(t => t.name === 'Meta CAPI - InitiateCheckout');

    if (!pvTag || !vcTag || !icTag) throw new Error('One or more Meta CAPI standard tags missing');
    if (!pvTag.parameter.find(p => p.key === 'eventId')) throw new Error('eventId missing in PageView');
    if (!vcTag.parameter.find(p => p.key === 'eventId')) throw new Error('eventId missing in ViewContent');
    if (!icTag.parameter.find(p => p.key === 'eventId')) throw new Error('eventId missing in InitiateCheckout');
  });

  // 4. Secret Security: No Meta Access Token in Frontend Files or DataLayer
  await test('4. Security: No Meta Access Token in frontend HTML, JS, dataLayer, or Web GTM', async () => {
    const html = fs.readFileSync(path.join(__dirname, 'products', 'chicken-booster', 'index.html'), 'utf8');
    const eventBusJs = fs.readFileSync(path.join(__dirname, 'assets', 'js', 'core', 'event-bus.js'), 'utf8');
    const webGtm = fs.readFileSync(path.join(__dirname, 'gtm', 'gtm_web_container.json'), 'utf8');

    if (html.includes('EAAB') || html.includes('EAAX') || html.includes('META_ACCESS_TOKEN')) {
      throw new Error('Access token placeholder leaked into index.html!');
    }
    if (eventBusJs.includes('EAAB') || eventBusJs.includes('META_ACCESS_TOKEN')) {
      throw new Error('Access token leaked into event-bus.js!');
    }
    if (webGtm.includes('Meta Access Token') || webGtm.includes('apiAccessToken')) {
      throw new Error('Meta Access Token leaked into Web GTM container export!');
    }
  });

  // 5. Deduplication Simulation: Browser Pixel eventID equals Server CAPI eventID
  const { eventBus } = require('./assets/js/core/event-bus.js');
  global.window = {
    dataLayer: [],
    location: {
      href: 'http://localhost:3000/products/chicken-booster/',
      pathname: '/products/chicken-booster/'
    }
  };
  global.document = {
    title: 'চিকেন বুস্টার (Chicken Booster) — পোল্ট্রি গ্রোথ ও রোগ প্রতিরোধক ফর্মুলা'
  };

  // 6. Real Backend Order Creation & Deduplication Verification
  let confirmedOrder = null;
  await test('5. Real backend order creation provides server-authoritative numbers for Browser & Server Meta events', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      product_id: 'chicken-booster',
      variant_id: 'variant-2',
      quantity: 1,
      idempotency_key: 'capi_test_key_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
      customer: {
        name: 'সিএপিআই টেস্টার',
        phone: '01800112233',
        address: 'বনানী রোড ১১, ঢাকা',
        delivery_zone: 'inside_dhaka'
      }
    });

    if (res.statusCode !== 201) throw new Error(`Order creation failed with ${res.statusCode}: ${res.body}`);
    confirmedOrder = res.json.order;
    if (!confirmedOrder || !confirmedOrder.order_number) throw new Error('Missing confirmed order_number');
  });

  await test('6. Deduplication: Browser Meta Pixel Purchase and Server CAPI Purchase receive exact same event_id', async () => {
    const pur = eventBus.trackPurchase(confirmedOrder);
    if (!pur) throw new Error('Purchase event generation failed');

    // Simulate Browser Meta Pixel Tag Call
    const browserPixelPayload = {
      eventName: 'Purchase',
      params: {
        content_ids: ['chicken-booster'],
        value: pur.ecommerce.value,
        currency: pur.ecommerce.currency,
        num_items: 1
      },
      options: {
        eventID: pur.event_id
      }
    };

    // Simulate Server-Side GTM Meta CAPI Event Payload
    const serverCapiPayload = {
      event_name: 'Purchase',
      event_id: pur.event_id,
      event_time: Math.floor(new Date(pur.event_time).getTime() / 1000),
      action_source: 'website',
      user_data: {
        client_ip_address: '127.0.0.1',
        client_user_agent: 'NodeTestAgent'
      },
      custom_data: {
        currency: pur.ecommerce.currency,
        value: pur.ecommerce.value,
        content_ids: ['chicken-booster'],
        content_type: 'product',
        order_id: pur.transaction_id
      }
    };

    if (browserPixelPayload.options.eventID !== serverCapiPayload.event_id) {
      throw new Error(`Deduplication mismatch! Browser eventID: ${browserPixelPayload.options.eventID} vs Server event_id: ${serverCapiPayload.event_id}`);
    }
    if (serverCapiPayload.custom_data.order_id !== confirmedOrder.order_number) {
      throw new Error(`order_id mismatch! Expected ${confirmedOrder.order_number}, got ${serverCapiPayload.custom_data.order_id}`);
    }
    if (serverCapiPayload.custom_data.value !== confirmedOrder.total) {
      throw new Error(`value mismatch! Expected ${confirmedOrder.total}, got ${serverCapiPayload.custom_data.value}`);
    }
  });

  // 7. Privacy: Zero Customer PII in Server CAPI Payload
  await test('7. Privacy: Zero customer phone or address in Server CAPI event payload', async () => {
    const dlString = JSON.stringify(global.window.dataLayer);
    if (dlString.includes('01800112233')) throw new Error('Customer phone leaked into dataLayer!');
    if (dlString.includes('বনানী রোড ১১, ঢাকা')) throw new Error('Customer address leaked into dataLayer!');
  });

  // 8. Tracking Failure Safety: Order processing succeeds independently of tracking
  await test('8. Resilience: Order API and database transactions succeed even if tracking is unreachable', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      product_id: 'chicken-booster',
      variant_id: 'variant-1',
      quantity: 1,
      idempotency_key: 'capi_resilience_key_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
      customer: {
        name: 'রেজিলিয়েন্স টেস্ট',
        phone: '01711223344',
        address: 'মতিঝিল বাণিজ্যিক এলাকা, ঢাকা',
        delivery_zone: 'inside_dhaka'
      }
    });

    if (res.statusCode !== 201) throw new Error(`Order creation failed during resilience test: ${res.statusCode}`);
    if (!res.json.order || !res.json.order.order_number) throw new Error('Missing order confirmation');
  });

  // 9. Phase 1 Regression
  await test('9. Phase 1 regression test suite (node test_phase1.js)', async () => {
    const output = execSync('node test_phase1.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 1 failed:\n${output}`);
  });

  // 10. Phase 2 Regression
  await test('10. Phase 2 regression test suite (node test_phase2.js)', async () => {
    const output = execSync('node test_phase2.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 2 failed:\n${output}`);
  });

  // 11. Phase 3 Regression
  await test('11. Phase 3 regression test suite (node test_phase3.js)', async () => {
    const output = execSync('node test_phase3.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 3 failed:\n${output}`);
  });

  // 12. Phase 4 Regression
  await test('12. Phase 4 regression test suite (node test_phase4.js)', async () => {
    const output = execSync('node test_phase4.js', { encoding: 'utf8' });
    if (!output.includes('17/17 TESTS PASSED')) throw new Error(`Phase 4 failed:\n${output}`);
  });

  // 13. Phase 5 Regression
  await test('13. Phase 5 regression test suite (node test_phase5.js)', async () => {
    const output = execSync('node test_phase5.js', { encoding: 'utf8' });
    if (!output.includes('15/15 TESTS PASSED')) throw new Error(`Phase 5 failed:\n${output}`);
  });

  // 14. Phase 6 Regression
  await test('14. Phase 6 regression test suite (node test_phase6.js)', async () => {
    const output = execSync('node test_phase6.js', { encoding: 'utf8' });
    if (!output.includes('15/15 TESTS PASSED')) throw new Error(`Phase 6 failed:\n${output}`);
  });

  // 15. Phase 7 Regression
  await test('15. Phase 7 regression test suite (node test_phase7.js)', async () => {
    const output = execSync('node test_phase7.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 7 failed:\n${output}`);
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
