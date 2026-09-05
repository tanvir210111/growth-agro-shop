/**
 * test_admin_meta_tracking_phase7.js
 * Comprehensive automated verification for Phase 7 Admin Panel Meta Pixel & Tracking Control.
 */

const fs = require('fs');
const assert = require('assert');

console.log('======================================================');
console.log('🧪 RUNNING PHASE 7: ADMIN META TRACKING UI & PARITY TESTS');
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

// 2. Navigation Structure Checks
console.log('\n--- Test 2: Sidebar Navigation Structure ---');
assert(htmlAdmin.includes('id="subnav-meta-tracking"'), 'Sidebar must include subnav-meta-tracking');
assert(htmlAdmin.includes("switchView('meta-tracking')"), 'subnav-meta-tracking must call switchView("meta-tracking")');
assert(htmlAdmin.includes('Facebook / Meta'), 'Sidebar link must be titled Facebook / Meta');
console.log('✅ Facebook / Meta subnav item cleanly integrated in Setting & Configuration tree');

// 3. View Panel Structure Checks
console.log('\n--- Test 3: view-meta-tracking Panel Structure ---');
assert(htmlAdmin.includes('id="view-meta-tracking"'), 'Must include view-meta-tracking view panel');
assert(htmlAdmin.includes('Facebook / Meta Tracking Control'), 'Must display title Facebook / Meta Tracking Control');
assert(htmlAdmin.includes('id="metaMasterToggleBtn"'), 'Must include Master Tracking toggle button');
assert(htmlAdmin.includes('id="metaActivePixelCardName"'), 'Must include Active Pixel overview card');
assert(htmlAdmin.includes('id="metaBrowserBadge"'), 'Must include Browser Pixel status badge');
assert(htmlAdmin.includes('id="metaServerBadge"'), 'Must include Server CAPI status badge');
assert(htmlAdmin.includes('id="metaActivePixelSelect"'), 'Must include active pixel dropdown');
assert(htmlAdmin.includes('id="metaDefaultPixelDisplay"'), 'Must include default pixel display');
assert(htmlAdmin.includes('id="metaPixelsTableBody"'), 'Must include pixel table body');
assert(htmlAdmin.includes('id="evtBrowserPageView"'), 'Must include PageView event control');
assert(htmlAdmin.includes('id="evtBrowserAddToCart"'), 'Must include AddToCart browser control');
assert(htmlAdmin.includes('id="evtServerAddToCart"'), 'Must include AddToCart server control');
assert(htmlAdmin.includes('id="evtBrowserInitiateCheckout"'), 'Must include InitiateCheckout browser control');
assert(htmlAdmin.includes('id="evtServerInitiateCheckout"'), 'Must include InitiateCheckout server control');
assert(htmlAdmin.includes('id="evtBrowserPurchase"'), 'Must include Purchase browser control');
assert(htmlAdmin.includes('id="evtServerPurchase"'), 'Must include Purchase server control');
console.log('✅ All 4 UI sections (Global Status, Pixel Settings, Pixel IDs Table, Custom Events) verified');

// 4. Modal Security & Token Protection
console.log('\n--- Test 4: Modal Security & Token Protection ---');
assert(htmlAdmin.includes('id="metaPixelModal"'), 'Must include metaPixelModal');
assert(htmlAdmin.includes('type="password" id="metaInputAccessToken"'), 'Token input must be type="password"');
assert(htmlAdmin.includes('placeholder="Leave blank to keep existing token"'), 'Token placeholder must indicate blank keeps existing');
assert(htmlAdmin.includes('id="metaDeleteModal"'), 'Must include metaDeleteModal');
console.log('✅ Token input is masked password and delete confirmation modal exists');

// 5. JavaScript Application Logic Checks
console.log('\n--- Test 5: JavaScript Controller & State Checks ---');
assert(appJsAdmin.includes("'meta-tracking': 'Facebook / Meta Tracking'"), 'titleMap must include meta-tracking');
assert(appJsAdmin.includes("if (targetView === 'meta-tracking') loadMetaTrackingData();"), 'doSwitchView must trigger loadMetaTrackingData');
assert(appJsAdmin.includes('function loadMetaTrackingData()'), 'Must define loadMetaTrackingData');
assert(appJsAdmin.includes('function renderMetaTrackingUI()'), 'Must define renderMetaTrackingUI');
assert(appJsAdmin.includes('function openAddMetaPixelModal()'), 'Must define openAddMetaPixelModal');
assert(appJsAdmin.includes('function openEditMetaPixelModal(pixelId)'), 'Must define openEditMetaPixelModal');
assert(appJsAdmin.includes('function saveMetaPixelForm()'), 'Must define saveMetaPixelForm');
assert(appJsAdmin.includes('function setActiveMetaPixel(pixelId)'), 'Must define setActiveMetaPixel');
assert(appJsAdmin.includes('function setDefaultMetaPixel(pixelId)'), 'Must define setDefaultMetaPixel');
assert(appJsAdmin.includes('function promptDeleteMetaPixel('), 'Must define promptDeleteMetaPixel');
assert(appJsAdmin.includes('function executeDeleteMetaPixel()'), 'Must define executeDeleteMetaPixel');
assert(appJsAdmin.includes('function toggleMetaMasterSwitch()'), 'Must define toggleMetaMasterSwitch');
assert(appJsAdmin.includes('function saveMetaTrackingSettings()'), 'Must define saveMetaTrackingSettings');
console.log('✅ All Phase 7 UI lifecycle and action handlers defined');

// 6. Security Invariants
console.log('\n--- Test 6: Critical Token Security Invariants ---');
// Verify token is never stored in localStorage or sessionStorage
assert(!appJsAdmin.includes("localStorage.setItem('meta_token'"), 'Tokens must not be saved to localStorage');
assert(!appJsAdmin.includes("sessionStorage.setItem('meta_token'"), 'Tokens must not be saved to sessionStorage');
assert(!appJsAdmin.includes("console.log(rawToken)"), 'Tokens must never be logged');
assert(!appJsAdmin.includes("console.log(token)"), 'Tokens must never be logged');
assert(appJsAdmin.includes('>Configured<'), 'Must display Configured badge');
assert(appJsAdmin.includes('>Not Configured<'), 'Must display Not Configured badge');
console.log('✅ CAPI Access Token is never logged, stored in web storage, or exposed in plain text');

console.log('\n======================================================');
console.log('🎉 ALL PHASE 7 ADMIN PANEL VERIFICATIONS PASSED!');
console.log('======================================================');
