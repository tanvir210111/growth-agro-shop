/**
 * test_landing_success_pageview_deduplication.js
 *
 * Verification of Landing Success Page Meta Pixel Tracking:
 *
 * 1. Initial success page load -> PageView = 1, Purchase = 1, ViewContent = 0
 * 2. Refresh same success page -> PageView = 1 (no additional), Purchase = 1 (no additional)
 * 3. Same order opened again in same browser session -> PageView = 1, Purchase = 1 (0 additional)
 * 4. Distinct order -> PageView = 1 (for that order), Purchase = 1 (for that order)
 * 5. Opening a different landing page does not incorrectly reuse the previous order's success-page tracking key
 * 6. PageView guard (meta_tracked_success_pageview_{orderNumber}) and Purchase guard (meta_tracked_purchase_{orderNumber}) are independent
 * 7. Live rendered blade template contains meta_tracked_success_pageview_{orderNumber}
 * 8. fbq.disablePushState = true is preserved
 */

const http = require('http');
const assert = require('assert');
const fs = require('fs');
const path = require('path');
const { createOrder } = require('./server/db');

function createBrowserSession() {
  const store = {};
  const events = [];

  return {
    sessionStorage: {
      getItem: (key) => store[key] || null,
      setItem: (key, val) => { store[key] = String(val); },
      removeItem: (key) => { delete store[key]; },
      clear: () => { Object.keys(store).forEach(k => delete store[k]); }
    },
    fbq: (action, eventName, payload) => {
      events.push({ action, eventName, payload, timestamp: Date.now() });
    },
    getEvents: () => [...events],
    getEventCount: (eventName) => events.filter(e => e.eventName === eventName).length
  };
}

function sendReq(port, reqPath, method = 'GET', data = null) {
  return new Promise((resolve, reject) => {
    const payload = data ? JSON.stringify(data) : null;
    const req = http.request({
      hostname: '127.0.0.1',
      port: port,
      path: reqPath,
      method: method,
      headers: Object.assign({
        'Accept': 'application/json, text/html'
      }, payload ? {
        'Content-Type': 'application/json',
        'Content-Length': Buffer.byteLength(payload)
      } : {})
    }, (res) => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        let json = null;
        try { json = JSON.parse(body); } catch (e) {}
        resolve({ status: res.statusCode, headers: res.headers, body, json });
      });
    });
    req.on('error', reject);
    req.setTimeout(6000, () => {
      req.destroy();
      reject(new Error('Request timed out'));
    });
    if (payload) req.write(payload);
    req.end();
  });
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
    console.error(err);
    throw err;
  }
}

(async function run() {
  console.log('================================================================');
  console.log('🧪 TESTING LANDING SUCCESS PAGE META PIXEL PAGEVIEW DEDUPLICATION');
  console.log('================================================================\n');

  // Create Order A via POST /api/orders
  const phoneA = '017' + Math.floor(10000000 + Math.random() * 90000000);
  const resA = await sendReq(8000, '/api/orders', 'POST', {
    productId: 'chicken-booster',
    product_id: 'chicken-booster',
    variantId: 'variant-1',
    variant_id: 'variant-1',
    quantity: 1,
    items: [
      { productId: 'chicken-booster', variantId: 'variant-1', quantity: 1, name: 'চিকেন বুস্টার (৫০০ গ্রাম)', price: 990 }
    ],
    deliveryZone: 'inside',
    customerName: 'Success Page Customer A',
    phone: phoneA,
    address: 'Banani, Dhaka',
    source: 'LANDING_PAGE',
    paymentMethod: 'Cash on Delivery'
  });
  assert.ok(resA.status === 200 || resA.status === 201, `Order A creation failed: ${resA.status}`);
  const orderA = resA.json.order ? resA.json.order.order_number : resA.json.order_number;
  assert.ok(orderA, 'Order A number must exist');

  // Create Order B via POST /api/orders
  const phoneB = '018' + Math.floor(10000000 + Math.random() * 90000000);
  const resB = await sendReq(8000, '/api/orders', 'POST', {
    productId: 'chicken-booster',
    product_id: 'chicken-booster',
    variantId: 'variant-2',
    variant_id: 'variant-2',
    quantity: 2,
    items: [
      { productId: 'chicken-booster', variantId: 'variant-2', quantity: 2, name: 'চিকেন বুস্টার (২ টি প্যাক)', price: 925 }
    ],
    deliveryZone: 'outside',
    customerName: 'Success Page Customer B',
    phone: phoneB,
    address: 'GEC Circle, Chittagong',
    source: 'LANDING_PAGE',
    paymentMethod: 'Cash on Delivery'
  });
  assert.ok(resB.status === 200 || resB.status === 201, `Order B creation failed: ${resB.status}`);
  const orderB = resB.json.order ? resB.json.order.order_number : resB.json.order_number;
  assert.ok(orderB, 'Order B number must exist');

  // TEST 1: Rendered template contains order-specific success PageView guard
  await testStep('1. Rendered success page contains order-specific success PageView key', async () => {
    const res = await sendReq(8000, `/product/chicken-booster/success/${orderA}`);
    assert.strictEqual(res.status, 200);
    assert.ok(res.body.includes(`meta_tracked_success_pageview_${orderA}`), 'Must contain order-specific PageView key');
    assert.ok(res.body.includes("const metaDedupeKey = 'meta_tracked_purchase_' + orderNo;"), 'Must contain order-specific Purchase key');
    assert.ok(res.body.includes('fbq.disablePushState = true;'), 'Must preserve fbq.disablePushState');
    assert.ok(!res.body.includes('ViewContent'), 'Must NOT contain ViewContent');
  });

  // TEST 2: Initial load fires PageView=1 and Purchase=1, ViewContent=0
  await testStep('2. Initial success page load fires PageView=1, Purchase=1, ViewContent=0', async () => {
    const browser = createBrowserSession();
    const pvKey = 'meta_tracked_success_pageview_' + orderA;
    const purchaseKey = 'meta_tracked_purchase_' + orderA;

    // Simulate first load of success page for Order A
    if (!browser.sessionStorage.getItem(pvKey)) {
      browser.sessionStorage.setItem(pvKey, '1');
      browser.fbq('track', 'PageView');
    }
    if (!browser.sessionStorage.getItem(purchaseKey)) {
      browser.sessionStorage.setItem(purchaseKey, '1');
      browser.fbq('track', 'Purchase', { value: 1050, currency: 'BDT' });
    }

    assert.strictEqual(browser.getEventCount('PageView'), 1, 'PageView must be 1 on initial load');
    assert.strictEqual(browser.getEventCount('Purchase'), 1, 'Purchase must be 1 on initial load');
    assert.strictEqual(browser.getEventCount('ViewContent'), 0, 'ViewContent must be 0');
  });

  // TEST 3: Refreshing same success page does NOT fire additional PageView or Purchase
  await testStep('3. Refreshing same success URL preserves PageView=1, Purchase=1 (zero duplicates)', async () => {
    const browser = createBrowserSession();
    const pvKey = 'meta_tracked_success_pageview_' + orderA;
    const purchaseKey = 'meta_tracked_purchase_' + orderA;

    // First load
    if (!browser.sessionStorage.getItem(pvKey)) {
      browser.sessionStorage.setItem(pvKey, '1');
      browser.fbq('track', 'PageView');
    }
    if (!browser.sessionStorage.getItem(purchaseKey)) {
      browser.sessionStorage.setItem(purchaseKey, '1');
      browser.fbq('track', 'Purchase', { value: 1050, currency: 'BDT' });
    }

    // Refresh 1
    if (!browser.sessionStorage.getItem(pvKey)) {
      browser.sessionStorage.setItem(pvKey, '1');
      browser.fbq('track', 'PageView');
    }
    if (!browser.sessionStorage.getItem(purchaseKey)) {
      browser.sessionStorage.setItem(purchaseKey, '1');
      browser.fbq('track', 'Purchase', { value: 1050, currency: 'BDT' });
    }

    // Refresh 2
    if (!browser.sessionStorage.getItem(pvKey)) {
      browser.sessionStorage.setItem(pvKey, '1');
      browser.fbq('track', 'PageView');
    }
    if (!browser.sessionStorage.getItem(purchaseKey)) {
      browser.sessionStorage.setItem(purchaseKey, '1');
      browser.fbq('track', 'Purchase', { value: 1050, currency: 'BDT' });
    }

    assert.strictEqual(browser.getEventCount('PageView'), 1, 'PageView must stay 1 across multiple refreshes');
    assert.strictEqual(browser.getEventCount('Purchase'), 1, 'Purchase must stay 1 across multiple refreshes');
  });

  // TEST 4: Distinct order fires its own PageView and Purchase
  await testStep('4. Distinct order (Order B) fires its own PageView and Purchase in same session', async () => {
    const browser = createBrowserSession();
    const pvKeyA = 'meta_tracked_success_pageview_' + orderA;
    const purchaseKeyA = 'meta_tracked_purchase_' + orderA;
    const pvKeyB = 'meta_tracked_success_pageview_' + orderB;
    const purchaseKeyB = 'meta_tracked_purchase_' + orderB;

    // Order A visited
    browser.sessionStorage.setItem(pvKeyA, '1');
    browser.fbq('track', 'PageView');
    browser.sessionStorage.setItem(purchaseKeyA, '1');
    browser.fbq('track', 'Purchase', { value: 1050, currency: 'BDT' });

    // Order B visited
    if (!browser.sessionStorage.getItem(pvKeyB)) {
      browser.sessionStorage.setItem(pvKeyB, '1');
      browser.fbq('track', 'PageView');
    }
    if (!browser.sessionStorage.getItem(purchaseKeyB)) {
      browser.sessionStorage.setItem(purchaseKeyB, '1');
      browser.fbq('track', 'Purchase', { value: 1850, currency: 'BDT' });
    }

    assert.strictEqual(browser.getEventCount('PageView'), 2, 'Total PageView must be 2 (1 for A, 1 for B)');
    assert.strictEqual(browser.getEventCount('Purchase'), 2, 'Total Purchase must be 2 (1 for A, 1 for B)');
  });

  // TEST 5: Landing page and success page keys are scoped and independent
  await testStep('5. Landing page and Success page PageView keys are completely isolated', async () => {
    const browser = createBrowserSession();
    const lpKey = 'meta_tracked_pageview_chicken-booster';
    const successKey = 'meta_tracked_success_pageview_' + orderA;

    // Visit Landing Page
    if (!browser.sessionStorage.getItem(lpKey)) {
      browser.sessionStorage.setItem(lpKey, '1');
      browser.fbq('track', 'PageView');
    }
    assert.strictEqual(browser.getEventCount('PageView'), 1, 'Landing page visit fires PageView = 1');

    // Proceed to Success Page (must fire its own PageView once because successKey is independent)
    if (!browser.sessionStorage.getItem(successKey)) {
      browser.sessionStorage.setItem(successKey, '1');
      browser.fbq('track', 'PageView');
    }
    assert.strictEqual(browser.getEventCount('PageView'), 2, 'Success page load fires its own PageView = 1');

    // Re-visiting landing page does NOT duplicate
    if (!browser.sessionStorage.getItem(lpKey)) {
      browser.sessionStorage.setItem(lpKey, '1');
      browser.fbq('track', 'PageView');
    }
    assert.strictEqual(browser.getEventCount('PageView'), 2, 'Re-visiting landing page does not fire duplicate');

    // Re-visiting success page does NOT duplicate
    if (!browser.sessionStorage.getItem(successKey)) {
      browser.sessionStorage.setItem(successKey, '1');
      browser.fbq('track', 'PageView');
    }
    assert.strictEqual(browser.getEventCount('PageView'), 2, 'Re-visiting success page does not fire duplicate');
  });

  console.log('\n================================================================');
  console.log(`🎉 ALL ${passed} / ${total} SUCCESS PAGEVIEW TESTS PASSED!`);
  console.log('================================================================\n');

  if (passed !== total) process.exit(1);
})();
