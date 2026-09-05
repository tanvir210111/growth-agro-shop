/**
 * test_meta_purchase_event_control_phase8.js
 * Comprehensive automated verification for Phase 8 Purchase Event Control (Instant / Delay / Hold).
 */

const fs = require('fs');
const assert = require('assert');

console.log('======================================================');
console.log('🧪 RUNNING PHASE 8: PURCHASE EVENT CONTROL & PARITY TESTS');
console.log('======================================================\n');

// 1. Exact Parity Checks
console.log('--- Test 1: Admin Parity Checks ---');
const appJsAdmin = fs.readFileSync('admin/app.js', 'utf8');
const appJsPublic = fs.readFileSync('E Commerce Baby/public/admin/app.js', 'utf8');
assert.strictEqual(appJsAdmin, appJsPublic, 'admin/app.js and E Commerce Baby/public/admin/app.js must be 100% identical');
console.log('✅ admin/app.js and E Commerce Baby/public/admin/app.js are 100% identical (0 diff)');

const htmlAdmin = fs.readFileSync('admin/index.html', 'utf8');
const htmlPublic = fs.readFileSync('E Commerce Baby/public/admin/index.html', 'utf8');
assert.strictEqual(htmlAdmin, htmlPublic, 'admin/index.html and E Commerce Baby/public/admin/index.html must be 100% identical');
console.log('✅ admin/index.html and E Commerce Baby/public/admin/index.html are 100% identical (0 diff)');

// 2. UI Structure Checks
console.log('\n--- Test 2: Purchase Event Control UI Elements ---');
assert(htmlAdmin.includes('Purchase Event Control (Server CAPI)'), 'Must include Purchase Event Control title');
assert(htmlAdmin.includes('id="purchaseModeInstant"'), 'Must include Instant radio button');
assert(htmlAdmin.includes('id="purchaseModeDelay"'), 'Must include Delay radio button');
assert(htmlAdmin.includes('id="purchaseModeHold"'), 'Must include Hold radio button');
assert(htmlAdmin.includes('id="metaPurchaseDelayMinutes"'), 'Must include Delay Minutes input');
assert(htmlAdmin.includes('id="metaDelayMinutesContainer"'), 'Must include Delay Minutes container');
assert(htmlAdmin.includes('id="metaPurchasesTableBody"'), 'Must include Purchase queue table body');
assert(htmlAdmin.includes('id="btnProcessDelayedPurchases"'), 'Must include Process Delayed button');
assert(htmlAdmin.includes('id="metaQueueSearchInput"'), 'Must include Queue search input');
assert(htmlAdmin.includes('id="metaQueueFilterStatus"'), 'Must include Queue status filter');
assert(htmlAdmin.includes('id="metaQueueFilterMode"'), 'Must include Queue mode filter');
console.log('✅ All Phase 8 HTML UI control elements verified');

// 3. Admin JavaScript Functions Checks
console.log('\n--- Test 3: Admin JavaScript Handlers ---');
assert(appJsAdmin.includes('function handlePurchaseModeChange()'), 'Must define handlePurchaseModeChange');
assert(appJsAdmin.includes('function loadMetaPurchaseQueue('), 'Must define loadMetaPurchaseQueue');
assert(appJsAdmin.includes('function renderMetaPurchaseQueueTable('), 'Must define renderMetaPurchaseQueueTable');
assert(appJsAdmin.includes('function releaseMetaPurchase('), 'Must define releaseMetaPurchase');
assert(appJsAdmin.includes('function retryMetaPurchase('), 'Must define retryMetaPurchase');
assert(appJsAdmin.includes('function triggerProcessDelayedPurchases()'), 'Must define triggerProcessDelayedPurchases');
assert(appJsAdmin.includes('purchase_event_mode: selectedMode'), 'saveMetaTrackingSettings must send purchase_event_mode');
assert(appJsAdmin.includes('purchase_delay_minutes:'), 'saveMetaTrackingSettings must send purchase_delay_minutes');
console.log('✅ All Phase 8 Admin JavaScript handlers and API bindings verified');

// 4. Node.js Landing CAPI Purchase Control Deferral
console.log('\n--- Test 4: Node.js Landing Page Purchase Control Deferral ---');
const metaCapi = require('./server/meta-capi');

// Test 4A: Instant Mode
metaCapi.setMockConfig({
  is_enabled: true,
  active_pixel_id: '111111111111111',
  access_token: 'MOCK_TOKEN',
  server_events: { purchase: true },
  purchase_event_mode: 'instant'
});

let mockHttpCalled = false;
metaCapi.setMockHttpHandler(async (args) => {
  mockHttpCalled = true;
  return { success: true, events_received: 1, fbtrace_id: 'trace_test_instant' };
});

(async () => {
  const nonce = Date.now();
  const instantRes = await metaCapi.sendEvent({
    event_name: 'Purchase',
    event_id: `purchase_TEST_INSTANT_NODE_${nonce}`,
    user_data: { phone: '01711111111' },
    custom_data: { value: 1200, currency: 'BDT' }
  });

  assert(instantRes.success === true, 'Instant dispatch must succeed');
  assert(mockHttpCalled === true, 'Instant mode must call Meta Graph API');
  console.log('✅ Node CAPI Instant mode dispatches immediately via Graph API');

  // Test 4B: Delay Mode
  metaCapi.setMockConfig({
    is_enabled: true,
    active_pixel_id: '111111111111111',
    access_token: 'MOCK_TOKEN',
    server_events: { purchase: true },
    purchase_event_mode: 'delay'
  });

  mockHttpCalled = false;
  const delayRes = await metaCapi.sendEvent({
    event_name: 'Purchase',
    event_id: `purchase_TEST_DELAY_NODE_${nonce}`,
    user_data: { phone: '01722222222' },
    custom_data: { value: 1200, currency: 'BDT' }
  });

  assert(delayRes.success === true, 'Delay mode response must succeed');
  assert(delayRes.deferred === true, 'Delay mode must be deferred');
  assert(delayRes.purchase_mode === 'delay', 'purchase_mode must be delay');
  assert(mockHttpCalled === false, 'Delay mode must NOT call Graph API from Node');
  console.log('✅ Node CAPI Delay mode defers dispatch to Laravel authoritative queue (No double-dispatch)');

  // Test 4C: Hold Mode
  metaCapi.setMockConfig({
    is_enabled: true,
    active_pixel_id: '111111111111111',
    access_token: 'MOCK_TOKEN',
    server_events: { purchase: true },
    purchase_event_mode: 'hold'
  });

  mockHttpCalled = false;
  const holdRes = await metaCapi.sendEvent({
    event_name: 'Purchase',
    event_id: `purchase_TEST_HOLD_NODE_${nonce}`,
    user_data: { phone: '01733333333' },
    custom_data: { value: 1200, currency: 'BDT' }
  });

  assert(holdRes.success === true, 'Hold mode response must succeed');
  assert(holdRes.deferred === true, 'Hold mode must be deferred');
  assert(holdRes.purchase_mode === 'hold', 'purchase_mode must be hold');
  assert(mockHttpCalled === false, 'Hold mode must NOT call Graph API from Node');
  console.log('✅ Node CAPI Hold mode defers dispatch to Laravel authoritative queue (No double-dispatch)');

  metaCapi.resetMockHttpHandler();
  metaCapi.invalidateConfigCache();

  console.log('\n======================================================');
  console.log('🎉 ALL PHASE 8 NODE & ADMIN VERIFICATIONS PASSED!');
  console.log('======================================================');
})();
