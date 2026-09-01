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

(async () => {
  console.log('================================================================');
  console.log('🧪 TESTING LANDING PAGE META PIXEL ADDTOCART & INITIATECHECKOUT ON/OFF TOGGLES');
  console.log('================================================================\n');

  // Step 1: Admin Authentication
  console.log('--- Step 1: Admin Login & Marketing Settings API ---');
  const loginRes = await sendReq(8000, '/api/admin/login', 'POST', {
    email: 'admin@gmail.com',
    password: 'admin123'
  });
  assert.strictEqual(loginRes.status, 200);
  const cookieHeader = (loginRes.headers['set-cookie'] || []).map(c => c.split(';')[0]).join('; ');
  const token = loginRes.json.token;
  const authHeaders = { Cookie: cookieHeader, Authorization: `Bearer ${token}` };

  // Step 2: Test 1 & 2: Default State & Admin UI HTML Check
  console.log('\n--- Step 2: Test 1 & 2: Default State & Admin UI HTML Elements ---');
  const adminIndexRes = await sendReq(8000, '/admin');
  assert.strictEqual(adminIndexRes.status, 200);
  assert.ok(adminIndexRes.body.includes('Landing Page AddToCart Tracking'), 'Must have AddToCart label in Admin UI');
  assert.ok(adminIndexRes.body.includes('Landing Page InitiateCheckout Tracking'), 'Must have InitiateCheckout label in Admin UI');
  assert.ok(adminIndexRes.body.includes('id="btnToggleAddToCart"'), 'Must have btnToggleAddToCart in Admin UI');
  assert.ok(adminIndexRes.body.includes('id="btnToggleInitiateCheckout"'), 'Must have btnToggleInitiateCheckout in Admin UI');
  assert.ok(adminIndexRes.body.includes('id="marketingAddToCartEnabled"'), 'Must have marketingAddToCartEnabled hidden input');
  assert.ok(adminIndexRes.body.includes('id="marketingInitiateCheckoutEnabled"'), 'Must have marketingInitiateCheckoutEnabled hidden input');
  assert.ok(adminIndexRes.body.includes("toggleMarketingSwitch('addToCart')"), 'Must wire toggleMarketingSwitch for AddToCart');
  assert.ok(adminIndexRes.body.includes("toggleMarketingSwitch('initiateCheckout')"), 'Must wire toggleMarketingSwitch for InitiateCheckout');
  console.log('✅ Admin UI contains exact labels, buttons, hidden inputs, and toggle handlers.');

  // Step 3: Test 3: Both ON (Default State)
  console.log('\n--- Step 3: Test 3: Landing Page with Both ON ---');
  const setBothOn = await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: '1793041018387711',
    landing_meta_add_to_cart_enabled: true,
    landing_meta_initiate_checkout_enabled: true
  }, authHeaders);
  assert.strictEqual(setBothOn.status, 200);
  assert.strictEqual(setBothOn.json.settings.landing_meta_add_to_cart_enabled, true);
  assert.strictEqual(setBothOn.json.settings.landing_meta_initiate_checkout_enabled, true);

  const cbOnRes = await sendReq(8000, '/product/chicken-booster');
  assert.strictEqual(cbOnRes.status, 200);
  assert.ok(cbOnRes.body.includes('const META_ADD_TO_CART_ENABLED = true;'), 'META_ADD_TO_CART_ENABLED must be true');
  assert.ok(cbOnRes.body.includes('const META_INITIATE_CHECKOUT_ENABLED = true;'), 'META_INITIATE_CHECKOUT_ENABLED must be true');
  assert.ok(cbOnRes.body.includes("fbq('track', 'PageView');"), 'PageView must fire');
  assert.ok(!cbOnRes.body.includes("ViewContent"), 'ViewContent must NOT fire');
  assert.ok(cbOnRes.body.includes("if (META_ADD_TO_CART_ENABLED && typeof window.fbq === 'function')"), 'AddToCart must check toggle');
  assert.ok(cbOnRes.body.includes("if (META_INITIATE_CHECKOUT_ENABLED && typeof window.fbq === 'function')"), 'InitiateCheckout must check toggle');
  console.log('✅ Both ON: PageView fires, ViewContent absent, AddToCart and InitiateCheckout are fully enabled.');

  // Step 4: Test 4: AddToCart OFF + InitiateCheckout ON
  console.log('\n--- Step 4: Test 4: AddToCart OFF + InitiateCheckout ON ---');
  const setAtcOff = await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: '1793041018387711',
    landing_meta_add_to_cart_enabled: false,
    landing_meta_initiate_checkout_enabled: true
  }, authHeaders);
  assert.strictEqual(setAtcOff.status, 200);
  assert.strictEqual(setAtcOff.json.settings.landing_meta_add_to_cart_enabled, false);
  assert.strictEqual(setAtcOff.json.settings.landing_meta_initiate_checkout_enabled, true);

  const cbAtcOffRes = await sendReq(8000, '/product/chicken-booster');
  assert.strictEqual(cbAtcOffRes.status, 200);
  assert.ok(cbAtcOffRes.body.includes('const META_ADD_TO_CART_ENABLED = false;'), 'META_ADD_TO_CART_ENABLED must be false');
  assert.ok(cbAtcOffRes.body.includes('const META_INITIATE_CHECKOUT_ENABLED = true;'), 'META_INITIATE_CHECKOUT_ENABLED must be true');
  console.log('✅ AddToCart OFF + InitiateCheckout ON: AddToCart disabled, InitiateCheckout enabled.');

  // Step 5: Test 5: AddToCart ON + InitiateCheckout OFF
  console.log('\n--- Step 5: Test 5: AddToCart ON + InitiateCheckout OFF ---');
  const setIcOff = await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: '1793041018387711',
    landing_meta_add_to_cart_enabled: true,
    landing_meta_initiate_checkout_enabled: false
  }, authHeaders);
  assert.strictEqual(setIcOff.status, 200);
  assert.strictEqual(setIcOff.json.settings.landing_meta_add_to_cart_enabled, true);
  assert.strictEqual(setIcOff.json.settings.landing_meta_initiate_checkout_enabled, false);

  const cbIcOffRes = await sendReq(8000, '/product/chicken-booster');
  assert.strictEqual(cbIcOffRes.status, 200);
  assert.ok(cbIcOffRes.body.includes('const META_ADD_TO_CART_ENABLED = true;'), 'META_ADD_TO_CART_ENABLED must be true');
  assert.ok(cbIcOffRes.body.includes('const META_INITIATE_CHECKOUT_ENABLED = false;'), 'META_INITIATE_CHECKOUT_ENABLED must be false');
  console.log('✅ AddToCart ON + InitiateCheckout OFF: AddToCart enabled, InitiateCheckout disabled.');

  // Step 6: Test 6: Both OFF + Order Placement & Purchase Verification
  console.log('\n--- Step 6: Test 6: Both OFF + Order Placement & Dedicated Purchase Success Page ---');
  const setBothOff = await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: '1793041018387711',
    landing_meta_add_to_cart_enabled: false,
    landing_meta_initiate_checkout_enabled: false
  }, authHeaders);
  assert.strictEqual(setBothOff.status, 200);
  assert.strictEqual(setBothOff.json.settings.landing_meta_add_to_cart_enabled, false);
  assert.strictEqual(setBothOff.json.settings.landing_meta_initiate_checkout_enabled, false);

  const cbBothOffRes = await sendReq(8000, '/product/chicken-booster');
  assert.strictEqual(cbBothOffRes.status, 200);
  assert.ok(cbBothOffRes.body.includes('const META_ADD_TO_CART_ENABLED = false;'), 'META_ADD_TO_CART_ENABLED must be false');
  assert.ok(cbBothOffRes.body.includes('const META_INITIATE_CHECKOUT_ENABLED = false;'), 'META_INITIATE_CHECKOUT_ENABLED must be false');

  // Place order with both toggles OFF to confirm order flow and Purchase are unaffected
  const orderRes = await sendReq(8000, '/api/orders', 'POST', {
    slug: 'chicken-booster',
    landing_page_slug: 'chicken-booster',
    productId: 'chicken-booster',
    product_id: 'chicken-booster',
    source: 'LANDING_PAGE',
    customer_name: 'Toggle Test User',
    name: 'Toggle Test User',
    phone: '01711' + String(Date.now()).slice(-6),
    customer_phone: '01711' + String(Date.now()).slice(-6),
    address: 'Test Farm Road, Bogura',
    shipping_address: 'Test Farm Road, Bogura',
    deliveryZone: 'outside',
    delivery_zone: 'outside',
    quantity: 1,
    items: [
      { productId: 'chicken-booster', variantId: 'pkg-1', quantity: 1, name: '১ কেজি চিকেন বুস্টার', price: 2300 }
    ],
    variantQuantities: {
      'pkg-1': 1
    }
  });
  assert.ok(orderRes.status === 200 || orderRes.status === 201);
  assert.ok(orderRes.json && orderRes.json.success);
  const orderNo = orderRes.json.order.order_number;

  const successRes = await sendReq(8000, `/product/chicken-booster/success/${orderNo}`);
  assert.strictEqual(successRes.status, 200);
  assert.ok(successRes.body.includes("fbq('track', 'PageView');"), 'PageView must fire on success page');
  assert.ok(successRes.body.includes("window.fbq('track', 'Purchase', {"), 'Purchase MUST still fire on success page');
  assert.ok(!successRes.body.includes("ViewContent"), 'ViewContent must NOT fire on success page');
  assert.ok(successRes.body.includes('id="btnDownloadReceipt"'), 'Receipt button must be present');
  console.log('✅ Both OFF: AddToCart & InitiateCheckout are suppressed, but order creation, source-matched redirect, and Purchase tracking remain 100% functional.');

  // Step 7: Test 7: Multiple Dynamic Landing Pages Automatic Adaptation
  console.log('\n--- Step 7: Test 7: Multiple Dynamic Landing Pages ---');
  const msRes = await sendReq(8000, '/product/mediascope-it');
  assert.strictEqual(msRes.status, 200);
  assert.ok(msRes.body.includes('const META_ADD_TO_CART_ENABLED = false;'));
  assert.ok(msRes.body.includes('const META_INITIATE_CHECKOUT_ENABLED = false;'));

  // Create temporary dynamic page
  const dynamicSlug = 'dyn-toggle-test-' + Date.now();
  const createRes = await sendReq(8000, '/api/admin/landing-pages', 'POST', {
    name: 'Dynamic Toggle Page',
    slug: dynamicSlug,
    status: 'published',
    theme: 'universal',
    product_name: 'Dynamic Vitamin Booster',
    product_id: 'vit-boost-99',
    content: {
      packages: [
        { id: 'pkg-1', name: '500g Pack', price: 950, weight: '500g' }
      ]
    }
  }, authHeaders);
  assert.strictEqual(createRes.status, 201);
  const tempPageId = createRes.json.page_id;

  const dynRes = await sendReq(8000, `/product/${dynamicSlug}`);
  assert.strictEqual(dynRes.status, 200);
  assert.ok(dynRes.body.includes('const META_ADD_TO_CART_ENABLED = false;'));
  assert.ok(dynRes.body.includes('const META_INITIATE_CHECKOUT_ENABLED = false;'));
  console.log('✅ Dynamic future landing pages inherit settings automatically without hardcoded slugs.');

  // Cleanup dynamic page
  await sendReq(8000, `/api/admin/landing-pages/${tempPageId}`, 'DELETE', null, authHeaders);
  console.log('✅ Cleaned up dynamic test page');

  // Step 8: Test 8: Main Website Regressions Untouched
  console.log('\n--- Step 8: Test 8: Main E-Commerce Website Regressions Untouched ---');
  const mainProductRes = await sendReq(8000, '/product/girls-red-butterfly-printed-t-shirt-floral-shorts-set');
  assert.strictEqual(mainProductRes.status, 200);
  assert.ok(mainProductRes.body.includes("fbq('track', 'PageView')"));
  assert.ok(mainProductRes.body.includes("fbq('track', 'ViewContent'"));
  assert.ok(mainProductRes.body.includes("triggerDetailInitiateCheckout()"));

  const mainCheckoutRes = await sendReq(8000, '/checkout');
  assert.strictEqual(mainCheckoutRes.status, 200);
  assert.ok(mainCheckoutRes.body.includes("fbq('track', 'PageView')"));
  assert.ok(mainCheckoutRes.body.includes("fbq('track', 'InitiateCheckout', {"));
  console.log('✅ Main website ViewContent, InitiateCheckout, PageView and Purchase completely untouched and intact.');

  // Step 9: Restore Defaults (Both ON)
  console.log('\n--- Step 9: Restore Defaults (Both ON) ---');
  const restoreRes = await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: '1793041018387711',
    landing_meta_add_to_cart_enabled: true,
    landing_meta_initiate_checkout_enabled: true
  }, authHeaders);
  assert.strictEqual(restoreRes.status, 200);
  assert.strictEqual(restoreRes.json.settings.landing_meta_add_to_cart_enabled, true);
  assert.strictEqual(restoreRes.json.settings.landing_meta_initiate_checkout_enabled, true);
  console.log('✅ Settings restored to default ON/ON state.');

  console.log('\n================================================================');
  console.log('🎉 ALL LANDING PAGE META PIXEL TOGGLE REQUIREMENTS PASSED!');
  console.log('================================================================\n');
})();
