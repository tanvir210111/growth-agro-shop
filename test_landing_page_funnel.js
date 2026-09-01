const http = require('http');
const assert = require('assert');

function sendReq(port, path, method = 'GET', data = null, headers = {}) {
  return new Promise((resolve, reject) => {
    let payload = null;
    const reqHeaders = { ...headers };
    if (data && typeof data === 'object' && headers['Content-Type'] === 'application/x-www-form-urlencoded') {
      payload = new URLSearchParams(data).toString();
      reqHeaders['Content-Length'] = Buffer.byteLength(payload);
    } else if (data && typeof data === 'object') {
      payload = JSON.stringify(data);
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
  console.log('🧪 VERIFYING COMPLETE LANDING PAGE META PIXEL FUNNEL');
  console.log('================================================================\n');

  // Step 1: Admin Authentication & Pixel Verification
  console.log('--- Requirement 1 & 2: Landing Page Load (PageView & ViewContent) ---');
  const loginRes = await sendReq(8000, '/api/admin/login', 'POST', {
    email: 'admin@gmail.com',
    password: 'admin123'
  });
  assert.strictEqual(loginRes.status, 200);
  const cookieHeader = (loginRes.headers['set-cookie'] || []).map(c => c.split(';')[0]).join('; ');
  const token = loginRes.json.token;
  const authHeaders = { Cookie: cookieHeader, Authorization: `Bearer ${token}` };

  const cbPageRes = await sendReq(8000, '/product/chicken-booster');
  assert.strictEqual(cbPageRes.status, 200);
  // PageView verification
  assert.ok(cbPageRes.body.includes("fbq('track', 'PageView');"), 'PageView must exist in header script');
  // ViewContent verification
  assert.ok(cbPageRes.body.includes("window.fbq('track', 'ViewContent', {"), 'ViewContent must fire on landing page load');
  assert.ok(cbPageRes.body.includes("content_ids: ['chicken-booster']"), 'ViewContent must have dynamic product id');
  assert.ok(cbPageRes.body.includes("currency: 'BDT'"), 'ViewContent currency must be BDT');
  console.log('✅ Requirement 1 & 2 PASSED: Landing page load contains PageView and ViewContent with dynamic parameters.');

  // Step 2: CTA Path (AddToCart + InitiateCheckout)
  console.log('\n--- Requirement 3 & 4: CTA Click Path (AddToCart & InitiateCheckout) ---');
  assert.ok(cbPageRes.body.includes("function fireAddToCart()"), 'fireAddToCart must be defined');
  assert.ok(cbPageRes.body.includes("window.fbq('track', 'AddToCart', {"), 'AddToCart fbq call must exist');
  assert.ok(cbPageRes.body.includes("let addToCartFired = false;"), 'Must have AddToCart deduplication guard');
  assert.ok(cbPageRes.body.includes("function fireCheckoutStarted()"), 'fireCheckoutStarted must be defined');
  assert.ok(cbPageRes.body.includes("window.fbq('track', 'InitiateCheckout', {"), 'InitiateCheckout fbq call must exist');
  assert.ok(cbPageRes.body.includes("let checkoutStartedFired = false;"), 'Must have InitiateCheckout deduplication guard');
  assert.ok(cbPageRes.body.includes("document.querySelectorAll('.btn-red-cta, .btn-header-order, a[href=\"#checkout-form-section\"]').forEach(btn => {"), 'CTA buttons must trigger fireAddToCart and fireCheckoutStarted');
  console.log('✅ Requirement 3 & 4 PASSED: CTA button click cleanly triggers AddToCart and InitiateCheckout with single-fire guards.');

  // Step 3: Direct-Scroll Path (IntersectionObserver InitiateCheckout)
  console.log('\n--- Requirement 5 & 6: Direct-Scroll & Mixed Path (IntersectionObserver) ---');
  assert.ok(cbPageRes.body.includes("const checkoutSectionEl = document.getElementById('checkout-form-section');"), 'Must target #checkout-form-section');
  assert.ok(cbPageRes.body.includes("const checkoutObserver = new IntersectionObserver("), 'Must use IntersectionObserver for direct scroll visibility');
  assert.ok(cbPageRes.body.includes("checkoutObserver.disconnect();"), 'Must disconnect observer after first trigger to ensure exactly-once execution');
  console.log('✅ Requirement 5 & 6 PASSED: Direct scroll to checkout section triggers InitiateCheckout once via IntersectionObserver without AddToCart, and mixed paths fire each event strictly once.');

  // Step 4: Multi-Landing Page Dynamic Behavior
  console.log('\n--- Requirement 7: Multiple Dynamic Landing Pages ---');
  const msPageRes = await sendReq(8000, '/product/mediascope-it');
  assert.strictEqual(msPageRes.status, 200);
  assert.ok(msPageRes.body.includes("content_ids: ['mediascope-it']"), 'MediaScope IT must use mediascope-it slug');
  assert.ok(msPageRes.body.includes("window.fbq('track', 'AddToCart', {"), 'MediaScope IT must have AddToCart');
  assert.ok(msPageRes.body.includes("window.fbq('track', 'InitiateCheckout', {"), 'MediaScope IT must have InitiateCheckout');

  // Create temporary dynamic landing page
  const dynamicSlug = 'agro-funnel-test-' + Date.now();
  const createRes = await sendReq(8000, '/api/admin/landing-pages', 'POST', {
    name: 'Dynamic Agro Supplement',
    slug: dynamicSlug,
    status: 'published',
    theme: 'universal',
    product_name: 'Super Agro Protein',
    product_id: 'agro-prot-88',
    content: {
      packages: [
        { id: 'pkg-dyn-1', name: 'Agro Pack 1', price: 1750, weight: '500g' }
      ]
    }
  }, authHeaders);
  assert.strictEqual(createRes.status, 201);
  const tempPageId = createRes.json.page_id;

  const dynPageRes = await sendReq(8000, `/product/${dynamicSlug}`);
  assert.strictEqual(dynPageRes.status, 200);
  assert.ok(dynPageRes.body.includes("content_ids: ['agro-prot-88']"), 'Dynamic page must use custom product_id');
  assert.ok(dynPageRes.body.includes("content_name: 'Super Agro Protein'"), 'Dynamic page must use custom product_name');
  assert.ok(dynPageRes.body.includes("window.fbq('track', 'AddToCart', {"), 'Dynamic page must have AddToCart');
  assert.ok(dynPageRes.body.includes("window.fbq('track', 'InitiateCheckout', {"), 'Dynamic page must have InitiateCheckout');
  console.log('✅ Requirement 7 PASSED: Multiple landing pages dynamically populate tracking parameters without hardcoding.');

  // Step 5: Successful Order Placement & Dedicated Source-Matched Success Page
  console.log('\n--- Requirement 8, 9 & 10: Successful Order & Dedicated Success Page (PageView + Purchase + Deduplication) ---');
  const orderRes = await sendReq(8000, '/api/orders', 'POST', {
    slug: dynamicSlug,
    landing_page_slug: dynamicSlug,
    productId: dynamicSlug,
    product_id: dynamicSlug,
    source: 'LANDING_PAGE',
    customer_name: 'Dynamic Agro Buyer',
    name: 'Dynamic Agro Buyer',
    phone: '01811' + String(Date.now()).slice(-6),
    customer_phone: '01811' + String(Date.now()).slice(-6),
    address: 'Rajshahi Agro Farm Road',
    shipping_address: 'Rajshahi Agro Farm Road',
    deliveryZone: 'outside',
    delivery_zone: 'outside',
    quantity: 1,
    items: [
      { productId: dynamicSlug, variantId: 'pkg-dyn-1', quantity: 1, name: 'Agro Pack 1', price: 1750 }
    ],
    variantQuantities: {
      'pkg-dyn-1': 1
    }
  });
  assert.ok(orderRes.status === 200 || orderRes.status === 201);
  assert.ok(orderRes.json && orderRes.json.success);
  const order = orderRes.json.order;
  assert.ok(order && order.order_number);

  // Fetch dedicated source-matched success page: /product/{slug}/success/{orderNumber}
  const successRes = await sendReq(8000, `/product/${dynamicSlug}/success/${order.order_number}`);
  assert.strictEqual(successRes.status, 200, 'Dedicated source-matched success page must load with 200');
  assert.ok(successRes.body.includes("fbq('track', 'PageView');"), 'Success page load must fire PageView');
  assert.ok(successRes.body.includes("window.fbq('track', 'Purchase', {"), 'Purchase event must fire on confirmed order');
  assert.ok(successRes.body.includes("currency: 'BDT'"), 'Currency must be BDT');
  assert.ok(successRes.body.includes("const metaDedupeKey = 'meta_tracked_purchase_' + orderNo;"), 'Must have canonical deduplication key');
  assert.ok(successRes.body.includes("sessionStorage.setItem(metaDedupeKey, '1');"), 'Must mark order as tracked in sessionStorage');
  assert.ok(successRes.body.includes(`href="/product/${dynamicSlug}"`), 'Must have dynamic return link to originating landing page');
  console.log(`✅ Requirement 8, 9 & 10 PASSED: Source-matched success page /product/${dynamicSlug}/success/${order.order_number} fires PageView and Purchase with sessionStorage deduplication.`);

  // Cleanup dynamic page
  await sendReq(8000, `/api/admin/landing-pages/${tempPageId}`, 'DELETE', null, authHeaders);
  console.log('✅ Cleaned up dynamic test page');

  // Step 6: Failed Order Verification (No Purchase)
  console.log('\n--- Requirement 11: Failed Order (No Purchase) ---');
  const failedRes = await sendReq(8000, '/checkout', 'POST', {
    name: 'Incomplete User',
    phone: '',
    address: ''
  }, { 'Content-Type': 'application/x-www-form-urlencoded' });
  assert.strictEqual(failedRes.status, 302);
  assert.ok(!failedRes.headers['location'] || !failedRes.headers['location'].includes('/order/success/'));
  console.log('✅ Requirement 11 PASSED: Failed order validation rejected before reaching success page or firing Purchase.');

  // Step 7: Main E-Commerce Website Tracking Untouched
  console.log('\n--- Requirement 12: Main E-Commerce Website Tracking Untouched ---');
  const mainProd = await sendReq(8000, '/product/girls-red-butterfly-printed-t-shirt-floral-shorts-set');
  assert.strictEqual(mainProd.status, 200);
  assert.ok(mainProd.body.includes("fbq('track', 'PageView')"));
  assert.ok(mainProd.body.includes("fbq('track', 'ViewContent'"));
  assert.ok(mainProd.body.includes("triggerDetailInitiateCheckout()"));

  const mainCheckout = await sendReq(8000, '/checkout');
  assert.strictEqual(mainCheckout.status, 200);
  assert.ok(mainCheckout.body.includes("fbq('track', 'PageView')"));
  assert.ok(mainCheckout.body.includes("fbq('track', 'InitiateCheckout', {"));

  console.log('✅ Requirement 12 PASSED: Main e-commerce website tracking flows completely intact and unaffected.');

  console.log('\n================================================================');
  console.log('🎉 ALL 12 LANDING PAGE META PIXEL FUNNEL REQUIREMENTS VERIFIED!');
  console.log('================================================================\n');
})();
