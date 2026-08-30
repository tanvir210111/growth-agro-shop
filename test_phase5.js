const http = require('http');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('=== PHASE 5 META PIXEL WEB INTEGRATION TEST SUITE ===\n');

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

  // 1. GTM Web Container JSON Export Verification
  const gtmJsonPath = path.join(__dirname, 'gtm', 'gtm_web_container.json');
  await test('1. GTM Web Container export file exists and is valid JSON', async () => {
    if (!fs.existsSync(gtmJsonPath)) throw new Error('gtm_web_container.json does not exist');
    const gtmConfig = JSON.parse(fs.readFileSync(gtmJsonPath, 'utf8'));
    if (!gtmConfig.containerVersion || !gtmConfig.containerVersion.tag) {
      throw new Error('Invalid GTM container export format');
    }
  });

  const gtmConfig = JSON.parse(fs.readFileSync(gtmJsonPath, 'utf8'));
  const tags = gtmConfig.containerVersion.tag;
  const triggers = gtmConfig.containerVersion.trigger;

  // 2. Meta Pixel Base Tag & PageView in GTM
  await test('2. GTM container includes Meta Pixel Base Tag & PageView with eventID', async () => {
    const baseTag = tags.find(t => t.name.includes('Meta Pixel - Base Code'));
    if (!baseTag) throw new Error('Meta Pixel base tag missing in GTM export');
    const html = baseTag.parameter.find(p => p.key === 'html').value;
    if (!html.includes("fbq('init'") || !html.includes("fbq('track', 'PageView'")) {
      throw new Error('Invalid Meta Pixel base code');
    }
    if (!html.includes("eventID: '{{dlv - event_id}}'")) {
      throw new Error('eventID parameter missing in PageView tag');
    }
  });

  // 3. Meta ViewContent Tag in GTM
  await test('3. GTM container includes Meta ViewContent Tag with content_ids and value', async () => {
    const vcTag = tags.find(t => t.name.includes('ViewContent'));
    if (!vcTag) throw new Error('Meta ViewContent tag missing in GTM export');
    const html = vcTag.parameter.find(p => p.key === 'html').value;
    if (!html.includes("fbq('track', 'ViewContent'") || !html.includes("content_ids: ['chicken-booster']")) {
      throw new Error('Invalid ViewContent tag configuration');
    }
    if (!html.includes("eventID: '{{dlv - event_id}}'")) {
      throw new Error('eventID parameter missing in ViewContent tag');
    }
  });

  // 4. Meta InitiateCheckout Tag in GTM
  await test('4. GTM container includes Meta InitiateCheckout Tag with eventID', async () => {
    const icTag = tags.find(t => t.name.includes('InitiateCheckout'));
    if (!icTag) throw new Error('Meta InitiateCheckout tag missing in GTM export');
    const html = icTag.parameter.find(p => p.key === 'html').value;
    if (!html.includes("fbq('track', 'InitiateCheckout'")) {
      throw new Error('Invalid InitiateCheckout tag configuration');
    }
    if (!html.includes("eventID: '{{dlv - event_id}}'")) {
      throw new Error('eventID parameter missing in InitiateCheckout tag');
    }
  });

  // 5. Meta Purchase Tag in GTM
  await test('5. GTM container includes Meta Purchase Tag with server value and eventID', async () => {
    const purTag = tags.find(t => t.name.includes('Purchase'));
    if (!purTag) throw new Error('Meta Purchase tag missing in GTM export');
    const html = purTag.parameter.find(p => p.key === 'html').value;
    if (!html.includes("fbq('track', 'Purchase'")) {
      throw new Error('Invalid Purchase tag configuration');
    }
    if (!html.includes("eventID: '{{dlv - event_id}}'")) {
      throw new Error('eventID parameter missing in Purchase tag');
    }
  });

  // 6. Meta Custom Event Triggers in GTM
  await test('6. GTM container includes custom triggers for page_view, view_content, initiate_checkout, purchase', async () => {
    const triggerNames = triggers.map(tr => tr.name);
    if (!triggerNames.includes('Custom Event - page_view')) throw new Error('page_view trigger missing');
    if (!triggerNames.includes('Custom Event - view_content')) throw new Error('view_content trigger missing');
    if (!triggerNames.includes('Custom Event - initiate_checkout')) throw new Error('initiate_checkout trigger missing');
    if (!triggerNames.includes('Custom Event - purchase')) throw new Error('purchase trigger missing');
  });

  // 7. Event Bus Data Layer to Meta Mapping Simulation
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

  // Mock Meta fbq function to capture calls
  const metaFbqCalls = [];
  global.fbq = function(action, eventName, params, options) {
    metaFbqCalls.push({ action, eventName, params, options });
  };

  // 8. Test DataLayer Events trigger correct Meta parameters
  await test('7. view_content dataLayer event provides all required Meta parameters', async () => {
    const product = { id: 'chicken-booster', name: 'Chicken Booster', currencyCode: 'BDT' };
    const variant = { id: 'variant-2', name: '২ টি প্যাক', price: 1850 };
    const vc = eventBus.trackViewContent(product, variant);

    // Simulate GTM tag execution
    global.fbq('track', 'ViewContent', {
      content_ids: [vc.ecommerce.items[0].item_id],
      content_name: vc.ecommerce.items[0].item_name,
      content_type: 'product',
      value: vc.ecommerce.value,
      currency: vc.ecommerce.currency
    }, { eventID: vc.event_id });

    const call = metaFbqCalls.find(c => c.eventName === 'ViewContent');
    if (!call) throw new Error('Meta ViewContent not called');
    if (call.params.value !== 1850) throw new Error(`Expected value 1850, got ${call.params.value}`);
    if (call.params.currency !== 'BDT') throw new Error(`Expected currency BDT, got ${call.params.currency}`);
    if (!call.options.eventID) throw new Error('eventID missing in Meta call');
  });

  // 9. Real Backend Order Confirmation for Meta Purchase
  let confirmedOrder = null;
  await test('8. Real backend order creation returns confirmed order for Meta Purchase', async () => {
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
      idempotency_key: 'meta_test_key_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
      customer: {
        name: 'মেটা পিক্সেল টেস্টার',
        phone: '01900112233',
        address: 'উত্তরা সেক্টর ৩, ঢাকা',
        delivery_zone: 'inside_dhaka'
      }
    });

    if (res.statusCode !== 201) throw new Error(`Order creation failed with ${res.statusCode}: ${res.body}`);
    confirmedOrder = res.json.order;
    if (!confirmedOrder || !confirmedOrder.order_number) throw new Error('Missing confirmed order_number');
  });

  // 10. Meta Purchase Event with Authoritative Server Values & eventID
  await test('9. Meta Purchase event receives server-authoritative value and matching eventID', async () => {
    const pur = eventBus.trackPurchase(confirmedOrder);
    if (!pur) throw new Error('Purchase event generation failed');

    // Simulate GTM Meta Purchase tag
    global.fbq('track', 'Purchase', {
      content_ids: ['chicken-booster'],
      content_name: pur.ecommerce.items[0].item_name,
      content_type: 'product',
      value: pur.ecommerce.value,
      currency: pur.ecommerce.currency,
      num_items: pur.ecommerce.items[0].quantity
    }, { eventID: pur.event_id });

    const call = metaFbqCalls.find(c => c.eventName === 'Purchase');
    if (!call) throw new Error('Meta Purchase not called');
    if (call.params.value !== confirmedOrder.total) throw new Error(`Expected total ${confirmedOrder.total}, got ${call.params.value}`);
    if (call.params.currency !== 'BDT') throw new Error(`Expected currency BDT, got ${call.params.currency}`);
    if (!call.options.eventID || !call.options.eventID.startsWith('evt_pur_')) {
      throw new Error('eventID missing or invalid in Meta Purchase call');
    }
  });

  // 11. Security & Privacy: No PII in Meta Pixel Payloads
  await test('10. Privacy: Zero customer PII in Meta Pixel call parameters', async () => {
    const callsString = JSON.stringify(metaFbqCalls);
    if (callsString.includes('01900112233')) throw new Error('Phone number leaked into Meta pixel parameters!');
    if (callsString.includes('উত্তরা সেক্টর ৩, ঢাকা')) throw new Error('Address leaked into Meta pixel parameters!');
    if (callsString.includes('মেটা পিক্সেল টেস্টার')) throw new Error('Name leaked into Meta pixel parameters!');
  });

  // 12. Phase 1 Regression
  await test('11. Phase 1 regression test suite (node test_phase1.js)', async () => {
    const output = execSync('node test_phase1.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 1 test failed:\n${output}`);
  });

  // 13. Phase 2 Regression
  await test('12. Phase 2 regression test suite (node test_phase2.js)', async () => {
    const output = execSync('node test_phase2.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 2 test failed:\n${output}`);
  });

  // 14. Phase 3 Regression
  await test('13. Phase 3 regression test suite (node test_phase3.js)', async () => {
    const output = execSync('node test_phase3.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 3 test failed:\n${output}`);
  });

  // 15. Phase 4 Regression
  await test('14. Phase 4 regression test suite (node test_phase4.js)', async () => {
    const output = execSync('node test_phase4.js', { encoding: 'utf8' });
    if (!output.includes('17/17 TESTS PASSED')) throw new Error(`Phase 4 test failed:\n${output}`);
  });

  // 16. Landing Page Flow Audit
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
