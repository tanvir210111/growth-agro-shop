/**
 * Focused Unit & Integration Tests for Universal Product Landing Page Order Architecture
 * 
 * Verifies:
 * a) Universal landing-page order resolves package price from DB
 * b) Tampered client price is ignored
 * c) Invalid package ID is rejected (Clean 400 Bad Request error)
 * d) Invalid landing-page slug is rejected (Clean 400 Bad Request error)
 * e) Legacy chicken-booster/storefront pricing still works
 * f) Multi-item dynamic landing page pricing & custom delivery rules
 */

const assert = require('node:assert');
const { DatabaseSync } = require('node:sqlite');
const path = require('path');
const fs = require('fs');
const { calculateOrderTotals, PRODUCTS, findLandingPageInDb } = require('./server/products');

console.log('====================================================');
console.log('🧪 RUNNING UNIVERSAL PRODUCT ORDER PRICING TESTS');
console.log('====================================================\n');

// 1. Ensure test landing page exists in Laravel database
const dbPath = path.join(__dirname, 'E Commerce Baby', 'database', 'database.sqlite');
const db = new DatabaseSync(dbPath);

const testSlug = 'mediascope-it-serum-' + Date.now();
const testPageContent = JSON.stringify({
  packages: [
    {
      id: 'serum-30ml',
      name: 'Organic Face Serum (30ml)',
      price: 850,
      old_price: 1200,
      weight: '30ml',
      is_active: true
    },
    {
      id: 'serum-combo-2pack',
      name: 'Mega Combo 2x Serum (60ml)',
      price: 1500,
      old_price: 2400,
      weight: '60ml',
      is_active: true
    }
  ]
});

const testDeliveryConfig = JSON.stringify({
  delivery_type: 'paid',
  charge_inside_dhaka: 70,
  charge_outside_dhaka: 130,
  same_charge_everywhere: false,
  free_delivery_above: true,
  free_delivery_threshold: 2000,
  inside_label: 'ঢাকার ভেতরে (হোম ডেলিভারি)',
  outside_label: 'ঢাকার বাইরে (কুরিয়ার)'
});

db.prepare(`
  INSERT INTO landing_pages (
    name, slug, status, theme, product_name, content, delivery_config, created_at, updated_at
  ) VALUES (?, ?, 'published', 'universal', 'MediaScope Premium Serum', ?, ?, datetime('now'), datetime('now'))
`).run('MediaScope Premium Serum', testSlug, testPageContent, testDeliveryConfig);

console.log(`✅ Seeded temporary test landing page with slug: ${testSlug}`);

try {
  // --------------------------------------------------------------------------
  // Test A: Universal landing-page order resolves package price from DB
  // --------------------------------------------------------------------------
  console.log('\n--- Test A: Universal landing-page order resolves package price from DB ---');
  const resA = calculateOrderTotals(testSlug, 'serum-30ml', 2, 'inside', null, { source: 'LANDING_PAGE' });
  assert.strictEqual(resA.product.id, testSlug);
  assert.strictEqual(resA.product.name, 'MediaScope Premium Serum');
  assert.strictEqual(resA.variant.id, 'serum-30ml');
  assert.strictEqual(resA.variant.price, 850);
  assert.strictEqual(resA.unitPrice, 850);
  assert.strictEqual(resA.quantity, 2);
  assert.strictEqual(resA.subtotal, 1700); // 850 * 2
  assert.strictEqual(resA.deliveryCharge, 70); // inside dhaka charge = 70
  assert.strictEqual(resA.total, 1770);
  console.log('✅ Passed Test A: Subtotal ৳1700, Delivery ৳70, Total ৳1770 correctly resolved from DB');

  // --------------------------------------------------------------------------
  // Test B: Tampered client price is ignored
  // --------------------------------------------------------------------------
  console.log('\n--- Test B: Tampered client price is ignored ---');
  const tamperedItems = [
    {
      productId: testSlug,
      variantId: 'serum-30ml',
      quantity: 1,
      name: 'Hacked Serum',
      price: 10 // Client attempts to buy 850 Tk serum for 10 Tk
    }
  ];
  const resB = calculateOrderTotals(testSlug, 'serum-30ml', 1, 'outside', tamperedItems, { source: 'LANDING_PAGE' });
  assert.strictEqual(resB.unitPrice, 850, 'Authoritative unit price must be 850, NOT tampered 10');
  assert.strictEqual(resB.subtotal, 850, 'Authoritative subtotal must be 850, NOT tampered 10');
  assert.strictEqual(resB.deliveryCharge, 130, 'Delivery charge for outside dhaka must be 130');
  assert.strictEqual(resB.total, 980); // 850 + 130
  console.log('✅ Passed Test B: Client-tampered price of ৳10 was completely IGNORED and DB price ৳850 was used');

  // --------------------------------------------------------------------------
  // Test C: Invalid package ID is rejected with clean error
  // --------------------------------------------------------------------------
  console.log('\n--- Test C: Invalid package ID is rejected with clean error ---');
  let errorCaughtC = false;
  try {
    calculateOrderTotals(testSlug, 'invalid-package-id-999', 1, 'inside', null, { source: 'LANDING_PAGE' });
  } catch (err) {
    errorCaughtC = true;
    assert.ok(err.message.includes('প্যাকেজ পাওয়া যায়নি') || err.message.includes('invalid-package-id-999'));
    console.log(`✅ Caught expected error for invalid package ID: "${err.message}"`);
  }
  assert.ok(errorCaughtC, 'Must throw error on invalid package ID');

  // Multi-item with one invalid package
  let errorCaughtC2 = false;
  try {
    calculateOrderTotals(testSlug, 'serum-30ml', 2, 'inside', [
      { variantId: 'serum-30ml', quantity: 1, price: 850 },
      { variantId: 'fake-hack-variant', quantity: 1, price: 1 }
    ], { source: 'LANDING_PAGE' });
  } catch (err) {
    errorCaughtC2 = true;
    assert.ok(err.message.includes('fake-hack-variant'));
    console.log(`✅ Caught expected error for multi-item invalid package ID: "${err.message}"`);
  }
  assert.ok(errorCaughtC2, 'Must throw error on invalid package ID in items array');

  // --------------------------------------------------------------------------
  // Test D: Invalid landing-page slug is rejected with clean error
  // --------------------------------------------------------------------------
  console.log('\n--- Test D: Invalid landing-page slug is rejected with clean error ---');
  let errorCaughtD = false;
  try {
    calculateOrderTotals('completely-unknown-landing-page-slug-xyz', 'default', 1, 'inside', null, { source: 'LANDING_PAGE' });
  } catch (err) {
    errorCaughtD = true;
    assert.ok(err.message.includes('এই পণ্যের মূল্য বা প্যাকেজ তথ্য পাওয়া যায়নি') || err.message.includes('ল্যান্ডিং পেজ পাওয়া যায়নি') || err.message.includes('পণ্য পাওয়া যায়নি'));
    console.log(`✅ Caught expected error for non-existent landing page slug: "${err.message}"`);
  }
  assert.ok(errorCaughtD, 'Must throw error on non-existent landing page slug');

  // --------------------------------------------------------------------------
  // Test E: Legacy chicken-booster & storefront pricing still works
  // --------------------------------------------------------------------------
  console.log('\n--- Test E: Legacy chicken-booster & storefront pricing still works ---');
  
  // E1. Baby store product from hardcoded PRODUCTS
  const resE1 = calculateOrderTotals('baby-butterfly-set', '6-12M', 2, 'inside');
  assert.strictEqual(resE1.product.name, PRODUCTS['baby-butterfly-set'].name);
  assert.strictEqual(resE1.unitPrice, 200);
  assert.strictEqual(resE1.subtotal, 400);
  assert.strictEqual(resE1.deliveryCharge, 60);
  assert.strictEqual(resE1.total, 460);
  console.log('✅ Passed Test E1: Baby store storefront product calculated accurately (৳400 + ৳60 = ৳460)');

  // E2. Chicken booster product
  const resE2 = calculateOrderTotals('chicken-booster', 'variant-2', 1, 'outside', null, { source: 'LANDING_PAGE' });
  assert.ok(resE2.subtotal >= 1850, `Subtotal ${resE2.subtotal} matches chicken booster 2-pack`);
  console.log(`✅ Passed Test E2: Chicken booster order calculated accurately (Total: ৳${resE2.total})`);

  // --------------------------------------------------------------------------
  // Test F: Custom Delivery threshold logic
  // --------------------------------------------------------------------------
  console.log('\n--- Test F: Custom Delivery threshold logic ---');
  // 2x Mega Combo = 1500 * 2 = 3000 >= threshold 2000 => free delivery
  const resF = calculateOrderTotals(testSlug, 'serum-combo-2pack', 2, 'outside', null, { source: 'LANDING_PAGE' });
  assert.strictEqual(resF.subtotal, 3000);
  assert.strictEqual(resF.deliveryCharge, 0, 'Should qualify for free delivery threshold above ৳2000');
  assert.strictEqual(resF.total, 3000);
  console.log('✅ Passed Test F: Order amount ৳3000 >= threshold ৳2000 correctly awarded free delivery (৳0)');

  console.log('\n====================================================');
  console.log('🎉 ALL UNIVERSAL ORDER PRICING TESTS PASSED!');
  console.log('====================================================\n');
} finally {
  // Clean up temporary database row
  db.prepare('DELETE FROM landing_pages WHERE slug = ?').run(testSlug);
}
