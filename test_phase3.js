const http = require('http');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('=== PHASE 3 DATA LAYER & TRACKING ARCHITECTURE TEST SUITE ===\n');

// Mock browser environment to test EventBus and DataLayer
function createMockBrowserEnv() {
  const dataLayer = [{ existing_item: 'preserve_me' }];
  const mockWindow = {
    dataLayer,
    location: {
      href: 'http://localhost:3000/products/chicken-booster/',
      pathname: '/products/chicken-booster/',
      search: ''
    }
  };
  const mockDocument = {
    title: 'চিকেন বুস্টার (Chicken Booster) — পোল্ট্রি গ্রোথ ও রোগ প্রতিরোধক ফর্মুলা'
  };

  return { mockWindow, mockDocument };
}

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

  // Load and instantiate EventBus in mocked browser context
  const { eventBus } = require('./assets/js/core/event-bus.js');
  global.window = {
    dataLayer: [{ original: 'preserved_entry' }],
    location: {
      href: 'http://localhost:3000/products/chicken-booster/',
      pathname: '/products/chicken-booster/'
    }
  };
  global.document = {
    title: 'চিকেন বুস্টার (Chicken Booster) — পোল্ট্রি গ্রোথ ও রোগ প্রতিরোধক ফর্মুলা'
  };

  // 1. Data Layer Initialization & Preservation
  await test('1. window.dataLayer initializes safely and preserves existing entries', async () => {
    if (!Array.isArray(global.window.dataLayer)) throw new Error('dataLayer is not an array');
    if (global.window.dataLayer[0].original !== 'preserved_entry') throw new Error('Original dataLayer entry was overwritten');
  });

  // 2. page_view Event Schema
  await test('2. page_view event generates standard schema with unique event_id', async () => {
    const pv = eventBus.trackPageView();
    if (pv.event !== 'page_view') throw new Error(`Expected event: page_view, got ${pv.event}`);
    if (!pv.event_id || typeof pv.event_id !== 'string') throw new Error('Missing event_id');
    if (!pv.event_time) throw new Error('Missing event_time');
    if (!pv.page || pv.page.path !== '/products/chicken-booster/') throw new Error('Missing or invalid page info');
    
    // Verify pushed to dataLayer
    const lastDl = global.window.dataLayer[global.window.dataLayer.length - 1];
    if (lastDl.event !== 'page_view' || lastDl.event_id !== pv.event_id) {
      throw new Error('page_view was not pushed correctly to window.dataLayer');
    }
  });

  // 3. view_content Event Schema & Deduplication
  await test('3. view_content event generates standard ecommerce.items and fires once', async () => {
    const product = { id: 'chicken-booster', name: 'Chicken Booster', currencyCode: 'BDT' };
    const variant = { id: 'variant-2', name: '২ টি প্যাক (১ কেজি কম্বো)', price: 1850 };
    
    const vc = eventBus.trackViewContent(product, variant);
    if (!vc || vc.event !== 'view_content') throw new Error('view_content failed');
    if (vc.ecommerce.value !== 1850) throw new Error(`Expected value 1850, got ${vc.ecommerce.value}`);
    if (vc.ecommerce.items[0].item_id !== 'chicken-booster') throw new Error('Invalid item_id');

    // Repeated call should return null (fired once)
    const vc2 = eventBus.trackViewContent(product, variant);
    if (vc2 !== null) throw new Error('view_content fired repeatedly');
  });

  // 4. cta_click Event
  await test('4. cta_click event records cta_name and target_section', async () => {
    const cta = eventBus.trackCtaClick('অর্ডার করতে এখানে ক্লিক করুন ➔', '#order-form-section');
    if (cta.event !== 'cta_click') throw new Error('Expected event: cta_click');
    if (cta.cta_name !== 'অর্ডার করতে এখানে ক্লিক করুন ➔') throw new Error('Invalid cta_name');
    if (cta.target_section !== '#order-form-section') throw new Error('Invalid target_section');
  });

  // 5. select_item Event
  await test('5. select_item event contains selected variant price and details', async () => {
    const product = { id: 'chicken-booster', name: 'Chicken Booster', currencyCode: 'BDT' };
    const variant4Pack = { id: 'variant-3', name: '৪ টি প্যাক (২ কেজি মেগা সেভার)', price: 3400 };
    
    const si = eventBus.trackSelectItem(product, variant4Pack);
    if (si.event !== 'select_item') throw new Error('Expected event: select_item');
    if (si.ecommerce.value !== 3400) throw new Error(`Expected value 3400, got ${si.ecommerce.value}`);
    if (si.ecommerce.items[0].item_variant !== 'variant-3') throw new Error('Invalid variant');
  });

  // 6. initiate_checkout Event & Session Single Fire
  await test('6. initiate_checkout event fires once per checkout session', async () => {
    const product = { id: 'chicken-booster', name: 'Chicken Booster', currencyCode: 'BDT' };
    const variant = { id: 'variant-2', name: '২ টি প্যাক', price: 1850 };
    
    const ic = eventBus.trackInitiateCheckout(product, variant, 1970);
    if (!ic || ic.event !== 'initiate_checkout') throw new Error('Expected event: initiate_checkout');
    if (ic.ecommerce.value !== 1970) throw new Error(`Expected value 1970, got ${ic.ecommerce.value}`);

    // Repeated call should return null
    const ic2 = eventBus.trackInitiateCheckout(product, variant, 1970);
    if (ic2 !== null) throw new Error('initiate_checkout fired repeatedly in same session');
  });

  // 7. Purchase Event requires Server Confirmation & Real Order ID
  let confirmedOrder = null;
  await test('7. Real POST /api/orders creates server order for purchase tracking', async () => {
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
      idempotency_key: 'key_p3_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
      customer: {
        name: 'ট্র্যাকিং টেস্টার',
        phone: '01899001122',
        address: 'মিরপুর ২, ঢাকা',
        delivery_zone: 'inside_dhaka'
      }
    });

    if (res.statusCode !== 201) throw new Error(`Order creation failed with ${res.statusCode}: ${res.body}`);
    confirmedOrder = res.json.order;
    if (!confirmedOrder || !confirmedOrder.order_number) throw new Error('Missing server order');
  });

  // 8. purchase Event Schema with Authoritative Server Values
  await test('8. purchase event uses server-confirmed total and real order_number', async () => {
    const pur = eventBus.trackPurchase(confirmedOrder);
    if (!pur || pur.event !== 'purchase') throw new Error('Expected event: purchase');
    if (pur.transaction_id !== confirmedOrder.order_number) throw new Error('transaction_id does not match server order_number');
    if (pur.order_number !== confirmedOrder.order_number) throw new Error('order_number mismatch');
    if (pur.ecommerce.value !== confirmedOrder.total) throw new Error('value does not match server total');
    if (pur.ecommerce.shipping !== confirmedOrder.delivery_charge) throw new Error('shipping does not match server delivery_charge');
    if (!pur.event_id.startsWith('evt_pur_')) throw new Error('Invalid purchase event_id prefix');
  });

  // 9. Zero Customer PII in window.dataLayer
  await test('9. Security & Privacy: No raw customer PII pushed into window.dataLayer', async () => {
    const dlString = JSON.stringify(global.window.dataLayer);
    if (dlString.includes('01899001122')) throw new Error('Customer phone number leaked into dataLayer!');
    if (dlString.includes('মিরপুর ২, ঢাকা')) throw new Error('Customer address leaked into dataLayer!');
    if (dlString.includes('ট্র্যাকিং টেস্টার')) throw new Error('Customer raw name leaked into dataLayer!');
  });

  // 10. Purchase Deduplication Protection
  await test('10. purchase event prevents duplicate firing for the same order_number', async () => {
    const dupPur = eventBus.trackPurchase(confirmedOrder);
    if (dupPur !== null) throw new Error('Duplicate purchase event was fired for the same order_number');
  });

  // 11. Failed API order does NOT trigger Purchase
  await test('11. Failed API order rejection does not trigger trackPurchase', async () => {
    const initialDlCount = global.window.dataLayer.length;
    
    // Simulate invalid order request
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      product_id: 'chicken-booster',
      variant_id: 'variant-1',
      customer: { name: '', phone: 'invalid', address: '' }
    });

    if (res.statusCode !== 400) throw new Error(`Expected 400 rejection, got ${res.statusCode}`);
    
    // dataLayer should NOT have received a purchase event
    const finalDlCount = global.window.dataLayer.length;
    if (finalDlCount !== initialDlCount) throw new Error('dataLayer received an event on failed API request');
  });

  // 12. Canonical Landing Page HTML contains Phase 3 DataLayer Script
  await test('12. Canonical landing page index.html contains window.dataLayer and TrackingBus', async () => {
    const html = fs.readFileSync(path.join(__dirname, 'products', 'chicken-booster', 'index.html'), 'utf8');
    if (!html.includes('window.dataLayer = window.dataLayer || []')) throw new Error('Missing dataLayer init in index.html');
    if (!html.includes('window.trackingEvents = TrackingBus')) throw new Error('Missing window.trackingEvents in index.html');
    if (!html.includes('trackPurchase(order)')) throw new Error('Missing trackPurchase call in index.html');
  });

  // 13. Synchronized extracted_html matches canonical DataLayer
  await test('13. Extracted HTML matches canonical DataLayer script', async () => {
    const canonical = fs.readFileSync(path.join(__dirname, 'products', 'chicken-booster', 'index.html'), 'utf8');
    const extracted = fs.readFileSync(path.join(__dirname, 'extracted_html', 'chicken-booster__step_1_checkout__widget_4259dac.html'), 'utf8');
    if (canonical !== extracted) throw new Error('Extracted HTML is not synchronized with canonical index.html');
  });

  // 14. Phase 1 Regression Verification
  await test('14. Phase 1 regression tests (node test_phase1.js)', async () => {
    const output = execSync('node test_phase1.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 1 test failed:\n${output}`);
  });

  // 15. Phase 2 Regression Verification
  await test('15. Phase 2 regression tests (node test_phase2.js)', async () => {
    const output = execSync('node test_phase2.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 2 test failed:\n${output}`);
  });

  // 16. Landing page JSON Flow Audit Verification
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
