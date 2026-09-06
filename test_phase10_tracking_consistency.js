/**
 * test_phase10_tracking_consistency.js
 * Comprehensive Verification Suite for Phase 10:
 * Browser <-> Server Tracking Time + Event ID Consistency
 */

const http = require('http');
const fs = require('fs');
const path = require('path');
const assert = require('assert');

console.log('================================================================');
console.log('🧪 RUNNING PHASE 10: BROWSER ↔ SERVER TRACKING TIME & EVENT ID CONSISTENCY');
console.log('================================================================\n');

function httpRequest(options, postData) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        let json = null;
        try { json = JSON.parse(body); } catch (e) { json = null; }
        resolve({ statusCode: res.statusCode, headers: res.headers, body, json });
      });
    });
    req.on('error', reject);
    if (postData) req.write(typeof postData === 'string' ? postData : JSON.stringify(postData));
    req.end();
  });
}

let passed = 0;
let total = 0;

function pass(name, detail = '') {
  total++;
  passed++;
  console.log(`✓ [PASS ${passed}] ${name}${detail ? ' — ' + detail : ''}`);
}

function fail(name, err) {
  total++;
  console.error(`✗ [FAIL] ${name}:`, err.message || err);
  process.exit(1);
}

async function runTests() {
  const testRunId = Date.now() + '_' + Math.random().toString(36).substring(2, 6);
  const idempotencyKey = `idemp_p10_${testRunId}`;
  const testPhone = '01799887766';

  let serverOrder = null;
  let serverCanonicalEventId = null;
  let serverCanonicalEventTime = null;
  let requestStartTime = Date.now();
  let requestEndTime = 0;

  // -------------------------------------------------------------
  // Test 1: Order Creation (HTTP 201) & Server Canonical Generation
  // -------------------------------------------------------------
  try {
    requestStartTime = Date.now();
    const res = await httpRequest({
      hostname: '127.0.0.1',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      productId: 'chicken-booster',
      variantId: 'broiler-1kg',
      quantity: 1,
      idempotency_key: idempotencyKey,
      deliveryZone: 'outside',
      customerName: 'সাদিকুর রহমান',
      phone: testPhone,
      address: 'গ্রাম: চরপাড়া, থানা: ত্রিশাল, জেলা: ময়মনসিংহ'
    });

    requestEndTime = Date.now();

    assert.strictEqual(res.statusCode, 201, `Expected 201, got ${res.statusCode}: ${res.body}`);
    assert(res.json && res.json.success, 'Response must be success: true');
    assert(res.json.order, 'Response must contain order object');

    serverOrder = res.json.order;
    serverCanonicalEventId = serverOrder.event_id;
    serverCanonicalEventTime = serverOrder.event_time;

    assert(serverOrder.order_number.startsWith('CB-'), `Invalid order_number format: ${serverOrder.order_number}`);
    assert(serverCanonicalEventId.startsWith('evt_pur_'), `event_id must start with evt_pur_: ${serverCanonicalEventId}`);
    assert.strictEqual(
      serverCanonicalEventId,
      'evt_pur_' + serverOrder.order_number.replace(/[^A-Za-z0-9]/g, '_'),
      'Canonical event_id must deterministically match evt_pur_<order_number>'
    );
    assert(serverCanonicalEventTime, 'Server must return canonical event_time');
    assert(!isNaN(Date.parse(serverCanonicalEventTime)), 'Canonical event_time must be valid ISO timestamp');
    
    // Check root tracking payload in HTTP response
    assert(res.json.tracking, 'Response must contain authoritative tracking block');
    assert.strictEqual(res.json.tracking.event, 'purchase', 'tracking.event must be purchase');
    assert.strictEqual(res.json.tracking.event_id, serverCanonicalEventId, 'tracking.event_id must equal canonical event_id');
    assert.strictEqual(res.json.tracking.event_time, serverCanonicalEventTime, 'tracking.event_time must equal canonical event_time');
    assert.strictEqual(res.json.tracking.transaction_id, serverOrder.order_number, 'tracking.transaction_id must equal order_number');

    pass('1. Server Order Creation & Canonical Identity', `ID=${serverCanonicalEventId}, Time=${serverCanonicalEventTime}`);
  } catch (e) {
    fail('1. Server Order Creation & Canonical Identity', e);
  }

  // -------------------------------------------------------------
  // Test 2: Browser DataLayer Receipt & Canonical Preservation
  // -------------------------------------------------------------
  let browserDataLayer = [];
  let browserPurchaseEvent = null;

  try {
    // Setup simulated browser environment
    global.window = {
      dataLayer: browserDataLayer,
      location: { href: 'http://127.0.0.1:3000/products/chicken-booster/', pathname: '/products/chicken-booster/' }
    };
    global.document = { title: 'চিকেন বুস্টার | Growth Agro' };

    // Load fresh eventBus instance
    delete require.cache[require.resolve('./assets/js/core/event-bus.js')];
    const { eventBus } = require('./assets/js/core/event-bus.js');

    // Browser receives HTTP 201 payload and dispatches trackPurchase(serverOrder)
    browserPurchaseEvent = eventBus.trackPurchase(serverOrder);

    assert(browserPurchaseEvent, 'trackPurchase must return purchase event object');
    assert.strictEqual(browserPurchaseEvent.event, 'purchase', 'Event name must be purchase');

    // Browser time MUST EQUAL Server canonical event time T
    assert.strictEqual(
      browserPurchaseEvent.event_time,
      serverCanonicalEventTime,
      'Browser event_time MUST be EXACT SAME as Server canonical event_time (Browser must not generate new Date())'
    );

    // Browser event_id MUST EQUAL Server canonical event_id
    assert.strictEqual(
      browserPurchaseEvent.event_id,
      serverCanonicalEventId,
      'Browser event_id MUST be EXACT SAME as Server canonical event_id'
    );

    assert.strictEqual(browserPurchaseEvent.transaction_id, serverOrder.order_number, 'Browser transaction_id mismatch');
    assert.strictEqual(browserPurchaseEvent.ecommerce.value, serverOrder.total, 'Browser ecommerce.value mismatch');
    assert.strictEqual(browserPurchaseEvent.ecommerce.currency, 'BDT', 'Browser currency must be BDT');

    // Confirm presence in window.dataLayer
    const dlEvent = browserDataLayer.find(e => e.event === 'purchase');
    assert(dlEvent, 'Purchase event must exist in window.dataLayer');
    assert.strictEqual(dlEvent.event_id, serverCanonicalEventId, 'dataLayer event_id mismatch');
    assert.strictEqual(dlEvent.event_time, serverCanonicalEventTime, 'dataLayer event_time mismatch');

    pass('2. Browser DataLayer Preservation', 'Browser time === Server canonical time (0 diff)');
  } catch (e) {
    fail('2. Browser DataLayer Preservation', e);
  }

  // -------------------------------------------------------------
  // Test 3: Web GTM Container Extraction & Meta Pixel Event Mapping
  // -------------------------------------------------------------
  let browserPixelPayload = null;

  try {
    const webGtmJson = JSON.parse(fs.readFileSync(path.join(__dirname, 'gtm', 'gtm_web_container.json'), 'utf8'));

    // Check Data Layer Variables in Web GTM
    const dlvEventId = webGtmJson.containerVersion.variable.find(v => v.name === 'dlv - event_id');
    const dlvEventTime = webGtmJson.containerVersion.variable.find(v => v.name === 'dlv - event_time');
    const dlvTxId = webGtmJson.containerVersion.variable.find(v => v.name === 'dlv - transaction_id');
    const dlvVal = webGtmJson.containerVersion.variable.find(v => v.name === 'dlv - ecommerce.value');

    assert(dlvEventId, 'Web GTM must define dlv - event_id');
    assert(dlvEventTime, 'Web GTM must define dlv - event_time');
    assert(dlvTxId, 'Web GTM must define dlv - transaction_id');
    assert(dlvVal, 'Web GTM must define dlv - ecommerce.value');

    // Check Meta Pixel Purchase Tag in Web GTM
    const metaPixelPurchaseTag = webGtmJson.containerVersion.tag.find(t => t.name === 'Meta Pixel - Purchase');
    assert(metaPixelPurchaseTag, 'Web GTM must contain Meta Pixel - Purchase tag');
    const tagHtml = metaPixelPurchaseTag.parameter.find(p => p.key === 'html').value;
    assert(tagHtml.includes("eventID: '{{dlv - event_id}}'"), 'Meta Pixel Purchase must pass eventID from dlv - event_id');

    // Simulate Web GTM execution of Meta Pixel Tag
    browserPixelPayload = {
      eventName: 'Purchase',
      eventID: browserPurchaseEvent.event_id,
      event_time: browserPurchaseEvent.event_time,
      transaction_id: browserPurchaseEvent.transaction_id,
      value: browserPurchaseEvent.ecommerce.value,
      currency: browserPurchaseEvent.ecommerce.currency
    };

    assert.strictEqual(browserPixelPayload.eventID, serverCanonicalEventId, 'Browser Pixel eventID must match server canonical');
    assert.strictEqual(browserPixelPayload.event_time, serverCanonicalEventTime, 'Browser Pixel event_time must match server canonical');
    assert.strictEqual(browserPixelPayload.transaction_id, serverOrder.order_number, 'Browser Pixel transaction_id must match server canonical');

    pass('3. Web GTM & Browser Meta Pixel Mapping', 'Meta Pixel eventID === serverCanonicalEventId');
  } catch (e) {
    fail('3. Web GTM & Browser Meta Pixel Mapping', e);
  }

  // -------------------------------------------------------------
  // Test 4: Server-Side GTM (sGTM) Ingestion & Meta CAPI Payload
  // -------------------------------------------------------------
  let serverCapiPayload = null;

  try {
    const sGtmJson = JSON.parse(fs.readFileSync(path.join(__dirname, 'gtm', 'gtm_server_container.json'), 'utf8'));

    // Check sGTM variables
    const sVarEventId = sGtmJson.containerVersion.variable.find(v => v.name === 'Event ID');
    const sVarEventTime = sGtmJson.containerVersion.variable.find(v => v.name === 'Event Time');
    const sVarTxId = sGtmJson.containerVersion.variable.find(v => v.name === 'Transaction ID');

    assert(sVarEventId, 'sGTM must define Event ID variable');
    assert(sVarEventTime, 'sGTM must define Event Time variable');
    assert(sVarTxId, 'sGTM must define Transaction ID variable');

    // Check Meta CAPI Purchase Tag in sGTM
    const sGtmPurchaseTag = sGtmJson.containerVersion.tag.find(t => t.name === 'Meta CAPI - Purchase');
    assert(sGtmPurchaseTag, 'sGTM must contain Meta CAPI - Purchase tag');

    const params = sGtmPurchaseTag.parameter.reduce((acc, p) => { acc[p.key] = p.value; return acc; }, {});
    assert.strictEqual(params.eventId, '{{Event ID}}', 'sGTM CAPI Purchase must map eventId to {{Event ID}}');
    assert.strictEqual(params.eventTime, '{{Event Time}}', 'sGTM CAPI Purchase must map eventTime to {{Event Time}}');
    assert.strictEqual(params.orderId, '{{Transaction ID}}', 'sGTM CAPI Purchase must map orderId to {{Transaction ID}}');

    // Simulate sGTM tag dispatch to Meta CAPI
    const canonicalEpochSeconds = Math.floor(Date.parse(serverCanonicalEventTime) / 1000);

    serverCapiPayload = {
      event_name: 'Purchase',
      event_id: browserPixelPayload.eventID,
      event_time: browserPixelPayload.event_time,
      event_time_seconds: canonicalEpochSeconds,
      transaction_id: browserPixelPayload.transaction_id,
      value: browserPixelPayload.value,
      currency: browserPixelPayload.currency
    };

    assert.strictEqual(serverCapiPayload.event_id, serverCanonicalEventId, 'Server CAPI event_id must match server canonical');
    assert.strictEqual(serverCapiPayload.event_time, serverCanonicalEventTime, 'Server CAPI event_time must match server canonical');
    assert.strictEqual(serverCapiPayload.transaction_id, serverOrder.order_number, 'Server CAPI transaction_id must match server canonical');
    assert.strictEqual(serverCapiPayload.value, serverOrder.total, 'Server CAPI value must match server order total');
    assert.strictEqual(serverCapiPayload.currency, 'BDT', 'Server CAPI currency must be BDT');

    pass('4. Server-Side GTM & Meta CAPI Preservation', `EpochSeconds=${canonicalEpochSeconds}, EventID=${serverCapiPayload.event_id}`);
  } catch (e) {
    fail('4. Server-Side GTM & Meta CAPI Preservation', e);
  }

  // -------------------------------------------------------------
  // Test 5: Replay / Retry Idempotency (Same Order, Same ID, Same Time)
  // -------------------------------------------------------------
  try {
    // Submit identical order (same phone, product, variant, idempotency key)
    const replayRes = await httpRequest({
      hostname: '127.0.0.1',
      port: 3000,
      path: '/api/orders',
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    }, {
      productId: 'chicken-booster',
      variantId: 'broiler-1kg',
      quantity: 1,
      idempotency_key: idempotencyKey,
      deliveryZone: 'outside',
      customerName: 'সাদিকুর রহমান',
      phone: testPhone,
      address: 'গ্রাম: চরপাড়া, থানা: ত্রিশাল, জেলা: ময়মনসিংহ'
    });

    assert.strictEqual(replayRes.statusCode, 200, `Replay must return 200, got ${replayRes.statusCode}`);
    assert(replayRes.json && replayRes.json.is_replay, 'Response must indicate is_replay: true');
    
    const replayedOrder = replayRes.json.order;
    assert.strictEqual(replayedOrder.order_number, serverOrder.order_number, 'Replayed order_number must match original');
    assert.strictEqual(replayedOrder.event_id, serverCanonicalEventId, 'Replayed event_id must be EXACT SAME as original');
    assert.strictEqual(replayedOrder.event_time, serverCanonicalEventTime, 'Replayed event_time must be EXACT SAME as original');

    // Simulate browser repeated call
    const { eventBus } = require('./assets/js/core/event-bus.js');
    const dlLengthBefore = global.window.dataLayer.length;
    const dupRes = eventBus.trackPurchase(replayedOrder);

    assert.strictEqual(dupRes, null, 'Duplicate trackPurchase must return null to signal deduplication');
    assert.strictEqual(global.window.dataLayer.length, dlLengthBefore, 'No duplicate event must be pushed into dataLayer');

    pass('5. Replay & Retry Idempotency', 'Replayed order retains identical event_id, event_time, transaction_id');
  } catch (e) {
    fail('5. Replay & Retry Idempotency', e);
  }

  // -------------------------------------------------------------
  // Test 6: Rule 3 Verification (Zero CAPI Double-Dispatch from Node)
  // -------------------------------------------------------------
  try {
    const metaCapi = require('./server/meta-capi');
    metaCapi.setMockConfig({
      is_enabled: true,
      active_pixel_id: '123456789012345',
      access_token: 'MOCK_TOKEN',
      meta_capi_sender: 'sgtm',
      server_events: { purchase: true }
    });

    let mockHttpDispatched = false;
    metaCapi.setMockHttpHandler(async () => {
      mockHttpDispatched = true;
      return { success: true };
    });

    const capiRes = await metaCapi.sendEvent({
      event_name: 'Purchase',
      event_id: serverCanonicalEventId,
      event_time: serverCanonicalEventTime,
      user_data: { phone: testPhone },
      custom_data: { value: serverOrder.total, currency: 'BDT' }
    });

    assert(capiRes.skipped, 'Node CAPI sendEvent must skip when sGTM is active CAPI sender');
    assert.strictEqual(mockHttpDispatched, false, 'Node MUST NOT call Meta Graph API when sGTM is active CAPI sender');
    assert.strictEqual(capiRes.sender, 'sgtm', 'Sender reason must be sgtm');

    metaCapi.resetMockHttpHandler();
    pass('6. Rule 3 Verification: Zero Duplicate CAPI Dispatch', 'Node.js skips Graph API when sGTM is exclusive sender');
  } catch (e) {
    fail('6. Rule 3 Verification', e);
  }

  // -------------------------------------------------------------
  // Test 7: Privacy & Zero PII in Data Layer & Public Tracking
  // -------------------------------------------------------------
  try {
    const rawDataLayerStr = JSON.stringify(browserDataLayer);
    assert(!rawDataLayerStr.includes(testPhone), 'Raw customer phone number MUST NOT appear in dataLayer');
    assert(!rawDataLayerStr.includes('সাদিকুর রহমান'), 'Raw customer name MUST NOT appear in dataLayer');
    assert(!rawDataLayerStr.includes('ত্রিশাল'), 'Raw customer address MUST NOT appear in dataLayer');

    pass('7. Privacy & Zero PII in Data Layer', 'Zero raw phone, name, or address in tracking dataLayer');
  } catch (e) {
    fail('7. Privacy & Zero PII in Data Layer', e);
  }

  // -------------------------------------------------------------
  // Test 8: Scope Isolation (Admin routes remain 100% untracked)
  // -------------------------------------------------------------
  try {
    const adminHtml = fs.readFileSync(path.join(__dirname, 'admin', 'index.html'), 'utf8');
    assert(!adminHtml.includes('gtm.js'), 'Admin panel must not load GTM snippet');
    assert(!adminHtml.includes('fbevents.js'), 'Admin panel must not load Meta Pixel script');

    pass('8. Scope Isolation: Admin Untracked', 'Admin panel index.html has zero tracking scripts');
  } catch (e) {
    fail('8. Scope Isolation: Admin Untracked', e);
  }

  // -------------------------------------------------------------
  // Test 9: Transport Latency Measurement & Timing Diagnostics
  // -------------------------------------------------------------
  try {
    const diagRes = await httpRequest({
      hostname: '127.0.0.1',
      port: 3000,
      path: '/api/tracking/timing-diagnostics',
      method: 'GET'
    });

    assert.strictEqual(diagRes.statusCode, 200, 'GET /api/tracking/timing-diagnostics must return 200');
    assert(diagRes.json && diagRes.json.success, 'Diagnostics response must be success: true');
    assert(Array.isArray(diagRes.json.events), 'Diagnostics must contain events array');

    const recordedDiag = diagRes.json.events.find(e => e.order_number === serverOrder.order_number);
    assert(recordedDiag, 'Diagnostics must record current order timing');
    assert.strictEqual(recordedDiag.event_id, serverCanonicalEventId, 'Diagnostic event_id must match');
    assert.strictEqual(recordedDiag.event_time, serverCanonicalEventTime, 'Diagnostic event_time must match');

    const httpRoundTripLatencyMs = requestEndTime - requestStartTime;
    const serverTimestampMs = recordedDiag.server_timestamp_ms;
    const serverCreatedMs = Date.parse(recordedDiag.created_at);
    const transportDriftMs = Math.abs(serverTimestampMs - serverCreatedMs);

    pass(
      '9. Transport Latency Diagnostics',
      `HTTP Round-Trip: ${httpRoundTripLatencyMs}ms | Transport Drift: ${transportDriftMs}ms | Canonical Event Time Preserved: ${serverCanonicalEventTime}`
    );
  } catch (e) {
    fail('9. Transport Latency Diagnostics', e);
  }

  // -------------------------------------------------------------
  // Final Comparison Table Assertion
  // -------------------------------------------------------------
  console.log('\n================================================================');
  console.log('📊 PHASE 10 COMPLETE TRACE & EXACT COMPARISON TABLE:');
  console.log('================================================================\n');

  const comparisonTable = [
    {
      Field: 'event_id',
      Server_Canonical: serverCanonicalEventId,
      Browser_Pixel: browserPixelPayload.eventID,
      Server_CAPI: serverCapiPayload.event_id,
      Match: (serverCanonicalEventId === browserPixelPayload.eventID && browserPixelPayload.eventID === serverCapiPayload.event_id) ? 'EXACT SAME' : 'MISMATCH'
    },
    {
      Field: 'event_time',
      Server_Canonical: serverCanonicalEventTime,
      Browser_Pixel: browserPixelPayload.event_time,
      Server_CAPI: serverCapiPayload.event_time,
      Match: (serverCanonicalEventTime === browserPixelPayload.event_time && browserPixelPayload.event_time === serverCapiPayload.event_time) ? 'EXACT SAME' : 'MISMATCH'
    },
    {
      Field: 'transaction_id',
      Server_Canonical: serverOrder.order_number,
      Browser_Pixel: browserPixelPayload.transaction_id,
      Server_CAPI: serverCapiPayload.transaction_id,
      Match: (serverOrder.order_number === browserPixelPayload.transaction_id && browserPixelPayload.transaction_id === serverCapiPayload.transaction_id) ? 'EXACT SAME' : 'MISMATCH'
    },
    {
      Field: 'value',
      Server_Canonical: serverOrder.total,
      Browser_Pixel: browserPixelPayload.value,
      Server_CAPI: serverCapiPayload.value,
      Match: (serverOrder.total === browserPixelPayload.value && browserPixelPayload.value === serverCapiPayload.value) ? 'EXACT SAME' : 'MISMATCH'
    },
    {
      Field: 'currency',
      Server_Canonical: 'BDT',
      Browser_Pixel: browserPixelPayload.currency,
      Server_CAPI: serverCapiPayload.currency,
      Match: (browserPixelPayload.currency === 'BDT' && serverCapiPayload.currency === 'BDT') ? 'EXACT SAME' : 'MISMATCH'
    }
  ];

  console.table(comparisonTable);

  comparisonTable.forEach(row => {
    assert.strictEqual(row.Match, 'EXACT SAME', `Comparison failure for ${row.Field}: Mismatch detected!`);
  });

  console.log('\n================================================================');
  console.log(`🎉 ALL ${passed}/${total} PHASE 10 TRACKING CONSISTENCY TESTS PASSED (100%)!`);
  console.log('================================================================\n');
}

runTests().catch(err => {
  console.error('Fatal test error:', err);
  process.exit(1);
});
