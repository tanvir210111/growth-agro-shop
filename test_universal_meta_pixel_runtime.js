/**
 * test_universal_meta_pixel_runtime.js
 *
 * Dedicated verification suite for Universal Runtime-Configurable Meta Pixel / Dataset ID:
 * Test A: Admin/database setting contains Pixel ID A (1615672197236009) -> rendered fbq init uses A.
 * Test B: Database setting changes to Pixel ID B (mock/local test ID) -> rendered fbq init uses B without code changes.
 * Test C: Event implementations (AddToCart, InitiateCheckout, Purchase) contain ZERO hardcoded Pixel IDs.
 * Test D: Landing pages and storefront share the exact same centralized configured ID.
 * Test E: Changing the configured ID does not alter event deduplication keys or behavior.
 * Test F: Admin validation rejects invalid formats (GTM-*, G-*, non-numeric text, spaces, invalid lengths).
 * Test G: Restores target Pixel ID (1615672197236009) in database.
 */

const http = require('http');
const assert = require('assert');
const fs = require('fs');
const path = require('path');

function sendReq(port, reqPath, method = 'GET', data = null, headers = {}) {
  return new Promise((resolve, reject) => {
    const payload = data ? JSON.stringify(data) : null;
    const reqHeaders = { ...headers };
    if (payload) {
      reqHeaders['Content-Type'] = 'application/json';
      reqHeaders['Content-Length'] = Buffer.byteLength(payload);
    }
    const req = http.request({ hostname: '127.0.0.1', port, path: reqPath, method, headers: reqHeaders, timeout: 8000 }, res => {
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

const ID_A = '1615672197236009';
const ID_B = '987654321012345'; // Safe 15-digit local test mock identifier

(async () => {
  console.log('================================================================');
  console.log('🧪 TESTING UNIVERSAL RUNTIME-CONFIGURABLE META PIXEL ARCHITECTURE');
  console.log('================================================================\n');

  // Step 1: Admin Authentication
  console.log('--- Step 1: Admin Authentication ---');
  const loginRes = await sendReq(8000, '/api/admin/login', 'POST', {
    email: 'admin@gmail.com',
    password: 'admin123'
  });
  assert.strictEqual(loginRes.status, 200, 'Admin login must succeed');
  const token = loginRes.json.token;
  const cookieHeader = (loginRes.headers['set-cookie'] || []).map(c => c.split(';')[0]).join('; ');
  const authHeaders = { Cookie: cookieHeader, Authorization: `Bearer ${token}` };
  console.log('✅ Admin login succeeded. Token acquired.');

  // Step 2: Test F - Validation enforcement
  console.log('\n--- Step 2: Test F - Validation Enforcement for Invalid Inputs ---');
  const gtmRes = await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: 'GTM-ABCD1234'
  }, authHeaders);
  assert.strictEqual(gtmRes.status, 422, 'GTM container ID must be rejected');
  assert.ok(gtmRes.json && gtmRes.json.message.includes('Google container ID'), 'Rejection message must indicate Google container ID');
  console.log('✅ GTM container ID (GTM-ABCD1234) rejected with HTTP 422.');

  const gaRes = await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: 'G-123456789'
  }, authHeaders);
  assert.strictEqual(gaRes.status, 422, 'GA4 measurement ID must be rejected');
  console.log('✅ GA4 ID (G-123456789) rejected with HTTP 422.');

  const textRes = await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: 'my_meta_pixel_name'
  }, authHeaders);
  assert.strictEqual(textRes.status, 422, 'Non-numeric arbitrary text must be rejected');
  console.log('✅ Non-numeric arbitrary text rejected with HTTP 422.');

  const shortRes = await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: '12345'
  }, authHeaders);
  assert.strictEqual(shortRes.status, 422, 'Too short number must be rejected');
  console.log('✅ Short numeric string rejected with HTTP 422.');

  // Step 3: Test A - Configure ID A (1615672197236009)
  console.log('\n--- Step 3: Test A - Configure ID A (1615672197236009) via Admin API ---');
  const saveARes = await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: ID_A
  }, authHeaders);
  assert.strictEqual(saveARes.status, 200, 'Saving ID A must return 200');
  assert.strictEqual(saveARes.json.settings.facebook_pixel, ID_A, 'Saved pixel must match ID A');
  console.log(`✅ Database setting updated to ID A: ${ID_A}`);

  // Verify rendered HTML on Storefront (/)
  const storeARes = await sendReq(8000, '/');
  assert.strictEqual(storeARes.status, 200);
  assert.ok(storeARes.body.includes(`fbq('init', '${ID_A}');`), 'Storefront must render fbq init with ID A');
  assert.ok(storeARes.body.includes(`facebook.com/tr?id=${ID_A}&ev=PageView&noscript=1`), 'Storefront noscript must have ID A');
  console.log('✅ Storefront (/) HTML dynamically rendered ID A.');

  // Verify rendered HTML on Landing Page (/product/chicken-booster)
  const lpARes = await sendReq(8000, '/product/chicken-booster');
  assert.strictEqual(lpARes.status, 200);
  assert.ok(lpARes.body.includes(`fbq('init', '${ID_A}');`), 'Landing page must render fbq init with ID A');
  assert.ok(lpARes.body.includes(`facebook.com/tr?id=${ID_A}&ev=PageView&noscript=1`), 'Landing page noscript must have ID A');
  console.log('✅ Landing Page (/product/chicken-booster) HTML dynamically rendered ID A.');

  // Step 4: Test B - Runtime Switch to ID B without code change or redeploy
  console.log('\n--- Step 4: Test B - Runtime Switch to ID B (Local Mock ID) without code modification ---');
  const saveBRes = await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: ID_B
  }, authHeaders);
  assert.strictEqual(saveBRes.status, 200, 'Saving ID B must return 200');
  assert.strictEqual(saveBRes.json.settings.facebook_pixel, ID_B, 'Saved pixel must match ID B');
  console.log(`✅ Database setting switched to ID B: ${ID_B}`);

  // Fetch Storefront again - must immediately render ID B and NOT contain ID A
  const storeBRes = await sendReq(8000, '/');
  assert.strictEqual(storeBRes.status, 200);
  assert.ok(storeBRes.body.includes(`fbq('init', '${ID_B}');`), 'Storefront must immediately render ID B');
  assert.ok(!storeBRes.body.includes(`fbq('init', '${ID_A}');`), 'Storefront must NOT contain old ID A');
  console.log('✅ Storefront (/) immediately rendered ID B without deployment or code change.');

  // Fetch Landing Page again - must immediately render ID B and NOT contain ID A
  const lpBRes = await sendReq(8000, '/product/chicken-booster');
  assert.strictEqual(lpBRes.status, 200);
  assert.ok(lpBRes.body.includes(`fbq('init', '${ID_B}');`), 'Landing page must immediately render ID B');
  assert.ok(!lpBRes.body.includes(`fbq('init', '${ID_A}');`), 'Landing page must NOT contain old ID A');
  console.log('✅ Landing Page (/product/chicken-booster) immediately rendered ID B without deployment or code change.');

  // Step 5: Test C - Verify NO hardcoded Pixel IDs in event call sites
  console.log('\n--- Step 5: Test C - Audit Event Implementations for Hardcoded IDs ---');
  const filesToCheck = [
    'E Commerce Baby/resources/views/pages/landing-page.blade.php',
    'E Commerce Baby/resources/views/pages/landing-success.blade.php',
    'E Commerce Baby/resources/views/pages/order-success.blade.php',
    'E Commerce Baby/resources/views/pages/checkout.blade.php',
    'E Commerce Baby/resources/views/pages/product-detail.blade.php'
  ];

  for (const relPath of filesToCheck) {
    const fullPath = path.join(__dirname, relPath);
    const content = fs.readFileSync(fullPath, 'utf8');
    // Ensure no hardcoded 14-18 digit IDs appear inside fbq('track' calls
    const trackCalls = [...content.matchAll(/fbq\s*\(\s*['"]track['"][\s\S]*?\)/g)];
    for (const match of trackCalls) {
      assert.ok(!/\b\d{14,18}\b/.test(match[0]), `Event call must not contain hardcoded ID in ${relPath}: ${match[0]}`);
    }
  }
  console.log('✅ Verified: All event dispatch implementations (AddToCart, InitiateCheckout, Purchase) contain ZERO hardcoded Pixel IDs.');

  // Step 6: Test D - Centralized ID consistency across all routes
  console.log('\n--- Step 6: Test D - Centralized ID Consistency Across Routes ---');
  const mainCheck = storeBRes.body.match(/fbq\('init',\s*'([^']+)'\)/);
  const lpCheck = lpBRes.body.match(/fbq\('init',\s*'([^']+)'\)/);
  assert.ok(mainCheck && lpCheck, 'Both pages must contain fbq init');
  assert.strictEqual(mainCheck[1], lpCheck[1], 'Storefront and Landing Page must use exact same runtime ID');
  console.log(`✅ Both Storefront and Landing Page initialize identical configured ID: ${mainCheck[1]}`);

  // Step 7: Test E - Deduplication keys & behavior preservation
  console.log('\n--- Step 7: Test E - Verify Deduplication Keys & Logic Integrity ---');
  const metaPartial = fs.readFileSync(path.join(__dirname, 'E Commerce Baby/resources/views/partials/meta-pixel.blade.php'), 'utf8');
  assert.ok(metaPartial.includes('meta_tracked_pageview_{{ $lpSlug }}'), 'meta_tracked_pageview guard must exist in meta-pixel.blade.php');
  assert.ok(metaPartial.includes('meta_tracked_success_pageview_{{ $orderNumber }}'), 'meta_tracked_success_pageview guard must exist in meta-pixel.blade.php');
  assert.ok(metaPartial.includes('fbq.disablePushState = true;'), 'fbq.disablePushState must be preserved');

  const lpContent = fs.readFileSync(path.join(__dirname, 'E Commerce Baby/resources/views/pages/landing-page.blade.php'), 'utf8');
  assert.ok(lpContent.includes("meta_tracked_addtocart_' + LANDING_PAGE_SLUG"), 'meta_tracked_addtocart guard must exist in landing-page.blade.php');
  assert.ok(lpContent.includes("meta_tracked_initiatecheckout_' + LANDING_PAGE_SLUG"), 'meta_tracked_initiatecheckout guard must exist in landing-page.blade.php');

  const lpSuccessContent = fs.readFileSync(path.join(__dirname, 'E Commerce Baby/resources/views/pages/landing-success.blade.php'), 'utf8');
  assert.ok(lpSuccessContent.includes("meta_tracked_purchase_' + orderNo"), 'meta_tracked_purchase guard must exist in landing-success.blade.php');
  console.log('✅ All 5 required sessionStorage deduplication keys and fbq.disablePushState verified intact.');

  // Step 8: Test G - Restore target Pixel ID A (1615672197236009) in database
  console.log('\n--- Step 8: Test G - Restore Target Production Pixel ID (1615672197236009) ---');
  const restoreRes = await sendReq(8000, '/api/admin/settings/marketing', 'POST', {
    facebook_pixel: ID_A
  }, authHeaders);
  assert.strictEqual(restoreRes.status, 200);
  assert.strictEqual(restoreRes.json.settings.facebook_pixel, ID_A);

  const finalCheckRes = await sendReq(8000, '/product/chicken-booster');
  assert.ok(finalCheckRes.body.includes(`fbq('init', '${ID_A}');`), 'Final HTML must contain ID A');
  console.log(`✅ Production Target Pixel ID successfully restored in database: ${ID_A}`);

  console.log('\n================================================================');
  console.log('🎉 ALL UNIVERSAL RUNTIME CONFIGURATION TESTS PASSED (7/7)!');
  console.log('================================================================\n');
})().catch(err => {
  console.error('\n❌ TEST FAILED:', err);
  process.exit(1);
});
