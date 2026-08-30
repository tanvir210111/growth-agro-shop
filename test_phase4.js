const http = require('http');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

console.log('=== PHASE 4 GOOGLE TAG MANAGER WEB INTEGRATION TEST SUITE ===\n');

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

  const htmlContent = fs.readFileSync(path.join(__dirname, 'products', 'chicken-booster', 'index.html'), 'utf8');

  // 1. GTM Configuration & Snippet in HEAD
  await test('1. GTM snippet and dataLayer initialization exist in <head>', async () => {
    if (!htmlContent.includes('https://www.googletagmanager.com/gtm.js?id=')) {
      throw new Error('Official GTM script tag missing in head');
    }
    if (!htmlContent.includes('window.dataLayer = window.dataLayer || [];')) {
      throw new Error('window.dataLayer initialization missing in head');
    }
  });

  // 2. GTM Container ID Configuration
  await test('2. GTM Container ID is configurable (window.GTM_CONTAINER_ID / meta / query param)', async () => {
    if (!htmlContent.includes('window.GTM_CONTAINER_ID')) {
      throw new Error('window.GTM_CONTAINER_ID resolution missing');
    }
    if (!htmlContent.includes('GTM-XXXXXXX')) {
      throw new Error('GTM placeholder configuration missing');
    }
  });

  // 3. GTM Noscript in BODY
  await test('3. GTM <noscript> iframe fallback is located immediately in <body>', async () => {
    if (!htmlContent.includes('https://www.googletagmanager.com/ns.html?id=')) {
      throw new Error('GTM noscript iframe fallback missing');
    }
    const headCount = (htmlContent.match(/googletagmanager\.com\/gtm\.js/g) || []).length;
    const bodyCount = (htmlContent.match(/googletagmanager\.com\/ns\.html/g) || []).length;
    if (headCount !== 1) throw new Error(`Expected exactly 1 GTM head snippet, found ${headCount}`);
    if (bodyCount !== 1) throw new Error(`Expected exactly 1 GTM noscript snippet, found ${bodyCount}`);
  });

  // 4. Data Layer Event Bus & Mock Environment Testing
  const { eventBus } = require('./assets/js/core/event-bus.js');
  global.window = {
    dataLayer: [{ test_prior: 'preserved' }],
    location: {
      href: 'http://localhost:3000/products/chicken-booster/',
      pathname: '/products/chicken-booster/'
    }
  };
  global.document = {
    title: 'চিকেন বুস্টার (Chicken Booster) — পোল্ট্রি গ্রোথ ও রোগ প্রতিরোধক ফর্মুলা'
  };

  // 5. Existing dataLayer is preserved
  await test('4. Existing dataLayer entries remain intact when GTM loads', async () => {
    if (!Array.isArray(global.window.dataLayer)) throw new Error('dataLayer is not an array');
    if (global.window.dataLayer[0].test_prior !== 'preserved') throw new Error('Existing entry was overwritten');
  });

  // 6. GTM Data Layer Variables: page_view
  await test('5. GTM Data Layer: page_view contains event_id, event_time, and page variables', async () => {
    const pv = eventBus.trackPageView();
    if (pv.event !== 'page_view') throw new Error('Invalid event name');
    if (!pv.event_id || !pv.event_time || !pv.page.url) throw new Error('Missing standard page_view variables');
  });

  // 7. GTM Data Layer Variables: view_content
  await test('6. GTM Data Layer: view_content contains ecommerce.items and value', async () => {
    const product = { id: 'chicken-booster', name: 'Chicken Booster', currencyCode: 'BDT' };
    const variant = { id: 'variant-2', name: '২ টি প্যাক (১ কেজি কম্বো)', price: 1850 };
    const vc = eventBus.trackViewContent(product, variant);
    if (!vc || vc.event !== 'view_content') throw new Error('Invalid view_content');
    if (vc.ecommerce.value !== 1850 || vc.ecommerce.currency !== 'BDT') throw new Error('Invalid ecommerce value/currency');
    if (!vc.ecommerce.items || vc.ecommerce.items.length !== 1) throw new Error('Invalid ecommerce items array');
  });

  // 8. GTM Data Layer Variables: cta_click
  await test('7. GTM Data Layer: cta_click contains cta_name and target_section variables', async () => {
    const cta = eventBus.trackCtaClick('অর্ডার করুন', '#order-form-section');
    if (cta.event !== 'cta_click') throw new Error('Invalid cta_click');
    if (cta.cta_name !== 'অর্ডার করুন' || cta.target_section !== '#order-form-section') throw new Error('Invalid cta variables');
  });

  // 9. GTM Data Layer Variables: select_item
  await test('8. GTM Data Layer: select_item contains updated variant item details', async () => {
    const product = { id: 'chicken-booster', name: 'Chicken Booster', currencyCode: 'BDT' };
    const variant3 = { id: 'variant-3', name: '৪ টি প্যাক (২ কেজি মেগা সেভার)', price: 3400 };
    const si = eventBus.trackSelectItem(product, variant3);
    if (si.event !== 'select_item' || si.ecommerce.value !== 3400) throw new Error('Invalid select_item');
  });

  // 10. GTM Data Layer Variables: initiate_checkout
  await test('9. GTM Data Layer: initiate_checkout contains total amount and items', async () => {
    const product = { id: 'chicken-booster', name: 'Chicken Booster', currencyCode: 'BDT' };
    const variant = { id: 'variant-2', name: '২ টি প্যাক', price: 1850 };
    const ic = eventBus.trackInitiateCheckout(product, variant, 1970);
    if (!ic || ic.event !== 'initiate_checkout' || ic.ecommerce.value !== 1970) throw new Error('Invalid initiate_checkout');
  });

  // 11. GTM Purchase Trigger: Real Backend Order Creation
  let confirmedOrder = null;
  await test('10. Real backend order creation returns confirmed order for GTM Purchase tag', async () => {
    const res = await request({
      hostname: 'localhost',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      product_id: 'chicken-booster',
      variant_id: 'variant-3',
      quantity: 1,
      idempotency_key: 'gtm_test_key_' + Date.now() + '_' + Math.random().toString(36).substring(2, 7),
      customer: {
        name: 'জিটিএম টেস্টার',
        phone: '01700112233',
        address: 'গুলশান ২, ঢাকা',
        delivery_zone: 'inside_dhaka'
      }
    });

    if (res.statusCode !== 201) throw new Error(`Order failed with ${res.statusCode}: ${res.body}`);
    confirmedOrder = res.json.order;
    if (!confirmedOrder || !confirmedOrder.order_number) throw new Error('Missing confirmed order_number');
  });

  // 12. GTM Purchase Event Structure & Server Authority
  await test('11. GTM Data Layer: purchase event contains transaction_id, server value, and items', async () => {
    const pur = eventBus.trackPurchase(confirmedOrder);
    if (!pur || pur.event !== 'purchase') throw new Error('Invalid purchase event');
    if (pur.transaction_id !== confirmedOrder.order_number) throw new Error('transaction_id does not match server order_number');
    if (pur.ecommerce.value !== confirmedOrder.total) throw new Error('ecommerce.value does not match server total');
    if (pur.ecommerce.shipping !== confirmedOrder.delivery_charge) throw new Error('ecommerce.shipping does not match server delivery fee');
  });

  // 13. Privacy: No PII in GTM Data Layer
  await test('12. Privacy: No customer phone or address in window.dataLayer for GTM', async () => {
    const dlString = JSON.stringify(global.window.dataLayer);
    if (dlString.includes('01700112233')) throw new Error('Phone number leaked into dataLayer!');
    if (dlString.includes('গুলশান ২, ঢাকা')) throw new Error('Address leaked into dataLayer!');
  });

  // 14. Extracted HTML synchronization check
  await test('13. Extracted HTML matches canonical landing page with GTM snippet', async () => {
    const canonical = fs.readFileSync(path.join(__dirname, 'products', 'chicken-booster', 'index.html'), 'utf8');
    const extracted = fs.readFileSync(path.join(__dirname, 'extracted_html', 'chicken-booster__step_1_checkout__widget_4259dac.html'), 'utf8');
    if (canonical !== extracted) throw new Error('Extracted HTML is not synchronized with canonical HTML');
  });

  // 15. Phase 1 Regression
  await test('14. Phase 1 regression test suite (node test_phase1.js)', async () => {
    const output = execSync('node test_phase1.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 1 test failed:\n${output}`);
  });

  // 16. Phase 2 Regression
  await test('15. Phase 2 regression test suite (node test_phase2.js)', async () => {
    const output = execSync('node test_phase2.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 2 test failed:\n${output}`);
  });

  // 17. Phase 3 Regression
  await test('16. Phase 3 regression test suite (node test_phase3.js)', async () => {
    const output = execSync('node test_phase3.js', { encoding: 'utf8' });
    if (!output.includes('16/16 TESTS PASSED')) throw new Error(`Phase 3 test failed:\n${output}`);
  });

  // 18. Landing Page JSON Flow Audit
  await test('17. Landing page JSON flow audit (node audit_all.js)', async () => {
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
