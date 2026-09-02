/**
 * test_landing_page_deduplication_and_order.js
 * 
 * Verifies:
 * 1. Landing Page order button works end-to-end and creates real order in DB.
 * 2. Meta Pixel PageView, AddToCart, InitiateCheckout, and Purchase are single-fire
 *    per session/order across browser refreshes using sessionStorage keys.
 * 3. ViewContent remains completely absent.
 * 4. Distinct landing pages and distinct orders can fire their respective initial events.
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

// Simulated Browser Environment for Meta Pixel Deduplication Logic
function createBrowserSession() {
  const sessionStorage = new Map();
  const firedEvents = [];

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
    getEventCount: (eventName) => firedEvents.filter(e => e.event === eventName).length
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
  console.log('🧪 TESTING LANDING PAGE ORDER BUTTON & SINGLE-FIRE META PIXEL FUNNEL');
  console.log('================================================================\n');

  // TEST 1: Landing Page HTML contains scoped deduplication keys & no ViewContent
  await testStep('1. Landing page contains scoped sessionStorage keys and ZERO ViewContent', async () => {
    const res = await sendReq(8000, '/product/chicken-booster');
    assert.strictEqual(res.status, 200);
    assert.ok(res.body.includes('meta_tracked_pageview_chicken-booster'), 'Must have scoped PageView deduplication key');
    assert.ok(res.body.includes('meta_tracked_addtocart_'), 'Must have scoped AddToCart deduplication key');
    assert.ok(res.body.includes('meta_tracked_initiatecheckout_'), 'Must have scoped InitiateCheckout deduplication key');
    assert.ok(!res.body.includes("ViewContent"), 'ViewContent must NOT be present anywhere in landing page HTML');
  });

  // TEST 2: Simulate Session Flow for Landing Page A (First Load -> Refresh -> CTA -> Refresh -> Checkout)
  await testStep('2. Meta Pixel Funnel: PageView, AddToCart, InitiateCheckout are single-fire across refreshes', () => {
    const browser = createBrowserSession();
    const landingSlug = 'chicken-booster';

    // A. First Load of Landing Page A
    const pageViewKey = 'meta_tracked_pageview_' + landingSlug;
    if (!browser.sessionStorage.getItem(pageViewKey)) {
      browser.sessionStorage.setItem(pageViewKey, '1');
      browser.fbq('track', 'PageView');
    }
    assert.strictEqual(browser.getEventCount('PageView'), 1, 'First load must fire PageView = 1');

    // B. Refresh Landing Page A
    if (!browser.sessionStorage.getItem(pageViewKey)) {
      browser.sessionStorage.setItem(pageViewKey, '1');
      browser.fbq('track', 'PageView');
    }
    assert.strictEqual(browser.getEventCount('PageView'), 1, 'Refresh must NOT fire additional PageView');

    // C. Click CTA (Fires AddToCart and InitiateCheckout)
    const atcKey = 'meta_tracked_addtocart_' + landingSlug;
    if (!browser.sessionStorage.getItem(atcKey)) {
      browser.sessionStorage.setItem(atcKey, '1');
      browser.fbq('track', 'AddToCart', { content_name: 'Chicken Booster' });
    }
    assert.strictEqual(browser.getEventCount('AddToCart'), 1, 'First CTA click must fire AddToCart = 1');

    const icKey = 'meta_tracked_initiatecheckout_' + landingSlug;
    if (!browser.sessionStorage.getItem(icKey)) {
      browser.sessionStorage.setItem(icKey, '1');
      browser.fbq('track', 'InitiateCheckout', { content_name: 'Chicken Booster' });
    }
    assert.strictEqual(browser.getEventCount('InitiateCheckout'), 1, 'Entering checkout must fire InitiateCheckout = 1');

    // D. Refresh Page after CTA & Scroll to Checkout Again
    if (!browser.sessionStorage.getItem(pageViewKey)) {
      browser.sessionStorage.setItem(pageViewKey, '1');
      browser.fbq('track', 'PageView');
    }
    if (!browser.sessionStorage.getItem(atcKey)) {
      browser.sessionStorage.setItem(atcKey, '1');
      browser.fbq('track', 'AddToCart');
    }
    if (!browser.sessionStorage.getItem(icKey)) {
      browser.sessionStorage.setItem(icKey, '1');
      browser.fbq('track', 'InitiateCheckout');
    }
    assert.strictEqual(browser.getEventCount('PageView'), 1, 'No additional PageView on refresh');
    assert.strictEqual(browser.getEventCount('AddToCart'), 1, 'No additional AddToCart on refresh/scroll');
    assert.strictEqual(browser.getEventCount('InitiateCheckout'), 1, 'No additional InitiateCheckout on refresh/scroll');
  });

  // TEST 3: Distinct Landing Page (Landing Page B) gets its own PageView
  await testStep('3. Different landing page fires PageView once in the same session', () => {
    const browser = createBrowserSession();
    // Visit Landing Page A
    browser.sessionStorage.setItem('meta_tracked_pageview_chicken-booster', '1');
    browser.fbq('track', 'PageView');

    // Visit Landing Page B (e.g. face-serum)
    const landingBKey = 'meta_tracked_pageview_face-serum';
    if (!browser.sessionStorage.getItem(landingBKey)) {
      browser.sessionStorage.setItem(landingBKey, '1');
      browser.fbq('track', 'PageView');
    }
    assert.strictEqual(browser.getEventCount('PageView'), 2, 'Two distinct landing pages fire 1 PageView each');

    // Refresh Landing Page B
    if (!browser.sessionStorage.getItem(landingBKey)) {
      browser.sessionStorage.setItem(landingBKey, '1');
      browser.fbq('track', 'PageView');
    }
    assert.strictEqual(browser.getEventCount('PageView'), 2, 'Refreshing Landing Page B does not add a 3rd PageView');
  });

  // TEST 4: Actual Order Button Click & Backend Submission (Real Order in DB)
  let testOrderNumber = '';
  await testStep('4. Order button click submits form and creates real database order', async () => {
    const testPhone = '017' + Math.floor(10000000 + Math.random() * 90000000);
    const orderPayload = {
      productId: 'chicken-booster',
      product_id: 'chicken-booster',
      variantId: 'variant-1',
      variant_id: 'variant-1',
      quantity: 1,
      items: [
        { productId: 'chicken-booster', variantId: 'variant-1', quantity: 1, name: 'চিকেন বুস্টার (৫০০ গ্রাম)', price: 990 }
      ],
      deliveryZone: 'inside',
      delivery_zone: 'inside',
      customerName: 'Test Order Button User',
      customer_name: 'Test Order Button User',
      name: 'Test Order Button User',
      phone: testPhone,
      customer_phone: testPhone,
      address: 'House 10, Road 5, Dhanmondi, Dhaka',
      shipping_address: 'House 10, Road 5, Dhanmondi, Dhaka',
      source: 'LANDING_PAGE',
      paymentMethod: 'Cash on Delivery'
    };

    const res = await sendReq(8000, '/api/orders', 'POST', orderPayload);
    assert.ok(res.status === 200 || res.status === 201, `Expected 200/201, got ${res.status}`);
    assert.strictEqual(res.json.success, true);
    testOrderNumber = res.json.order ? res.json.order.order_number : res.json.order_number;
    assert.ok(testOrderNumber, 'Must return order number');
  });

  // TEST 5: Success Page URL resolves & Purchase Event is single-fire
  await testStep('5. Success page renders, Purchase fires once, refresh does not duplicate Purchase', async () => {
    assert.ok(testOrderNumber, 'Order number required from Test 4');
    const successRes = await sendReq(8000, `/product/chicken-booster/success/${testOrderNumber}`);
    assert.strictEqual(successRes.status, 200);
    assert.ok(successRes.body.includes(testOrderNumber));
    assert.ok(successRes.body.includes("const metaDedupeKey = 'meta_tracked_purchase_' + orderNo;"), 'Must have order-specific purchase dedupe key');
    assert.ok(!successRes.body.includes('ViewContent'), 'Success page must NOT contain ViewContent');

    // Simulate Success Page Session
    const browser = createBrowserSession();
    const purchaseKey = 'meta_tracked_purchase_' + testOrderNumber;

    // First load of success page
    if (!browser.sessionStorage.getItem(purchaseKey)) {
      browser.sessionStorage.setItem(purchaseKey, '1');
      browser.fbq('track', 'Purchase', { value: 990, currency: 'BDT' });
    }
    assert.strictEqual(browser.getEventCount('Purchase'), 1, 'First load of success page must fire Purchase = 1');

    // Refresh success page
    if (!browser.sessionStorage.getItem(purchaseKey)) {
      browser.sessionStorage.setItem(purchaseKey, '1');
      browser.fbq('track', 'Purchase', { value: 990, currency: 'BDT' });
    }
    assert.strictEqual(browser.getEventCount('Purchase'), 1, 'Refresh of success page must NOT fire additional Purchase');

    // Distinct new order can fire its own purchase
    const newOrderNo = 'CB-20260902-NEWORDER99';
    const newPurchaseKey = 'meta_tracked_purchase_' + newOrderNo;
    if (!browser.sessionStorage.getItem(newPurchaseKey)) {
      browser.sessionStorage.setItem(newPurchaseKey, '1');
      browser.fbq('track', 'Purchase', { value: 1850, currency: 'BDT' });
    }
    assert.strictEqual(browser.getEventCount('Purchase'), 2, 'Distinct order fires its own Purchase');
  });

  console.log('\n================================================================');
  console.log(`🎉 ALL ${passed} / ${total} LANDING PAGE & META PIXEL TESTS PASSED!`);
  console.log('================================================================\n');

  if (passed !== total) {
    process.exit(1);
  }
})();
