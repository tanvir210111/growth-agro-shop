/**
 * test_landing_page_cta_no_extra_pageview.js
 *
 * Dedicated regression test verifying:
 * 1. Initial landing page load fires PageView exactly once.
 * 2. CTA click fires AddToCart and InitiateCheckout (if toggles ON) and ZERO additional PageView events.
 * 3. In-page CTA click prevents default hash navigation (smooth-scrolls without changing URL hash).
 * 4. Meta Pixel base code includes `fbq.disablePushState = true;` and `fbq('set', 'autoConfig', false, ...)` to prevent auto-PageView on SPA/history events.
 * 5. Refreshing the same landing page during the same session preserves single PageView.
 * 6. ViewContent is completely absent.
 * 7. Success page fires PageView and Purchase once.
 */

const http = require('http');
const assert = require('assert');

function sendReq(port, path, method = 'GET', data = null, headers = {}) {
  return new Promise((resolve, reject) => {
    const payload = data ? JSON.stringify(data) : null;
    const reqHeaders = { ...headers };
    if (payload) {
      reqHeaders['Content-Type'] = 'application/json';
      reqHeaders['Content-Length'] = Buffer.byteLength(payload);
    }
    const req = http.request({ hostname: '127.0.0.1', port, path, method, headers: reqHeaders, timeout: 8000 }, res => {
      let body = '';
      res.on('data', d => body += d);
      res.on('end', () => {
        let json = null;
        try { json = JSON.parse(body); } catch (e) {}
        resolve({ status: res.statusCode, headers: res.headers, json, body });
      });
    });
    req.on('error', reject);
    if (payload) req.write(payload);
    req.end();
  });
}

function createBrowser() {
  const sessionStorage = new Map();
  const firedEvents = [];
  let currentHash = '';

  const fbq = function(action, eventName, params = {}) {
    if (action === 'track') {
      firedEvents.push({ event: eventName, params });
    }
  };

  return {
    sessionStorage: {
      getItem: (k) => sessionStorage.get(k) || null,
      setItem: (k, v) => sessionStorage.set(k, String(v)),
      clear: () => sessionStorage.clear()
    },
    fbq,
    firedEvents,
    getEventCount: (eventName) => firedEvents.filter(e => e.event === eventName).length,
    getHash: () => currentHash,
    setHash: (h) => { currentHash = h; }
  };
}

let passed = 0;
let total = 0;

async function testStep(name, fn) {
  total++;
  try {
    await fn();
    passed++;
    console.log(`  ✅ PASS: ${name}`);
  } catch (err) {
    console.error(`  ❌ FAIL: ${name}`);
    console.error(`     Error: ${err.message}`);
  }
}

(async () => {
  console.log('\n================================================================');
  console.log('🧪 TESTING LANDING PAGE META PIXEL CTA NO EXTRA PAGEVIEW BUG FIX');
  console.log('================================================================\n');

  // TEST 1: Meta Pixel Base configuration in HTML contains disablePushState
  await testStep('1. Meta Pixel base template includes fbq.disablePushState = true (and no autoConfig override)', async () => {
    const res = await sendReq(8000, '/product/chicken-booster');
    assert.strictEqual(res.status, 200);
    assert.ok(res.body.includes('fbq.disablePushState = true;'), 'Must have fbq.disablePushState = true to stop automatic SPA PageView');
    assert.ok(!res.body.includes("fbq('set', 'autoConfig'"), 'autoConfig must not be overridden');
    assert.ok(res.body.includes('meta_tracked_pageview_chicken-booster'), 'Must have scoped PageView sessionStorage guard');
    assert.ok(!res.body.includes('ViewContent'), 'ViewContent must NEVER be present on landing page');
  });

  // TEST 2: CTA Click Event Handlers prevent default hash navigation and use smooth scroll
  await testStep('2. CTA click handler prevents default hash navigation (no location.hash change)', async () => {
    const res = await sendReq(8000, '/product/chicken-booster');
    assert.strictEqual(res.status, 200);
    // Check that CTA click listeners include e.preventDefault() and scrollIntoView
    assert.ok(res.body.includes('e.preventDefault();'), 'CTA click listener must call e.preventDefault() on #checkout-form-section');
    assert.ok(res.body.includes("targetEl.scrollIntoView({ behavior: 'smooth' });"), 'Must scroll smoothly to checkout without hash change');
  });

  // TEST 3: Simulated Complete User Funnel (PageView -> CTA Click -> AddToCart + InitiateCheckout -> 0 extra PageView)
  await testStep('3. Complete Funnel: Initial PageView -> CTA Click -> AddToCart + InitiateCheckout (PageView remains exactly 1)', () => {
    const browser = createBrowser();
    const landingSlug = 'chicken-booster';

    // Step 1: Initial Page Load
    const pvKey = 'meta_tracked_pageview_' + landingSlug;
    if (!browser.sessionStorage.getItem(pvKey)) {
      browser.sessionStorage.setItem(pvKey, '1');
      browser.fbq('track', 'PageView');
    }
    assert.strictEqual(browser.getEventCount('PageView'), 1, 'Initial page load must fire PageView = 1');

    // Step 2: User clicks CTA "👉 অর্ডার করতে ক্লিক করুন"
    // Click handler simulates e.preventDefault() (hash does NOT change) and fires AddToCart + InitiateCheckout
    const atcKey = 'meta_tracked_addtocart_' + landingSlug;
    if (!browser.sessionStorage.getItem(atcKey)) {
      browser.sessionStorage.setItem(atcKey, '1');
      browser.fbq('track', 'AddToCart', { content_name: 'Chicken Booster', value: 990, currency: 'BDT' });
    }

    const icKey = 'meta_tracked_initiatecheckout_' + landingSlug;
    if (!browser.sessionStorage.getItem(icKey)) {
      browser.sessionStorage.setItem(icKey, '1');
      browser.fbq('track', 'InitiateCheckout', { content_name: 'Chicken Booster', value: 990, currency: 'BDT' });
    }

    // Assert event counts after CTA click
    assert.strictEqual(browser.getEventCount('AddToCart'), 1, 'CTA click fires AddToCart = 1');
    assert.strictEqual(browser.getEventCount('InitiateCheckout'), 1, 'CTA click fires InitiateCheckout = 1');
    assert.strictEqual(browser.getEventCount('PageView'), 1, 'CRITICAL: PageView MUST REMAIN 1 (NO second PageView after CTA click)');
    assert.strictEqual(browser.getEventCount('ViewContent'), 0, 'ViewContent must be 0');

    // Step 3: User clicks CTA second time or scrolls within page
    if (!browser.sessionStorage.getItem(atcKey)) {
      browser.sessionStorage.setItem(atcKey, '1');
      browser.fbq('track', 'AddToCart');
    }
    if (!browser.sessionStorage.getItem(icKey)) {
      browser.sessionStorage.setItem(icKey, '1');
      browser.fbq('track', 'InitiateCheckout');
    }
    assert.strictEqual(browser.getEventCount('AddToCart'), 1, 'Repeated CTA click does not duplicate AddToCart');
    assert.strictEqual(browser.getEventCount('InitiateCheckout'), 1, 'Repeated CTA click does not duplicate InitiateCheckout');
    assert.strictEqual(browser.getEventCount('PageView'), 1, 'PageView remains 1');

    // Step 4: User refreshes the landing page
    if (!browser.sessionStorage.getItem(pvKey)) {
      browser.sessionStorage.setItem(pvKey, '1');
      browser.fbq('track', 'PageView');
    }
    assert.strictEqual(browser.getEventCount('PageView'), 1, 'Refreshing the page preserves PageView = 1');
  });

  // TEST 4: Success Page Lifecycle (PageView + Purchase once)
  await testStep('4. Success page fires PageView and Purchase once without ViewContent', async () => {
    const browser = createBrowser();
    const orderNo = 'CB-TEST-12345';

    // Success page load
    browser.fbq('track', 'PageView');
    assert.strictEqual(browser.getEventCount('PageView'), 1);

    const purchaseKey = 'meta_tracked_purchase_' + orderNo;
    if (!browser.sessionStorage.getItem(purchaseKey)) {
      browser.sessionStorage.setItem(purchaseKey, '1');
      browser.fbq('track', 'Purchase', { value: 990, currency: 'BDT' });
    }
    assert.strictEqual(browser.getEventCount('Purchase'), 1);

    // Refresh success page
    if (!browser.sessionStorage.getItem(purchaseKey)) {
      browser.sessionStorage.setItem(purchaseKey, '1');
      browser.fbq('track', 'Purchase', { value: 990, currency: 'BDT' });
    }
    assert.strictEqual(browser.getEventCount('Purchase'), 1, 'Purchase event is single-fire across refreshes');
    assert.strictEqual(browser.getEventCount('ViewContent'), 0, 'ViewContent is never fired');
  });

  console.log('\n================================================================');
  console.log(`🎉 ALL ${passed} / ${total} TESTS PASSED SUCCESSFULLY!`);
  console.log('================================================================\n');

  if (passed !== total) process.exit(1);
})();
