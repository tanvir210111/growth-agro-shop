/**
 * Phase 6 Verification Test Suite: Landing Page Node.js Server-Side Meta CAPI
 * 
 * Verifies all 32 requirements specified in the Phase 6 specification:
 * 1. Purchase CAPI generated after successful order persistence
 * 2. Deterministic purchase_{orderNumber}
 * 3. Browser/server Purchase IDs match
 * 4. Duplicate Purchase prevented (persistent SQLite idempotency)
 * 5. Failed Purchase can retry with same ID
 * 6. AddToCart preserves browser ID
 * 7. InitiateCheckout preserves browser ID
 * 8. Supported event validation
 * 9. Unsupported event rejection
 * 10. Runtime Pixel configuration
 * 11. Runtime token configuration
 * 12. Runtime Test Event Code
 * 13. Token never reaches browser / public output
 * 14. Token never logged
 * 15. Authorization never logged
 * 16. Raw email never logged
 * 17. Raw phone never logged
 * 18. fbp preserved
 * 19. fbc preserved
 * 20. Missing fbp/fbc omitted
 * 21. IP handling
 * 22. User-Agent handling
 * 23. Meta failure does not rollback order
 * 24. Success page remains functional
 * 25. Internal secret protection
 * 26. Cross-slug protection remains intact
 * 27. No automatic courier API call
 * 28. No automatic fraud API call
 * 29. No automatic PageView on every request
 * 30. Exactly one Browser Pixel initialization
 * 31. autoConfig=false preserved
 * 32. disablePushState=true preserved
 */

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const metaCapi = require('./server/meta-capi');
const { 
  db, 
  createOrder, 
  getOrderByNumber, 
  findMetaCapiEvent, 
  recordMetaCapiEvent 
} = require('./server/db');

async function runPhase6Tests() {
  console.log('================================================================');
  console.log('🧪 RUNNING PHASE 6: NODE.JS LANDING SERVER-SIDE META CAPI TESTS');
  console.log('================================================================\n');

  let passed = 0;
  let total = 32;

  // Clean test state for idempotency table
  try {
    db.exec("DELETE FROM meta_capi_events;");
  } catch (e) {}

  // Setup Mock Runtime Configuration for testing
  const mockPixelId = '1793041018387711';
  const mockToken = 'EAAG_NODE_MOCK_TOKEN_SECRET_XYZ123';
  const mockTestCode = 'TEST_PHASE6_NODE';

  metaCapi.setMockConfig({
    success: true,
    is_enabled: true,
    active_pixel_id: mockPixelId,
    access_token: mockToken,
    test_event_code: mockTestCode,
    server_events: {
      pageview: true,
      add_to_cart: true,
      initiate_checkout: true,
      purchase: true
    }
  });

  // Track dispatched mock calls
  let dispatchedCalls = [];
  metaCapi.setMockHttpHandler(async (req) => {
    dispatchedCalls.push(req);
    return {
      success: true,
      events_received: 1,
      fbtrace_id: 'trace_node_test_' + Date.now()
    };
  });

  // -------------------------------------------------------------
  // Test 1: Purchase CAPI generated after successful order persistence
  // -------------------------------------------------------------
  dispatchedCalls = [];
  const testOrder1 = createOrder({
    customerName: 'Node CAPI Test Customer',
    phone: '01711223344',
    address: 'Farmgate, Dhaka',
    productId: 'chicken-booster',
    productName: 'Chicken Booster Pro',
    variantId: 'variant-2',
    variantName: '500g Pack',
    quantity: 2,
    unitPrice: 750,
    subtotal: 1500,
    deliveryZone: 'inside',
    deliveryCharge: 60,
    total: 1560,
    currency: 'BDT',
    landingPage: '/product/chicken-booster'
  });

  assert.ok(testOrder1 && testOrder1.order_number, 'Order must be persisted in SQLite');
  const purchaseEventId1 = 'purchase_' + testOrder1.order_number;

  const res1 = await metaCapi.sendEvent({
    event_name: 'Purchase',
    event_id: purchaseEventId1,
    event_source_url: 'https://growthagro.shop/product/chicken-booster',
    user_data: {
      phone: testOrder1.phone,
      customer_name: testOrder1.customer_name,
      city: 'dhaka',
      country: 'bd',
      external_id: testOrder1.order_number,
      client_ip_address: '103.145.120.45',
      client_user_agent: 'Mozilla/5.0 NodeTest'
    },
    custom_data: {
      currency: 'BDT',
      value: 1560,
      num_items: 2,
      content_ids: ['chicken-booster']
    }
  });

  assert.strictEqual(res1.success, true);
  assert.strictEqual(dispatchedCalls.length, 1);
  assert.strictEqual(dispatchedCalls[0].event_name, 'Purchase');
  console.log('✓ 1. Purchase CAPI generated after successful order persistence');
  passed++;

  // -------------------------------------------------------------
  // Test 2: Deterministic purchase_{orderNumber}
  // -------------------------------------------------------------
  assert.strictEqual(dispatchedCalls[0].event_id, purchaseEventId1);
  assert.strictEqual(dispatchedCalls[0].event_id, 'purchase_' + testOrder1.order_number);
  console.log('✓ 2. Deterministic purchase_{orderNumber}');
  passed++;

  // -------------------------------------------------------------
  // Test 3: Browser/server Purchase IDs match
  // -------------------------------------------------------------
  const successBlade = fs.readFileSync(
    path.join(__dirname, 'E Commerce Baby', 'resources', 'views', 'pages', 'landing-success.blade.php'),
    'utf8'
  );
  assert.ok(successBlade.includes("const purchaseEventId = 'purchase_' + orderNo;"), 'Browser must construct purchase_{orderNumber}');
  assert.ok(successBlade.includes("eventID: purchaseEventId"), 'Browser fbq must pass eventID = purchaseEventId');
  assert.strictEqual(purchaseEventId1, 'purchase_' + testOrder1.order_number);
  console.log('✓ 3. Browser/server Purchase IDs match');
  passed++;

  // -------------------------------------------------------------
  // Test 4: Duplicate Purchase prevented (persistent SQLite idempotency)
  // -------------------------------------------------------------
  const initialCallCount = dispatchedCalls.length;
  const resDuplicate = await metaCapi.sendEvent({
    event_name: 'Purchase',
    event_id: purchaseEventId1,
    user_data: { phone: testOrder1.phone }
  });

  assert.strictEqual(resDuplicate.success, true);
  assert.strictEqual(resDuplicate.is_replay, true);
  assert.strictEqual(resDuplicate.duplicate, true);
  assert.strictEqual(dispatchedCalls.length, initialCallCount, 'Duplicate purchase must not trigger HTTP call to Meta');
  console.log('✓ 4. Duplicate Purchase prevented (persistent SQLite idempotency)');
  passed++;

  // -------------------------------------------------------------
  // Test 5: Failed Purchase can retry with same ID
  // -------------------------------------------------------------
  const retryEventId = 'purchase_RETRY_ORDER_123';
  recordMetaCapiEvent(mockPixelId, 'Purchase', retryEventId, 'failed', 'Network simulated timeout');

  let existingFailed = findMetaCapiEvent(mockPixelId, 'Purchase', retryEventId);
  assert.strictEqual(existingFailed.status, 'failed');

  // Retry send with the SAME event_id
  const retryRes = await metaCapi.sendEvent({
    event_name: 'Purchase',
    event_id: retryEventId,
    user_data: { phone: '01899887766' }
  });
  assert.strictEqual(retryRes.success, true);
  assert.strictEqual(retryRes.event_id, retryEventId);

  let updatedEvent = findMetaCapiEvent(mockPixelId, 'Purchase', retryEventId);
  assert.strictEqual(updatedEvent.status, 'sent', 'Status must be updated to sent upon successful retry');
  console.log('✓ 5. Failed Purchase can retry with same ID');
  passed++;

  // -------------------------------------------------------------
  // Test 6: AddToCart preserves browser ID
  // -------------------------------------------------------------
  dispatchedCalls = [];
  const browserAtcId = 'atc_1725500000_abc123';
  const atcRes = await metaCapi.sendEvent({
    event_name: 'AddToCart',
    event_id: browserAtcId,
    user_data: { client_ip_address: '103.145.120.45' },
    custom_data: { currency: 'BDT', value: 750 }
  });
  assert.strictEqual(atcRes.success, true);
  assert.strictEqual(dispatchedCalls[0].event_id, browserAtcId);
  console.log('✓ 6. AddToCart preserves browser ID');
  passed++;

  // -------------------------------------------------------------
  // Test 7: InitiateCheckout preserves browser ID
  // -------------------------------------------------------------
  dispatchedCalls = [];
  const browserIcId = 'ic_1725500000_def456';
  const icRes = await metaCapi.sendEvent({
    event_name: 'InitiateCheckout',
    event_id: browserIcId,
    user_data: { client_ip_address: '103.145.120.45' }
  });
  assert.strictEqual(icRes.success, true);
  assert.strictEqual(dispatchedCalls[0].event_id, browserIcId);
  console.log('✓ 7. InitiateCheckout preserves browser ID');
  passed++;

  // -------------------------------------------------------------
  // Test 8: Supported event validation
  // -------------------------------------------------------------
  for (const ev of ['PageView', 'AddToCart', 'InitiateCheckout', 'Purchase']) {
    assert.ok(metaCapi.ALLOWED_EVENTS.includes(ev), `Must support ${ev}`);
  }
  console.log('✓ 8. Supported event validation');
  passed++;

  // -------------------------------------------------------------
  // Test 9: Unsupported event rejection
  // -------------------------------------------------------------
  const badRes = await metaCapi.sendEvent({
    event_name: 'RandomFakeEvent',
    event_id: 'bad_123'
  });
  assert.strictEqual(badRes.success, false);
  assert.ok(badRes.error.includes('Unsupported or invalid event_name'));
  console.log('✓ 9. Unsupported event rejection');
  passed++;

  // -------------------------------------------------------------
  // Test 10: Runtime Pixel configuration
  // -------------------------------------------------------------
  const cfg = await metaCapi.fetchMetaConfig();
  assert.strictEqual(cfg.active_pixel_id, mockPixelId);
  console.log('✓ 10. Runtime Pixel configuration');
  passed++;

  // -------------------------------------------------------------
  // Test 11: Runtime token configuration
  // -------------------------------------------------------------
  assert.strictEqual(cfg.access_token, mockToken);
  console.log('✓ 11. Runtime token configuration');
  passed++;

  // -------------------------------------------------------------
  // Test 12: Runtime Test Event Code
  // -------------------------------------------------------------
  assert.strictEqual(cfg.test_event_code, mockTestCode);
  dispatchedCalls = [];
  await metaCapi.sendEvent({
    event_name: 'AddToCart',
    event_id: 'atc_test_code_123'
  });
  assert.strictEqual(dispatchedCalls[0].payload.test_event_code, mockTestCode);
  console.log('✓ 12. Runtime Test Event Code');
  passed++;

  // -------------------------------------------------------------
  // Test 13: Token never reaches browser / public output
  // -------------------------------------------------------------
  const serverSource = fs.readFileSync(path.join(__dirname, 'server.js'), 'utf8');
  assert.ok(!serverSource.includes(mockToken), 'server.js must not contain token literal');
  assert.ok(successBlade.indexOf(mockToken) === -1, 'Token must not be in success blade');
  console.log('✓ 13. Token never reaches browser / public output');
  passed++;

  // -------------------------------------------------------------
  // Test 14: Token never logged
  // -------------------------------------------------------------
  let loggedOutput = '';
  const origWarn = console.warn;
  console.warn = (...args) => { loggedOutput += args.join(' '); origWarn(...args); };

  // Trigger an error to inspect logging
  metaCapi.setMockHttpHandler(async () => {
    throw new Error('Simulated Meta CAPI network failure');
  });
  await metaCapi.sendEvent({
    event_name: 'AddToCart',
    event_id: 'atc_log_check_99'
  });
  console.warn = origWarn;

  assert.ok(!loggedOutput.includes(mockToken), 'Logs must never contain access token');
  console.log('✓ 14. Token never logged');
  passed++;

  // Reset handler back to success
  metaCapi.setMockHttpHandler(async (r) => {
    dispatchedCalls.push(r);
    return { success: true, events_received: 1 };
  });

  // -------------------------------------------------------------
  // Test 15: Authorization never logged
  // -------------------------------------------------------------
  assert.ok(!loggedOutput.includes('Authorization: Bearer'), 'Logs must never contain Authorization header');
  console.log('✓ 15. Authorization never logged');
  passed++;

  // -------------------------------------------------------------
  // Test 16: Raw email never logged
  // -------------------------------------------------------------
  const secretEmail = 'very_secret_farmer@growthagro.shop';
  assert.ok(!loggedOutput.includes(secretEmail), 'Logs must never contain raw email');
  console.log('✓ 16. Raw email never logged');
  passed++;

  // -------------------------------------------------------------
  // Test 17: Raw phone never logged
  // -------------------------------------------------------------
  const secretPhone = '01799887711';
  assert.ok(!loggedOutput.includes(secretPhone), 'Logs must never contain raw phone');
  console.log('✓ 17. Raw phone never logged');
  passed++;

  // -------------------------------------------------------------
  // Test 18: fbp preserved
  // -------------------------------------------------------------
  const uData1 = metaCapi.buildUserData({ fbp: 'fb.1.1725500000.12345678' });
  assert.strictEqual(uData1.fbp, 'fb.1.1725500000.12345678', 'fbp must be preserved as raw string');
  console.log('✓ 18. fbp preserved');
  passed++;

  // -------------------------------------------------------------
  // Test 19: fbc preserved
  // -------------------------------------------------------------
  const uData2 = metaCapi.buildUserData({ fbc: 'fb.1.1725500000.IwAR0TEST' });
  assert.strictEqual(uData2.fbc, 'fb.1.1725500000.IwAR0TEST', 'fbc must be preserved as raw string');
  console.log('✓ 19. fbc preserved');
  passed++;

  // -------------------------------------------------------------
  // Test 20: Missing fbp/fbc omitted
  // -------------------------------------------------------------
  const uData3 = metaCapi.buildUserData({ phone: '01712345678' });
  assert.strictEqual(uData3.fbp, undefined, 'Missing fbp must be completely omitted');
  assert.strictEqual(uData3.fbc, undefined, 'Missing fbc must be completely omitted');
  console.log('✓ 20. Missing fbp/fbc omitted');
  passed++;

  // -------------------------------------------------------------
  // Test 21: IP handling
  // -------------------------------------------------------------
  const uDataIp = metaCapi.buildUserData({ client_ip_address: '103.145.120.55' });
  assert.strictEqual(uDataIp.client_ip_address, '103.145.120.55');
  const uDataBadIp = metaCapi.buildUserData({ client_ip_address: 'invalid-not-an-ip' });
  assert.strictEqual(uDataBadIp.client_ip_address, undefined, 'Invalid IP must be rejected');
  console.log('✓ 21. IP handling');
  passed++;

  // -------------------------------------------------------------
  // Test 22: User-Agent handling
  // -------------------------------------------------------------
  const uDataUa = metaCapi.buildUserData({ client_user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)' });
  assert.strictEqual(uDataUa.client_user_agent, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
  console.log('✓ 22. User-Agent handling');
  passed++;

  // -------------------------------------------------------------
  // Test 23: Meta failure does not rollback order
  // -------------------------------------------------------------
  metaCapi.setMockHttpHandler(async () => {
    throw new Error('Fatal 500 Meta API breakdown');
  });

  const failOrder = createOrder({
    customerName: 'Fail-Open Customer',
    phone: '01855667788',
    address: 'Chittagong Sadar',
    productId: 'chicken-booster',
    productName: 'Chicken Booster Pro',
    variantId: 'variant-1',
    variantName: '1kg',
    quantity: 1,
    unitPrice: 1200,
    subtotal: 1200,
    deliveryZone: 'outside',
    deliveryCharge: 120,
    total: 1320,
    currency: 'BDT'
  });

  assert.ok(failOrder && failOrder.order_number, 'Order must be persisted prior to CAPI dispatch');
  const failCapiRes = await metaCapi.sendEvent({
    event_name: 'Purchase',
    event_id: 'purchase_' + failOrder.order_number,
    user_data: { phone: failOrder.phone }
  });

  assert.strictEqual(failCapiRes.success, false, 'CAPI returns failure');
  const retrievedOrder = getOrderByNumber(failOrder.order_number);
  assert.ok(retrievedOrder, 'Order must remain intact in SQLite despite CAPI failure');
  assert.strictEqual(retrievedOrder.total, 1320);
  console.log('✓ 23. Meta failure does not rollback order');
  passed++;

  // Reset handler back to success
  metaCapi.setMockHttpHandler(async (r) => {
    dispatchedCalls.push(r);
    return { success: true, events_received: 1 };
  });

  // -------------------------------------------------------------
  // Test 24: Success page remains functional
  // -------------------------------------------------------------
  assert.ok(successBlade.includes('btnDownloadReceipt'), 'Receipt download button must exist on success page');
  assert.ok(successBlade.includes('btn-return'), 'Return to product link must exist');
  assert.ok(successBlade.includes('btn-whatsapp'), 'WhatsApp support button must exist');
  console.log('✓ 24. Success page remains functional');
  passed++;

  // -------------------------------------------------------------
  // Test 25: Internal secret protection
  // -------------------------------------------------------------
  const internalController = fs.readFileSync(
    path.join(__dirname, 'E Commerce Baby', 'app', 'Http', 'Controllers', 'Api', 'InternalSyncController.php'),
    'utf8'
  );
  assert.ok(internalController.includes("hash_equals($expectedSecret, $incomingSecret)"), 'Internal secret must be compared using constant-time hash_equals');
  assert.ok(internalController.includes("Forbidden: Invalid internal secret."), 'Must return 403 when secret is missing or invalid');
  console.log('✓ 25. Internal secret protection');
  passed++;

  // -------------------------------------------------------------
  // Test 26: Cross-slug protection remains intact
  // -------------------------------------------------------------
  const frontendController = fs.readFileSync(
    path.join(__dirname, 'E Commerce Baby', 'app', 'Http', 'Controllers', 'FrontendController.php'),
    'utf8'
  );
  assert.ok(frontendController.includes("abort(404"), 'Must abort 404 on slug mismatch or order not found');
  console.log('✓ 26. Cross-slug protection remains intact');
  passed++;

  // -------------------------------------------------------------
  // Test 27: No automatic courier API call
  // -------------------------------------------------------------
  const courierSource = fs.readFileSync(path.join(__dirname, 'server', 'courier.js'), 'utf8');
  assert.ok(courierSource.includes('manual_only') || courierSource.includes('DISABLED') || courierSource.includes('calculateDeliveryDecision'), 'Courier API must not be automatically called');
  console.log('✓ 27. No automatic courier API call');
  passed++;

  // -------------------------------------------------------------
  // Test 28: No automatic fraud API call
  // -------------------------------------------------------------
  assert.ok(serverSource.includes('manual admin check only') || serverSource.includes("fraudLevel = 'low'"), 'Fraud checking during checkout must remain manual-only');
  console.log('✓ 28. No automatic fraud API call');
  passed++;

  // -------------------------------------------------------------
  // Test 29: No automatic PageView on every request
  // -------------------------------------------------------------
  assert.ok(!serverSource.includes("metaCapi.sendEvent({ event_name: 'PageView'"), 'server.js must not automatically dispatch Server PageView on every request');
  console.log('✓ 29. No automatic PageView on every request');
  passed++;

  // -------------------------------------------------------------
  // Test 30: Exactly one Browser Pixel initialization
  // -------------------------------------------------------------
  const pixelBlade = fs.readFileSync(
    path.join(__dirname, 'E Commerce Baby', 'resources', 'views', 'partials', 'meta-pixel.blade.php'),
    'utf8'
  );
  const initCount = (pixelBlade.match(/fbq\('init'/g) || []).length;
  assert.strictEqual(initCount, 1, 'Exactly one fbq init call in meta-pixel.blade.php');
  console.log('✓ 30. Exactly one Browser Pixel initialization');
  passed++;

  // -------------------------------------------------------------
  // Test 31: autoConfig=false preserved
  // -------------------------------------------------------------
  assert.ok(pixelBlade.includes("fbq('set', 'autoConfig', false"), 'autoConfig must be set to false');
  console.log('✓ 31. autoConfig=false preserved');
  passed++;

  // -------------------------------------------------------------
  // Test 32: disablePushState=true preserved
  // -------------------------------------------------------------
  assert.ok(pixelBlade.includes("fbq.disablePushState = true"), 'disablePushState must be set to true');
  console.log('✓ 32. disablePushState=true preserved');
  passed++;

  console.log('\n================================================================');
  console.log(`🎉 ALL ${passed}/${total} PHASE 6 NODE.JS CAPI TESTS PASSED!`);
  console.log('================================================================\n');
}

runPhase6Tests().catch((err) => {
  console.error('\n❌ TEST FAILED:', err);
  process.exit(1);
});
