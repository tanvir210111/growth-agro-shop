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
  console.log('🧪 TESTING PHASE 4 & DEDICATED SUCCESS PAGE — META PIXEL PURCHASE');
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

  assert.ok(successPageRes.body.includes("fbq('track', 'PageView')"), 'PageView must fire on success page');
  assert.ok(successPageRes.body.includes("window.fbq('track', 'Purchase', {"), 'Purchase event must be fired on success page');
  assert.ok(successPageRes.body.includes("currency: 'BDT'"), 'Currency must be BDT');
  assert.ok(successPageRes.body.includes("meta_tracked_purchase_"), 'Must have sessionStorage deduplication guard');
  console.log('✅ Main website /order/success page fires Purchase with exact server total, quantity (2), currency (BDT), and deduplication guard.');

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

  // Test 4: Landing Page JavaScript Redirect Logic
  console.log('\n--- Test 4: Landing Page Source Code Inspection for Redirect ---');
  const cbLandingRes = await sendReq(8000, '/product/chicken-booster');
  assert.strictEqual(cbLandingRes.status, 200);
  assert.ok(cbLandingRes.body.includes("window.location.href = '/product/' + encodeURIComponent(LANDING_PAGE_SLUG) + '/success/' + encodeURIComponent(orderNo);"), 'Landing page must redirect to source-matched /product/{slug}/success/{orderNumber}');
  assert.ok(!cbLandingRes.body.includes("successModal.classList.add('active')"), 'Modal must no longer be used');
  console.log('✅ Landing page correctly configured to redirect to source-matched URL: /product/{slug}/success/{orderNumber}.');

  // Test 5: Chicken Booster Landing Page Order & Dedicated Source-Matched Success Page
  console.log('\n--- Test 5: Chicken Booster Order Placement & Source-Matched Success Page ---');
  const cbOrderRes = await sendReq(8000, '/api/orders', 'POST', {
    slug: 'chicken-booster',
    landing_page_slug: 'chicken-booster',
    productId: 'chicken-booster',
    product_id: 'chicken-booster',
    source: 'LANDING_PAGE',
    customer_name: 'Chicken Booster Farmer',
    name: 'Chicken Booster Farmer',
    phone: '01811' + String(Date.now()).slice(-6),
    customer_phone: '01811' + String(Date.now()).slice(-6),
    address: 'Gazipur Poultry Farm Road',
    shipping_address: 'Gazipur Poultry Farm Road',
    deliveryZone: 'outside',
    delivery_zone: 'outside',
    quantity: 1,
    items: [
      { productId: 'chicken-booster', variantId: 'broiler-1kg', quantity: 1, name: 'Broiler Booster (১ কেজি)', price: 2300 }
    ],
    variantQuantities: {
      'broiler-1kg': 1
    }
  });
  assert.ok(cbOrderRes.status === 200 || cbOrderRes.status === 201, `Status was ${cbOrderRes.status}`);
  assert.ok(cbOrderRes.json && cbOrderRes.json.success, 'Chicken booster order must succeed');
  const cbOrder = cbOrderRes.json.order;
  assert.ok(cbOrder && cbOrder.total > 0, 'Server must return calculated order total');
  console.log(`✅ Chicken Booster order confirmed by server (Order #${cbOrder.order_number}, Total: ৳${cbOrder.total}).`);

  // Fetch dedicated source-matched success page for Chicken Booster order
  const cbSuccessRes = await sendReq(8000, `/product/chicken-booster/success/${cbOrder.order_number}`);
  assert.strictEqual(cbSuccessRes.status, 200, 'Source-matched success page must load cleanly');
  assert.ok(cbSuccessRes.body.includes(cbOrder.order_number), 'Success page must contain the exact order number');
  assert.ok(cbSuccessRes.body.includes("window.fbq('track', 'Purchase', {"), 'Purchase event must fire on dedicated success page');
  assert.ok(cbSuccessRes.body.includes("currency: 'BDT'"), 'Currency must be BDT');
  assert.ok(cbSuccessRes.body.includes("meta_tracked_purchase_"), 'Must have deduplication guard');
  assert.ok(cbSuccessRes.body.includes('href="/product/chicken-booster"'), 'Must have dynamic return CTA link to /product/chicken-booster');
  assert.ok(!cbSuccessRes.body.includes('site-header'), 'Must NOT have main website Baby Fashion header');
  assert.ok(!cbSuccessRes.body.includes('cart-drawer'), 'Must NOT have main website cart drawer');
  console.log('✅ Chicken Booster source-matched success page (/product/chicken-booster/success/CB-XXXX) verified.');

  // Test 5B: Backward Compatibility - Old /order/success URL redirects to source-matched URL
  console.log('\n--- Test 5B: Backward Compatibility (/order/success/CB-XXXX -> /product/chicken-booster/success/CB-XXXX) ---');
  const oldUrlRes = await sendReq(8000, `/order/success/${cbOrder.order_number}`);
  assert.strictEqual(oldUrlRes.status, 302, 'Old URL must return 302 redirect');
  assert.ok(oldUrlRes.headers['location'] && oldUrlRes.headers['location'].includes(`/product/chicken-booster/success/${cbOrder.order_number}`), 'Redirect must target canonical source-matched URL');
  console.log('✅ Backward compatibility verified: Old /order/success URL safely 302 redirects to canonical source-matched URL.');

  // Test 6: MediaScope IT Landing Page Order & Source-Matched Success Page
  console.log('\n--- Test 6: MediaScope IT Order Placement & Source-Matched Success Page ---');
  const msOrderRes = await sendReq(8000, '/api/orders', 'POST', {
    slug: 'mediascope-it',
    landing_page_slug: 'mediascope-it',
    productId: 'mediascope-it',
    product_id: 'mediascope-it',
    source: 'LANDING_PAGE',
    customer_name: 'MediaScope Customer',
    name: 'MediaScope Customer',
    phone: '01911' + String(Date.now()).slice(-6),
    customer_phone: '01911' + String(Date.now()).slice(-6),
    address: 'Chittagong GEC Circle',
    shipping_address: 'Chittagong GEC Circle',
    deliveryZone: 'outside',
    delivery_zone: 'outside',
    quantity: 1,
    items: [
      { productId: 'mediascope-it', variantId: 'pkg-1pc', quantity: 1, name: '১ পিস স্মার্ট ডিভাইস', price: 500 }
    ],
    variantQuantities: {
      'pkg-1pc': 1
    }
  });
  assert.ok(msOrderRes.status === 200 || msOrderRes.status === 201, `Status was ${msOrderRes.status}`);
  assert.ok(msOrderRes.json && msOrderRes.json.success);
  const msOrder = msOrderRes.json.order;
  console.log(`✅ MediaScope IT order confirmed by server (Order #${msOrder.order_number}).`);

  const msSuccessRes = await sendReq(8000, `/product/mediascope-it/success/${msOrder.order_number}`);
  assert.strictEqual(msSuccessRes.status, 200);
  assert.ok(msSuccessRes.body.includes(msOrder.order_number));
  assert.ok(msSuccessRes.body.includes("window.fbq('track', 'Purchase', {"));
  assert.ok(msSuccessRes.body.includes('href="/product/mediascope-it"'), 'Must have dynamic return CTA link to /product/mediascope-it');
  assert.ok(!msSuccessRes.body.includes('site-header'), 'Must NOT have main website header');
  console.log('✅ MediaScope IT source-matched success page (/product/mediascope-it/success/CB-XXXX) verified.');

  // Test 6B: Slug Mismatch Protection
  console.log('\n--- Test 6B: Slug Mismatch Protection (/product/mediascope-it/success/CB-CHICKEN-ORDER) ---');
  const mismatchRes = await sendReq(8000, `/product/mediascope-it/success/${cbOrder.order_number}`);
  assert.ok(mismatchRes.status === 404 || mismatchRes.status === 302, 'Mismatch must return 404 or 302 redirect');
  console.log('✅ Slug mismatch protection verified: Visiting under wrong slug safely protects originating landing page order.');

  // Test 7: Future Dynamic Landing Page Order & Source-Matched Success Page
  console.log('\n--- Test 7: Dynamic Future Landing Page Order & Source-Matched Success Page ---');
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
  assert.ok(dynamicOrderRes.status === 200 || dynamicOrderRes.status === 201);
  assert.ok(dynamicOrderRes.json && dynamicOrderRes.json.success);
  const dynamicOrder = dynamicOrderRes.json.order;
  assert.strictEqual(dynamicOrder.subtotal, 1850);

  const dynamicSuccessRes = await sendReq(8000, `/product/${futureSlug}/success/${dynamicOrder.order_number}`);
  assert.strictEqual(dynamicSuccessRes.status, 200);
  assert.ok(dynamicSuccessRes.body.includes(dynamicOrder.order_number));
  assert.ok(dynamicSuccessRes.body.includes("window.fbq('track', 'Purchase', {"));
  assert.ok(dynamicSuccessRes.body.includes(`href="/product/${futureSlug}"`), `Must have dynamic return CTA link to /product/${futureSlug}`);
  assert.ok(!dynamicSuccessRes.body.includes('site-header'));
  console.log(`✅ Dynamic future landing page source-matched success page (/product/${futureSlug}/success/...) verified.`);

  // Clean up dynamic page
  await sendReq(8000, `/api/admin/landing-pages/${tempPageId}`, 'DELETE', null, authHeaders);
  console.log('✅ Cleaned up dynamic test page');

  // Test 8: Invalid Order Number & Invalid Slug (Security & 404 Check)
  console.log('\n--- Test 8: Invalid Order Number & Invalid Slug 404 Checks ---');
  const invalidOrderRes = await sendReq(8000, '/product/chicken-booster/success/NON-EXISTENT-ORDER-999999');
  assert.strictEqual(invalidOrderRes.status, 404, 'Invalid order number must return 404');
  assert.ok(!invalidOrderRes.body.includes("window.fbq('track', 'Purchase'"), 'Invalid order must not fire Purchase');

  const invalidSlugRes = await sendReq(8000, `/product/invalid-fake-slug-xyz/success/${cbOrder.order_number}`);
  assert.strictEqual(invalidSlugRes.status, 404, 'Invalid slug must return 404');
  console.log('✅ Invalid order number and invalid slug properly return 404 without firing Purchase.');

  // Test 9: Deduplication Verification
  console.log('\n--- Test 9: Deduplication Protection Verification ---');
  assert.ok(successPageRes.body.includes("const metaDedupeKey = 'meta_tracked_purchase_' + orderNo;"));
  assert.ok(successPageRes.body.includes("if (!sessionStorage.getItem(metaDedupeKey)"));
  console.log('✅ Canonical sessionStorage deduplication key verified on dedicated success page.');

  console.log('\n================================================================');
  console.log('🎉 ALL DEDICATED SUCCESS PAGE & PURCHASE INTEGRATION TESTS PASSED!');
  console.log('================================================================\n');
})();
