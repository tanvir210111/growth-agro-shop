<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\MetaPixel;
use App\Models\MetaPurchaseRule;
use App\Models\MetaTrackingEvent;
use App\Models\MetaTrackingSetting;
use App\Models\Order;
use App\Services\MetaPurchaseControlService;
use App\Services\MetaPurchaseRuleService;
use App\Services\MetaTrackingConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaPurchaseRulePhase9Test extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected MetaPixel $pixel;
    protected MetaTrackingSetting $settings;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'events_received' => 1,
                'fbtrace_id'      => 'trace_phase9_fake',
            ], 200),
        ]);

        // Create Admin User
        $this->admin = Admin::create([
            'name'     => 'Admin Phase9',
            'email'    => 'admin_phase9@test.com',
            'password' => bcrypt('password123'),
            'role'     => 'admin',
        ]);

        // Create active default Meta Pixel
        $this->pixel = MetaPixel::create([
            'pixel_id'     => '9876543210123456',
            'pixel_name'   => 'Test Rule Pixel',
            'access_token' => 'EAAFakeTokenForTestingRulePhase9',
            'is_active'    => true,
            'is_default'   => true,
        ]);

        // Create Tracking Settings with auto_rules_enabled = true
        $this->settings = MetaTrackingSetting::current();
        $this->settings->update([
            'is_enabled'               => true,
            'active_pixel_id'          => $this->pixel->id,
            'browser_purchase_enabled' => true,
            'server_purchase_enabled'  => true,
            'purchase_event_mode'      => 'instant',
            'purchase_delay_minutes'   => 30,
            'auto_rules_enabled'       => true,
        ]);

        app(MetaTrackingConfigService::class)->invalidateCache();
    }

    /**
     * Helper for admin JSON requests.
     */
    protected function adminJson(string $method, string $uri, array $data = [])
    {
        return $this->actingAs($this->admin, 'admin')->json($method, $uri, $data);
    }

    /**
     * Helper to create valid Order records.
     */
    protected function createTestOrder(array $attrs = []): Order
    {
        return Order::create(array_merge([
            'invoice_no'       => 'INV-' . uniqid(),
            'customer_name'    => 'Test Customer',
            'customer_phone'   => '01711000001',
            'customer_address' => 'Dhaka, Bangladesh',
            'city_type'        => 'inside_dhaka',
            'delivery_charge'  => 60,
            'subtotal'         => 1000,
            'total_amount'     => 1060,
            'status'           => 'delivered',
        ], $attrs));
    }

    // =========================================================================
    // 1. OUT-OF-SCOPE CONDITIONS & OPERATORS REJECTED
    // =========================================================================

    public function test_fraud_level_is_rejected_in_rule_creation(): void
    {
        $response = $this->adminJson('POST', '/api/admin/meta/rules', [
            'rule_name'       => 'Fraud Rule Attempt',
            'priority'        => 1,
            'condition_field' => 'fraud_level',
            'operator'        => '>=',
            'condition_value' => '2',
            'action_mode'     => 'hold',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('meta_purchase_rules', [
            'condition_field' => 'fraud_level',
        ]);
    }

    public function test_cancelled_ratio_is_rejected_in_rule_creation(): void
    {
        $response = $this->adminJson('POST', '/api/admin/meta/rules', [
            'rule_name'       => 'Cancelled Ratio Attempt',
            'priority'        => 1,
            'condition_field' => 'cancelled_ratio',
            'operator'        => '>',
            'condition_value' => '0.5',
            'action_mode'     => 'hold',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('meta_purchase_rules', [
            'condition_field' => 'cancelled_ratio',
        ]);
    }

    public function test_between_operator_is_rejected_in_rule_creation(): void
    {
        $response = $this->adminJson('POST', '/api/admin/meta/rules', [
            'rule_name'       => 'Between Operator Attempt',
            'priority'        => 1,
            'condition_field' => 'order_total',
            'operator'        => 'between',
            'condition_value' => '500',
            'action_mode'     => 'delay',
            'delay_minutes'   => 15,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('meta_purchase_rules', [
            'operator' => 'between',
        ]);
    }

    public function test_schema_endpoint_excludes_fraud_and_between(): void
    {
        $response = $this->adminJson('GET', '/api/admin/meta/rules/schema');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertNotContains('fraud_level', $data['fields']);
        $this->assertNotContains('cancelled_ratio', $data['fields']);
        $this->assertNotContains('between', $data['operators']);

        // Assert all 9 canonical fields present
        $expectedFields = [
            'customer_order_count',
            'customer_delivered_count',
            'customer_return_count',
            'customer_cancelled_count',
            'customer_completed_count',
            'customer_return_ratio',
            'customer_has_previous_order',
            'order_source',
            'order_total',
        ];
        foreach ($expectedFields as $f) {
            $this->assertContains($f, $data['fields']);
        }

        // Assert 6 approved operators present
        $expectedOps = ['=', '!=', '>', '>=', '<', '<='];
        $this->assertEqualsCanonicalizing($expectedOps, $data['operators']);
    }

    // =========================================================================
    // 2. CANONICAL FIELDS & RULE ENGINE EVALUATION
    // =========================================================================

    public function test_customer_order_count_and_has_previous_order(): void
    {
        $service = app(MetaPurchaseRuleService::class);

        // Phone with 0 orders
        $metrics = $service->computeMetrics('01711000001');
        $this->assertEquals(0, $metrics['customer_order_count']);
        $this->assertEquals(0, $metrics['customer_has_previous_order']);

        // Create 2 past orders
        $this->createTestOrder([
            'invoice_no'     => 'INV-OLD-1',
            'customer_phone' => '01711000001',
            'status'         => 'delivered',
        ]);
        $this->createTestOrder([
            'invoice_no'     => 'INV-OLD-2',
            'customer_phone' => '01711000001',
            'status'         => 'processing',
        ]);

        $metrics = $service->computeMetrics('01711000001');
        $this->assertEquals(2, $metrics['customer_order_count']);
        $this->assertEquals(1, $metrics['customer_has_previous_order']);
    }

    public function test_current_order_is_always_excluded_from_history(): void
    {
        $service = app(MetaPurchaseRuleService::class);

        $this->createTestOrder([
            'invoice_no'     => 'INV-CURRENT-123',
            'customer_phone' => '01711000002',
            'status'         => 'pending',
        ]);

        // If excludeInvoice is set to INV-CURRENT-123, count must be 0
        $metricsWithExclusion = $service->computeMetrics('01711000002', 1500, 'MAIN_WEBSITE', 'INV-CURRENT-123');
        $this->assertEquals(0, $metricsWithExclusion['customer_order_count']);
        $this->assertEquals(0, $metricsWithExclusion['customer_has_previous_order']);

        // Without exclusion, it would be 1
        $metricsWithout = $service->computeMetrics('01711000002');
        $this->assertEquals(1, $metricsWithout['customer_order_count']);
    }

    public function test_delivered_count_equals_completed_count(): void
    {
        $service = app(MetaPurchaseRuleService::class);

        $this->createTestOrder([
            'invoice_no'     => 'INV-DEL-1',
            'customer_phone' => '01711000003',
            'status'         => 'delivered',
        ]);
        $this->createTestOrder([
            'invoice_no'     => 'INV-DEL-2',
            'customer_phone' => '01711000003',
            'status'         => 'delivered',
        ]);
        $this->createTestOrder([
            'invoice_no'     => 'INV-DEL-3',
            'customer_phone' => '01711000003',
            'status'         => 'pending',
        ]);

        $metrics = $service->computeMetrics('01711000003');
        $this->assertEquals(2, $metrics['customer_delivered_count']);
        $this->assertEquals(2, $metrics['customer_completed_count']);
        $this->assertEquals($metrics['customer_delivered_count'], $metrics['customer_completed_count']);
    }

    public function test_return_and_cancelled_statuses_computed_properly(): void
    {
        $service = app(MetaPurchaseRuleService::class);

        // 'returned' and 'return' both counted
        $this->createTestOrder(['invoice_no' => 'INV-RET-1', 'customer_phone' => '01711000004', 'status' => 'returned']);
        $this->createTestOrder(['invoice_no' => 'INV-RET-2', 'customer_phone' => '01711000004', 'status' => 'return']);

        // 'cancelled', 'cancel', 'rejected' all counted
        $this->createTestOrder(['invoice_no' => 'INV-CAN-1', 'customer_phone' => '01711000004', 'status' => 'cancelled']);
        $this->createTestOrder(['invoice_no' => 'INV-CAN-2', 'customer_phone' => '01711000004', 'status' => 'cancel']);
        $this->createTestOrder(['invoice_no' => 'INV-CAN-3', 'customer_phone' => '01711000004', 'status' => 'rejected']);

        $metrics = $service->computeMetrics('01711000004');
        $this->assertEquals(5, $metrics['customer_order_count']);
        $this->assertEquals(2, $metrics['customer_return_count']);
        $this->assertEquals(3, $metrics['customer_cancelled_count']);
        $this->assertEquals(0.4, $metrics['customer_return_ratio']); // 2 / 5 = 0.4
    }

    public function test_division_by_zero_guarded_when_no_history(): void
    {
        $service = app(MetaPurchaseRuleService::class);

        $metrics = $service->computeMetrics('01799999999');
        $this->assertEquals(0, $metrics['customer_order_count']);
        $this->assertEquals(0.0, $metrics['customer_return_ratio']);
    }

    public function test_phone_normalization_matches_different_formats(): void
    {
        $service = app(MetaPurchaseRuleService::class);

        $this->createTestOrder([
            'invoice_no'     => 'INV-NORM-1',
            'customer_phone' => '01712345678',
            'status'         => 'delivered',
        ]);

        // Querying with +880 or 880 prefix must match
        $m1 = $service->computeMetrics('+8801712345678');
        $this->assertEquals(1, $m1['customer_order_count']);

        $m2 = $service->computeMetrics('8801712345678');
        $this->assertEquals(1, $m2['customer_order_count']);

        $m3 = $service->computeMetrics('01712345678');
        $this->assertEquals(1, $m3['customer_order_count']);
    }

    // =========================================================================
    // 3. RULE SNAPSHOT IN META_TRACKING_EVENTS
    // =========================================================================

    public function test_rule_snapshot_persisted_when_rule_matches(): void
    {
        // Setup a rule: high return ratio -> hold
        $rule = MetaPurchaseRule::create([
            'rule_name'       => 'Hold High Return Customers',
            'priority'        => 1,
            'condition_field' => 'customer_return_ratio',
            'operator'        => '>=',
            'condition_value' => '0.5',
            'action_mode'     => 'hold',
            'is_active'       => true,
        ]);

        // Setup customer with 1 return out of 1 order (return_ratio = 1.0 >= 0.5)
        $this->createTestOrder([
            'invoice_no'     => 'INV-PRIOR-1',
            'customer_phone' => '01811223344',
            'status'         => 'returned',
        ]);

        // Place a new order
        $currentOrder = $this->createTestOrder([
            'invoice_no'     => 'INV-NEW-HOLD',
            'customer_phone' => '01811223344',
            'customer_name'  => 'Risky Customer',
            'total_amount'   => 1260,
            'status'         => 'pending',
        ]);

        $service = app(MetaPurchaseControlService::class);
        $service->handleMainWebsiteOrder(
            $currentOrder,
            ['phone' => '01811223344', 'name' => 'Risky Customer'],
            [['product_id' => 1, 'quantity' => 1, 'price' => 1200, 'title' => 'Product 1']],
            1260.0,
            60.0
        );

        // Find the tracking event for this purchase
        $event = MetaTrackingEvent::where('order_id', 'INV-NEW-HOLD')
            ->where('event_name', 'Purchase')
            ->first();

        $this->assertNotNull($event, 'Event must be recorded');
        $this->assertEquals('hold', $event->purchase_mode);
        $this->assertEquals($rule->id, $event->rule_id, 'Event rule_id must snapshot the matching rule ID');
        $this->assertEquals('Hold High Return Customers', $event->rule_name, 'Event rule_name must snapshot the rule name');
    }

    public function test_rule_edit_or_delete_does_not_change_existing_event_snapshot(): void
    {
        // 1. Create rule and generate event
        $rule = MetaPurchaseRule::create([
            'rule_name'       => 'Snapshot Immutable Rule',
            'priority'        => 1,
            'condition_field' => 'customer_has_previous_order',
            'operator'        => '=',
            'condition_value' => '1',
            'action_mode'     => 'delay',
            'delay_minutes'   => 45,
            'is_active'       => true,
        ]);

        $this->createTestOrder([
            'invoice_no'     => 'INV-SNAP-OLD',
            'customer_phone' => '01911223344',
            'status'         => 'delivered',
        ]);

        $currentOrder = $this->createTestOrder([
            'invoice_no'     => 'INV-SNAP-NEW',
            'customer_phone' => '01911223344',
            'customer_name'  => 'Snap Customer',
            'total_amount'   => 860,
            'status'         => 'pending',
        ]);

        $service = app(MetaPurchaseControlService::class);
        $service->handleMainWebsiteOrder(
            $currentOrder,
            ['phone' => '01911223344', 'name' => 'Snap Customer'],
            [['product_id' => 1, 'quantity' => 1, 'price' => 800, 'title' => 'Item A']],
            860.0,
            60.0
        );

        $event = MetaTrackingEvent::where('order_id', 'INV-SNAP-NEW')
            ->where('event_name', 'Purchase')
            ->first();

        $this->assertNotNull($event);
        $this->assertEquals($rule->id, $event->rule_id);
        $this->assertEquals('Snapshot Immutable Rule', $event->rule_name);
        $this->assertEquals('delay', $event->purchase_mode);
        $originalScheduledAt = $event->scheduled_at;

        // 2. Modify the rule (rename, change delay, change mode)
        $rule->update([
            'rule_name'     => 'Completely Different Name',
            'action_mode'   => 'hold',
            'delay_minutes' => 120,
        ]);

        // Event snapshot MUST NOT change
        $eventFresh = $event->fresh();
        $this->assertEquals($rule->id, $eventFresh->rule_id);
        $this->assertEquals('Snapshot Immutable Rule', $eventFresh->rule_name);
        $this->assertEquals('delay', $eventFresh->purchase_mode);
        $this->assertEquals($originalScheduledAt, $eventFresh->scheduled_at);

        // 3. Delete the rule
        $rule->delete();

        // Event snapshot MUST STILL remain unchanged
        $eventAfterDelete = $event->fresh();
        $this->assertEquals($rule->id, $eventAfterDelete->rule_id);
        $this->assertEquals('Snapshot Immutable Rule', $eventAfterDelete->rule_name);
        $this->assertEquals('delay', $eventAfterDelete->purchase_mode);
    }

    public function test_pixel_switching_does_not_affect_existing_events(): void
    {
        // Record an event with Pixel A
        $event = MetaTrackingEvent::create([
            'pixel_id'             => $this->pixel->pixel_id,
            'event_name'           => 'Purchase',
            'event_id'             => 'pur_test_pixel_switch_001',
            'order_id'             => 'INV-PIX-1',
            'order_source'         => 'MAIN_WEBSITE',
            'action_source'        => 'website',
            'server_status'        => MetaTrackingEvent::STATUS_HELD,
            'deduplication_status' => MetaTrackingEvent::DEDUP_PENDING,
            'purchase_mode'        => 'hold',
        ]);

        // Now admin creates and switches to Pixel B as active/default
        $pixelB = MetaPixel::create([
            'pixel_id'     => '1112223334445556',
            'pixel_name'   => 'Pixel B',
            'access_token' => 'TokenB',
            'is_active'    => true,
            'is_default'   => true,
        ]);
        $this->pixel->update(['is_default' => false, 'is_active' => false]);

        // Event must still be tied to original Pixel A
        $this->assertEquals($this->pixel->pixel_id, $event->fresh()->pixel_id);
        $this->assertNotEquals($pixelB->pixel_id, $event->fresh()->pixel_id);
    }

    // =========================================================================
    // 4. PRIORITY AND ORDER SOURCE
    // =========================================================================

    public function test_priority_ordering_first_matching_rule_wins(): void
    {
        // Rule 1: Priority 10 -> delay 10 min
        MetaPurchaseRule::create([
            'rule_name'       => 'Rule Prio 10',
            'priority'        => 10,
            'condition_field' => 'customer_has_previous_order',
            'operator'        => '=',
            'condition_value' => '1',
            'action_mode'     => 'delay',
            'delay_minutes'   => 10,
            'is_active'       => true,
        ]);

        // Rule 2: Priority 5 (higher priority) -> hold
        MetaPurchaseRule::create([
            'rule_name'       => 'Rule Prio 5',
            'priority'        => 5,
            'condition_field' => 'customer_has_previous_order',
            'operator'        => '=',
            'condition_value' => '1',
            'action_mode'     => 'hold',
            'is_active'       => true,
        ]);

        $service = app(MetaPurchaseRuleService::class);

        // Customer with prior order
        $this->createTestOrder([
            'invoice_no'     => 'INV-PRIO-TEST',
            'customer_phone' => '01511223344',
            'status'         => 'delivered',
        ]);

        $result = $service->evaluate('01511223344', 1000, 'MAIN_WEBSITE');

        $this->assertNotNull($result);
        $this->assertEquals('hold', $result['mode']);
        $this->assertEquals('Rule Prio 5', $result['rule_name']);
    }

    public function test_order_source_condition_matches_landing_vs_main(): void
    {
        MetaPurchaseRule::create([
            'rule_name'       => 'Hold Landing Orders',
            'priority'        => 1,
            'condition_field' => 'order_source',
            'operator'        => '=',
            'condition_value' => 'LANDING',
            'action_mode'     => 'hold',
            'is_active'       => true,
        ]);

        $service = app(MetaPurchaseRuleService::class);

        // For MAIN_WEBSITE: rule does not match -> null
        $resultMain = $service->evaluate('01700000000', 500, 'MAIN_WEBSITE');
        $this->assertNull($resultMain);

        // For LANDING: rule matches -> hold
        $resultLanding = $service->evaluate('01700000000', 500, 'LANDING');
        $this->assertNotNull($resultLanding);
        $this->assertEquals('hold', $resultLanding['mode']);
    }

    // =========================================================================
    // 5. FAIL-OPEN SAFETY
    // =========================================================================

    public function test_rules_disabled_falls_back_to_global_setting(): void
    {
        // Turn off auto rules
        $this->settings->update(['auto_rules_enabled' => false]);
        app(MetaTrackingConfigService::class)->invalidateCache();

        // Create a hold rule that would match
        MetaPurchaseRule::create([
            'rule_name'       => 'Hold All Rule',
            'priority'        => 1,
            'condition_field' => 'order_total',
            'operator'        => '>',
            'condition_value' => '100',
            'action_mode'     => 'hold',
            'is_active'       => true,
        ]);

        $order = $this->createTestOrder([
            'invoice_no'     => 'INV-FALLBACK-01',
            'customer_phone' => '01711223399',
            'customer_name'  => 'Fallback Customer',
            'total_amount'   => 560,
            'status'         => 'pending',
        ]);

        $service = app(MetaPurchaseControlService::class);
        $service->handleMainWebsiteOrder(
            $order,
            ['phone' => '01711223399', 'name' => 'Fallback Customer'],
            [['product_id' => 1, 'quantity' => 1, 'price' => 500, 'title' => 'Item X']],
            560.0,
            60.0
        );

        // Event should be instant, not held
        $event = MetaTrackingEvent::where('order_id', 'INV-FALLBACK-01')
            ->where('event_name', 'Purchase')
            ->first();

        $this->assertNotNull($event);
        $this->assertEquals('instant', $event->purchase_mode);
        $this->assertNull($event->rule_id);
    }
}
