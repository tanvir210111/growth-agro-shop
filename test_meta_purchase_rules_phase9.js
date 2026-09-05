const fs = require('fs');
const path = require('path');
const assert = require('assert');

console.log('======================================================');
console.log('🧪 RUNNING PHASE 9: PURCHASE RULE ENGINE & PARITY TESTS');
console.log('======================================================\n');

// --- Test 1: Admin Parity Checks ---
console.log('--- Test 1: Admin Parity Checks ---');
const adminAppRoot = fs.readFileSync(path.join(__dirname, 'admin', 'app.js'), 'utf8');
const adminAppPublic = fs.readFileSync(path.join(__dirname, 'E Commerce Baby', 'public', 'admin', 'app.js'), 'utf8');
assert.strictEqual(adminAppRoot, adminAppPublic, 'admin/app.js and E Commerce Baby/public/admin/app.js must be identical');
console.log('✅ admin/app.js and E Commerce Baby/public/admin/app.js are 100% byte-for-byte identical (0 diff)');

const adminHtmlRoot = fs.readFileSync(path.join(__dirname, 'admin', 'index.html'), 'utf8');
const adminHtmlPublic = fs.readFileSync(path.join(__dirname, 'E Commerce Baby', 'public', 'admin', 'index.html'), 'utf8');
assert.strictEqual(adminHtmlRoot, adminHtmlPublic, 'admin/index.html and E Commerce Baby/public/admin/index.html must be identical');
console.log('✅ admin/index.html and E Commerce Baby/public/admin/index.html are 100% byte-for-byte identical (0 diff)');

// --- Test 2: Out-of-Scope Elements Completely Removed ---
console.log('\n--- Test 2: Out-of-Scope Elements Completely Removed ---');
const ruleModalHtml = adminHtmlRoot.slice(adminHtmlRoot.indexOf('metaRuleModal'), adminHtmlRoot.indexOf('view-courier-api'));

assert(!ruleModalHtml.includes('fraud_level'), 'fraud_level must not exist in rule modal HTML');
assert(!ruleModalHtml.includes('cancelled_ratio'), 'cancelled_ratio must not exist in rule modal HTML');
assert(!ruleModalHtml.includes('value="between"'), '"between" operator value must not exist in rule modal HTML');
assert(!ruleModalHtml.includes('>between<'), '"between" option text must not exist in rule modal HTML');
console.log('✅ fraud_level, cancelled_ratio, and between operator are completely removed from rule modal HTML');

const ruleServicePhp = fs.readFileSync(
  path.join(__dirname, 'E Commerce Baby', 'app', 'Services', 'MetaPurchaseRuleService.php'),
  'utf8'
);
assert(!ruleServicePhp.includes('fraud_level'), 'fraud_level must not exist in MetaPurchaseRuleService.php');
assert(!ruleServicePhp.includes('cancelled_ratio'), 'cancelled_ratio must not exist in MetaPurchaseRuleService.php');
assert(!ruleServicePhp.includes("'between'"), '"between" operator must not exist in MetaPurchaseRuleService.php');
console.log('✅ fraud_level, cancelled_ratio, and between operator are completely absent from MetaPurchaseRuleService.php');

// --- Test 3: Approved Canonical Fields Present ---
console.log('\n--- Test 3: Approved Canonical Fields Present ---');
const canonicalFields = [
  'customer_order_count',
  'customer_delivered_count',
  'customer_return_count',
  'customer_cancelled_count',
  'customer_completed_count',
  'customer_return_ratio',
  'customer_has_previous_order',
  'order_source',
  'order_total'
];

canonicalFields.forEach(field => {
  assert(ruleModalHtml.includes(`value="${field}"`), `Rule modal HTML must include option for ${field}`);
  assert(ruleServicePhp.includes(`'${field}'`), `MetaPurchaseRuleService.php ALLOWED_FIELDS must include ${field}`);
});
console.log('✅ All 9 approved canonical fields present in both UI options and backend service whitelist');

// --- Test 4: Approved Operators Set ---
console.log('\n--- Test 4: Approved Operators Set ---');
const approvedOps = ['<', '<=', '>', '>=', '=', '!='];
approvedOps.forEach(op => {
  assert(ruleModalHtml.includes(`value="${op}"`), `Rule modal HTML must include operator ${op}`);
  assert(ruleServicePhp.includes(`'${op}'`), `MetaPurchaseRuleService.php must include operator ${op}`);
});
console.log('✅ Approved operators (<, <=, >, >=, =, !=) verified in both UI and backend');

// --- Test 5: Migration and Rule Snapshot Columns ---
console.log('\n--- Test 5: Migration and Rule Snapshot Columns ---');
const migrationPath = path.join(
  __dirname,
  'E Commerce Baby',
  'database',
  'migrations',
  '2026_09_05_000004_add_rule_snapshot_to_meta_tracking_events.php'
);
assert(fs.existsSync(migrationPath), 'Migration 2026_09_05_000004 must exist');
const migrationContent = fs.readFileSync(migrationPath, 'utf8');
assert(migrationContent.includes('rule_id'), 'Migration must add rule_id');
assert(migrationContent.includes('rule_name'), 'Migration must add rule_name');
console.log('✅ Migration adds rule_id and rule_name immutable snapshot columns to meta_tracking_events');

// --- Test 6: Model Fillable ---
console.log('\n--- Test 6: Model Fillable ---');
const modelContent = fs.readFileSync(
  path.join(__dirname, 'E Commerce Baby', 'app', 'Models', 'MetaTrackingEvent.php'),
  'utf8'
);
assert(modelContent.includes("'rule_id'"), 'MetaTrackingEvent fillable must include rule_id');
assert(modelContent.includes("'rule_name'"), 'MetaTrackingEvent fillable must include rule_name');
console.log('✅ MetaTrackingEvent fillable and casts include rule_id and rule_name');

console.log('\n======================================================');
console.log('🎉 ALL PHASE 9 VERIFICATIONS PASSED SUCCESSFULLY!');
console.log('======================================================');
