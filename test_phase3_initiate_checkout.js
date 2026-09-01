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
  console.log('🧪 TESTING PHASE 3 — META PIXEL INITIATECHECKOUT EVENT');
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

  // Test 2: Main Website Product Page Tracking Points
  console.log('\n--- Test 2: Main Website Product Detail Page InitiateCheckout Integration ---');
  const mainProductRes = await sendReq(8000, '/product/girls-red-butterfly-printed-t-shirt-floral-shorts-set');
  assert.strictEqual(mainProductRes.status, 200);
  assert.ok(mainProductRes.body.includes("fbq('track', 'PageView')"), 'PageView must fire');
  assert.ok(mainProductRes.body.includes("fbq('track', 'ViewContent'"), 'ViewContent must fire on load');
  assert.ok(mainProductRes.body.includes("function triggerDetailInitiateCheckout()"), 'triggerDetailInitiateCheckout must be defined');
  assert.ok(mainProductRes.body.includes("window.fbq('track', 'InitiateCheckout'"), 'InitiateCheckout call must be wired');
  assert.ok(mainProductRes.body.includes("content_ids: ['girls-red-butterfly-printed-t-shirt-floral-shorts-set']"), 'Must use dynamic product slug');
  assert.ok(mainProductRes.body.includes("let detailCheckoutStarted = false;"), 'Must have single execution deduplication guard');
  console.log('✅ Main website product page has InitiateCheckout tied to genuine order actions with deduplication protection.');

  // Test 3: Main Website /checkout Page
  console.log('\n--- Test 3: Main Website /checkout Page InitiateCheckout ---');
  const checkoutRes = await sendReq(8000, '/checkout');
  assert.strictEqual(checkoutRes.status, 200);
  assert.ok(checkoutRes.body.includes("fbq('track', 'PageView')"), 'PageView must fire on /checkout');
  assert.ok(checkoutRes.body.includes("fbq('track', 'InitiateCheckout', {"), 'InitiateCheckout must fire on /checkout');
  assert.ok(checkoutRes.body.includes("currency: 'BDT'"), 'currency must be BDT');
  console.log('✅ Main website /checkout page fires InitiateCheckout dynamically.');

  // Test 4: Chicken Booster Landing Page AddToCart & InitiateCheckout Integration
  console.log('\n--- Test 4: Chicken Booster Landing Page AddToCart & InitiateCheckout Integration ---');
  const cbRes = await sendReq(8000, '/product/chicken-booster');
  assert.strictEqual(cbRes.status, 200);
  assert.ok(cbRes.body.includes("fbq('track', 'PageView')"), 'PageView must fire');
  assert.ok(!cbRes.body.includes("ViewContent"), 'ViewContent must NOT fire on landing page');
  assert.ok(cbRes.body.includes("function fireAddToCart()"), 'fireAddToCart function must exist');
  assert.ok(cbRes.body.includes("window.fbq('track', 'AddToCart', {"), 'AddToCart call must be in fireAddToCart');
  assert.ok(cbRes.body.includes("let addToCartFired = false;"), 'Must have AddToCart deduplication guard');
  assert.ok(cbRes.body.includes("function fireCheckoutStarted()"), 'fireCheckoutStarted function must exist');
  assert.ok(cbRes.body.includes("window.fbq('track', 'InitiateCheckout', {"), 'InitiateCheckout call must be in fireCheckoutStarted');
  assert.ok(cbRes.body.includes("let checkoutStartedFired = false;"), 'Must have InitiateCheckout deduplication guard');
  assert.ok(cbRes.body.includes("IntersectionObserver"), 'Must have direct scroll IntersectionObserver for checkout section');
  assert.ok(cbRes.body.includes("content_ids: ['chicken-booster']"), 'content_ids must be chicken-booster');
  assert.ok(cbRes.body.includes("currency: 'BDT'"), 'currency must be BDT');
  console.log('✅ Chicken Booster landing page has AddToCart on CTA and InitiateCheckout on CTA/Direct-Scroll properly wired with deduplication.');

  // Test 5: MediaScope IT Landing Page AddToCart & InitiateCheckout Integration
  console.log('\n--- Test 5: MediaScope IT Landing Page AddToCart & InitiateCheckout Integration ---');
  const msRes = await sendReq(8000, '/product/mediascope-it');
  assert.strictEqual(msRes.status, 200);
  assert.ok(msRes.body.includes("fbq('track', 'PageView')"), 'PageView must fire');
  assert.ok(!msRes.body.includes("ViewContent"), 'ViewContent must NOT fire on landing page');
  assert.ok(msRes.body.includes("window.fbq('track', 'AddToCart', {"), 'AddToCart must be present');
  assert.ok(msRes.body.includes("window.fbq('track', 'InitiateCheckout', {"), 'InitiateCheckout must be present');
  console.log('✅ MediaScope IT landing page has AddToCart and InitiateCheckout properly wired.');

  // Test 6: Dynamic Future Landing Page InitiateCheckout
  console.log('\n--- Test 6: Dynamic Future Landing Page InitiateCheckout ---');
  const testSlug = 'dynamic-ic-test-' + Date.now();
  const createRes = await sendReq(8000, '/api/admin/landing-pages', 'POST', {
    name: 'Dynamic InitiateCheckout Page',
    slug: testSlug,
    status: 'published',
    theme: 'universal',
    product_name: 'Dynamic IC Product',
    product_id: 'prod-ic-777',
    content: {
      packages: [
        { id: 'pkg-ic-1', name: 'Pack 1', price: 1450, weight: '100g' }
      ]
    }
  }, authHeaders);
  assert.strictEqual(createRes.status, 201);
  const tempPageId = createRes.json.page_id;

  const dynamicPageRes = await sendReq(8000, `/product/${testSlug}`);
  assert.strictEqual(dynamicPageRes.status, 200);
  assert.ok(dynamicPageRes.body.includes("content_ids: ['prod-ic-777']"), 'Dynamic page must use custom product_id');
  assert.ok(dynamicPageRes.body.includes("content_name: 'Dynamic IC Product'"), 'Dynamic page must use custom product_name');
  assert.ok(dynamicPageRes.body.includes("window.fbq('track', 'InitiateCheckout', {"), 'InitiateCheckout must be wired dynamically');
  console.log('✅ Dynamic future landing page automatically configures InitiateCheckout without hardcoded slugs.');

  // Cleanup dynamic test page
  await sendReq(8000, `/api/admin/landing-pages/${tempPageId}`, 'DELETE', null, authHeaders);
  console.log('✅ Cleaned up dynamic test page');

  // Test 7: Verify Existing Order Flow
  console.log('\n--- Test 7: Verify Order Placement & API Health ---');
  const orderRes = await sendReq(8000, '/api/orders', 'POST', {
    slug: 'chicken-booster',
    landing_page_slug: 'chicken-booster',
    source: 'LANDING_PAGE',
    customer_name: 'Test Customer IC',
    name: 'Test Customer IC',
    phone: '01711' + String(Date.now()).slice(-6),
    customer_phone: '01711' + String(Date.now()).slice(-6),
    address: 'Dhaka Test Road, Banani',
    shipping_address: 'Dhaka Test Road, Banani',
    deliveryZone: 'inside',
    delivery_zone: 'inside',
    variantQuantities: {
      'cb-1kg': 1
    }
  });
  assert.ok(orderRes.status === 200 || orderRes.status === 201, `Order status must be 200/201 (got ${orderRes.status})`);
  console.log('✅ Order placement API works smoothly without any regressions.');

  console.log('\n================================================================');
  console.log('🎉 ALL PHASE 3 INITIATECHECKOUT INTEGRATION TESTS PASSED SUCCESSFULLY!');
  console.log('================================================================\n');
})();
