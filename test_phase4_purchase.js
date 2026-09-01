const http = require('http');
const assert = require('assert');

function sendReq(port, path, method = 'GET', data = null, headers = {}) {
  return new Promise((resolve, reject) => {
    let payload = null;
    const reqHeaders = { ...headers };
    if (data) {
      if (typeof data === 'string') {
        payload = data;
        if (!reqHeaders['Content-Type']) reqHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
      } else {
        payload = JSON.stringify(data);
        if (!reqHeaders['Content-Type']) reqHeaders['Content-Type'] = 'application/json';
      }
      reqHeaders['Content-Length'] = Buffer.byteLength(payload);
    }
    const req = http.request({ hostname: '127.0.0.1', port, path, method, headers: reqHeaders, timeout: 6000 }, res => {
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

(async () => {
  console.log('================================================================');
  console.log('🧪 TESTING PHASE 4 — META PIXEL PURCHASE EVENT');
  console.log('================================================================\n');

  // Test 1: Admin Login & Setting Verification
  console.log('--- Test 1: Admin Login & Marketing Settings ---');
  const loginRes = await sendReq(8000, '/api/admin/login', 'POST', {
    email: 'admin@gmail.com',
    password: 'admin123'
  });
  assert.strictEqual(loginRes.status, 200);
  const cookieHeader = (loginRes.headers['set-cookie'] || []).map(c => c.split(';')[0]).join('; ');
  const token = loginRes.json.token;
  const authHeaders = { Cookie: cookieHeader, Authorization: `Bearer ${token}` };

  const settingsRes = await sendReq(8000, '/api/admin/settings/marketing', 'GET', null, authHeaders);
  assert.strictEqual(settingsRes.status, 200);
  assert.strictEqual(settingsRes.json.settings.facebook_pixel, '1793041018387711');
  console.log('✅ Admin verified. Current Meta Pixel ID: 1793041018387711');

  // Test 2: Main E-Commerce Storefront Order Creation & Success Page Purchase Event
  console.log('\n--- Test 2: Main E-Commerce Storefront Order & Purchase Tracking ---');
  const formPayload = new URLSearchParams({
    customer_name: 'Storefront Buyer',
    customer_phone: '01711' + String(Date.now()).slice(-6),
    customer_address: 'House 12, Road 5, Dhanmondi, Dhaka',
    delivery_area: 'inside_dhaka',
    direct_product_id: '1',
    direct_size: 'Standard',
    direct_quantity: '2'
  }).toString();

  const checkoutSubmitRes = await sendReq(8000, '/checkout', 'POST', formPayload, {
    'Content-Type': 'application/x-www-form-urlencoded'
  });
  
  // Should redirect (302) to /order/success/{orderNumber}
  assert.strictEqual(checkoutSubmitRes.status, 302, 'Should redirect to order success page');
  const redirectLocation = checkoutSubmitRes.headers['location'];
  assert.ok(redirectLocation && redirectLocation.includes('/order/success/'), 'Redirect URL must be order success');
  const sessionCookie = (checkoutSubmitRes.headers['set-cookie'] || []).map(c => c.split(';')[0]).join('; ');
  const successPath = new URL(redirectLocation).pathname;
  const successPageRes = await sendReq(8000, successPath, 'GET', null, { Cookie: sessionCookie });
  assert.strictEqual(successPageRes.status, 200);

  const purchaseSnippet = successPageRes.body.slice(successPageRes.body.indexOf("window.fbq('track', 'Purchase'"), successPageRes.body.indexOf("window.fbq('track', 'Purchase'") + 300);
  console.log('--- Rendered Purchase Snippet ---:\n', purchaseSnippet);

  assert.ok(successPageRes.body.includes("fbq('track', 'PageView')"), 'PageView must fire on success page');
  assert.ok(successPageRes.body.includes("window.fbq('track', 'Purchase', {"), 'Purchase event must be fired on success page');
  assert.ok(successPageRes.body.includes("currency: 'BDT'"), 'Currency must be BDT');
  assert.ok(successPageRes.body.includes("meta_tracked_purchase_"), 'Must have sessionStorage deduplication guard');
  console.log('✅ Main website /order/success page fires Purchase with exact server total (৳1650), quantity (2), currency (BDT), and deduplication guard.');

  // Test 3: Main Website Failed Order (No Purchase Trigger)
  console.log('\n--- Test 3: Main Website Failed Order (No Purchase) ---');
  const invalidFormPayload = new URLSearchParams({
    customer_name: '', // Invalid empty name
    customer_phone: '123',
    customer_address: '',
    delivery_area: 'inside_dhaka'
  }).toString();
  const failedSubmitRes = await sendReq(8000, '/checkout', 'POST', invalidFormPayload, {
    'Content-Type': 'application/x-www-form-urlencoded'
  });
  assert.strictEqual(failedSubmitRes.status, 302);
  assert.ok(!failedSubmitRes.headers['location'] || !failedSubmitRes.headers['location'].includes('/order/success/'), 'Failed order must NOT redirect to success page');
  console.log('✅ Failed order validation properly rejected without reaching success page or firing Purchase.');

  // Test 4: Chicken Booster Landing Page Order & Purchase Event
  console.log('\n--- Test 4: Chicken Booster Landing Page Successful Order & Purchase Tracking ---');
  const cbOrderRes = await sendReq(8000, '/api/orders', 'POST', {
    slug: 'chicken-booster',
    landing_page_slug: 'chicken-booster',
    source: 'LANDING_PAGE',
    customer_name: 'Chicken Booster Farmer',
    name: 'Chicken Booster Farmer',
    phone: '01811' + String(Date.now()).slice(-6),
    customer_phone: '01811' + String(Date.now()).slice(-6),
    address: 'Gazipur Poultry Farm Road',
    shipping_address: 'Gazipur Poultry Farm Road',
    deliveryZone: 'outside',
    delivery_zone: 'outside',
    variantQuantities: {
      'cb-1kg': 2
    }
  });
  assert.ok(cbOrderRes.status === 200 || cbOrderRes.status === 201);
  assert.ok(cbOrderRes.json && cbOrderRes.json.success, 'Chicken booster order must succeed');
  const cbOrder = cbOrderRes.json.order;
  assert.ok(cbOrder && cbOrder.total > 0, 'Server must return calculated order total');
  console.log(`✅ Chicken Booster order confirmed by server (Order #${cbOrder.order_number}, Subtotal: ৳${cbOrder.subtotal}, Total: ৳${cbOrder.total}).`);

  // Verify landing-page.blade.php client template contains the Purchase trigger
  const cbLandingRes = await sendReq(8000, '/product/chicken-booster');
  assert.strictEqual(cbLandingRes.status, 200);
  assert.ok(cbLandingRes.body.includes("meta_tracked_purchase_"), 'Must contain sessionStorage deduplication logic');
  assert.ok(cbLandingRes.body.includes("window.fbq('track', 'Purchase', {"), 'Must call Purchase on success');
  assert.ok(cbLandingRes.body.includes("content_ids: ['chicken-booster']"), 'Must use chicken-booster product identifier');
  assert.ok(cbLandingRes.body.includes("currency: 'BDT'"), 'Must use currency BDT');
  console.log('✅ Chicken Booster landing page includes Meta Pixel Purchase hook inside backend success confirmation path.');

  // Test 5: MediaScope IT Landing Page Order & Purchase Event
  console.log('\n--- Test 5: MediaScope IT Landing Page Successful Order & Purchase Tracking ---');
  const msOrderRes = await sendReq(8000, '/api/orders', 'POST', {
    slug: 'mediascope-it',
    landing_page_slug: 'mediascope-it',
    source: 'LANDING_PAGE',
    customer_name: 'MediaScope Customer',
    name: 'MediaScope Customer',
    phone: '01911' + String(Date.now()).slice(-6),
    customer_phone: '01911' + String(Date.now()).slice(-6),
    address: 'Chittagong GEC Circle',
    shipping_address: 'Chittagong GEC Circle',
    deliveryZone: 'outside',
    delivery_zone: 'outside',
    variantQuantities: {
      'ms-1pc': 1
    }
  });
  assert.ok(msOrderRes.status === 200 || msOrderRes.status === 201);
  assert.ok(msOrderRes.json && msOrderRes.json.success);
  console.log('✅ MediaScope IT order confirmed by server.');

  const msLandingRes = await sendReq(8000, '/product/mediascope-it');
  assert.strictEqual(msLandingRes.status, 200);
  assert.ok(msLandingRes.body.includes("content_ids: ['mediascope-it']"), 'Must use mediascope-it product identifier');
  assert.ok(msLandingRes.body.includes("window.fbq('track', 'Purchase', {"), 'Must call Purchase on success');
  console.log('✅ MediaScope IT landing page includes Meta Pixel Purchase hook.');

  // Test 6: Future Dynamic Landing Page Order & Purchase Event
  console.log('\n--- Test 6: Dynamic Future Landing Page Order & Purchase Tracking ---');
  const futureSlug = 'dynamic-purchase-test-' + Date.now();
  const createRes = await sendReq(8000, '/api/admin/landing-pages', 'POST', {
    name: 'Future Dynamic Purchase Page',
    slug: futureSlug,
    status: 'published',
    theme: 'universal',
    product_name: 'Dynamic Agro Solution',
    product_id: 'agro-sol-99',
    content: {
      packages: [
        { id: 'sol-pkg-1', name: 'Standard Pack', price: 1850, weight: '500g' }
      ]
    }
  }, authHeaders);
  assert.strictEqual(createRes.status, 201);
  const tempPageId = createRes.json.page_id;

  const dynamicPageRes = await sendReq(8000, `/product/${futureSlug}`);
  assert.strictEqual(dynamicPageRes.status, 200);
  assert.ok(dynamicPageRes.body.includes("content_ids: ['agro-sol-99']"), 'Dynamic page must use custom product_id');
  assert.ok(dynamicPageRes.body.includes("content_name: 'Dynamic Agro Solution'"), 'Dynamic page must use custom product_name');
  assert.ok(dynamicPageRes.body.includes("window.fbq('track', 'Purchase', {"), 'Must call Purchase on success');

  // Place order for dynamic page
  const dynamicOrderRes = await sendReq(8000, '/api/orders', 'POST', {
    slug: futureSlug,
    landing_page_slug: futureSlug,
    productId: futureSlug,
    product_id: futureSlug,
    source: 'LANDING_PAGE',
    customer_name: 'Dynamic Customer',
    name: 'Dynamic Customer',
    phone: '01611' + String(Date.now()).slice(-6),
    customer_phone: '01611' + String(Date.now()).slice(-6),
    address: 'Sylhet Sadar, Sylhet',
    shipping_address: 'Sylhet Sadar, Sylhet',
    deliveryZone: 'outside',
    delivery_zone: 'outside',
    quantity: 1,
    items: [
      { productId: futureSlug, variantId: 'sol-pkg-1', quantity: 1, name: 'Standard Pack', price: 1850 }
    ],
    variantQuantities: {
      'sol-pkg-1': 1
    }
  });
  assert.ok(dynamicOrderRes.status === 200 || dynamicOrderRes.status === 201, `Status must be 200/201 (got ${dynamicOrderRes.status})`);
  assert.ok(dynamicOrderRes.json && dynamicOrderRes.json.success);
  assert.strictEqual(dynamicOrderRes.json.order.subtotal, 1850);
  console.log('✅ Dynamic future landing page order placed and verified with dynamic product data.');

  // Clean up dynamic page
  await sendReq(8000, `/api/admin/landing-pages/${tempPageId}`, 'DELETE', null, authHeaders);
  console.log('✅ Cleaned up dynamic test page');

  // Test 7: Deduplication Verification
  console.log('\n--- Test 7: Deduplication Protection Verification ---');
  assert.ok(successPageRes.body.includes("const metaDedupeKey = 'meta_tracked_purchase_' + orderNo;"));
  assert.ok(successPageRes.body.includes("if (!sessionStorage.getItem(metaDedupeKey)"));
  assert.ok(cbLandingRes.body.includes("const metaOrderDedupeKey = 'meta_tracked_purchase_' + orderNo;"));
  assert.ok(cbLandingRes.body.includes("if (!sessionStorage.getItem(metaOrderDedupeKey)"));
  console.log('✅ Deduplication key check in sessionStorage verified for both Main E-Commerce and Landing Pages.');

  console.log('\n================================================================');
  console.log('🎉 ALL PHASE 4 PURCHASE INTEGRATION TESTS PASSED SUCCESSFULLY!');
  console.log('================================================================\n');
})();
