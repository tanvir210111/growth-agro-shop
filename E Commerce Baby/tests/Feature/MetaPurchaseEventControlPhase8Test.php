<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\MetaPixel;
use App\Models\MetaTrackingEvent;
use App\Models\MetaTrackingSetting;
use App\Models\Order;
use App\Services\MetaConversionApiService;
use App\Services\MetaEventIdService;
use App\Services\MetaPurchaseControlService;
use App\Services\MetaTrackingConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaPurchaseEventControlPhase8Test extends TestCase
{
    use RefreshDatabase;

    protected Admin $superAdmin;
    protected Admin $admin;
    protected Admin $moderator;
    protected MetaPixel $pixelA;
    protected MetaPixel $pixelB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Admins for RBAC
        $this->superAdmin = Admin::create([
            'name'     => 'Super Admin',
            'email'    => 'superadmin@example.com',
            'password' => bcrypt('password123'),
            'role'     => 'superadmin',
        ]);

        $this->admin = Admin::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role'     => 'admin',
        ]);

        $this->moderator = Admin::create([
            'name'     => 'Moderator User',
            'email'    => 'mod@example.com',
            'password' => bcrypt('password123'),
            'role'     => 'moderator',
        ]);

        // 2. Create Two Meta Pixels
        $this->pixelA = MetaPixel::create([
            'pixel_name'      => 'Pixel Alpha',
            'pixel_id'        => '111111111111111',
            'access_token'    => 'EAAB_token_alpha_secret_value',
            'test_event_code' => 'TEST_ALPHA',
            'is_active'       => true,
            'is_default'      => true,
        ]);

        $this->pixelB = MetaPixel::create([
            'pixel_name'      => 'Pixel Beta',
            'pixel_id'        => '222222222222222',
            'access_token'    => 'EAAB_token_beta_secret_value',
            'test_event_code' => 'TEST_BETA',
            'is_active'       => false,
            'is_default'      => false,
        ]);

        // 3. Configure Tracking Settings
        $settings = MetaTrackingSetting::current();
        $settings->update([
            'is_enabled'                     => true,
            'active_pixel_id'                => $this->pixelA->id,
            'browser_purchase_enabled'       => true,
            'server_purchase_enabled'        => true,
            'purchase_event_mode'            => 'instant',
            'purchase_delay_minutes'         => 30,
        ]);

        app(MetaTrackingConfigService::class)->invalidateCache();
    }

    // =========================================================================
    // 1. SETTINGS & MODES CONFIGURATION
    // =========================================================================

    public function test_default_purchase_mode_is_instant()
    {
        $config = app(MetaTrackingConfigService::class);
        $this->assertEquals('instant', $config->getPurchaseEventMode());
        $this->assertEquals(30, $config->getPurchaseDelayMinutes());
    }

    public function test_admin_can_configure_delay_mode_with_duration()
    {
        $response = $this->actingAs($this->admin, 'admin')->putJson('/api/admin/meta/tracking-settings', [
            'purchase_event_mode'    => 'delay',
            'purchase_delay_minutes' => 45,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('settings.purchase_control.mode', 'delay');
        $response->assertJsonPath('settings.purchase_control.delay_minutes', 45);

        $config = app(MetaTrackingConfigService::class);
        $this->assertEquals('delay', $config->getPurchaseEventMode());
        $this->assertEquals(45, $config->getPurchaseDelayMinutes());
    }

    public function test_admin_can_configure_hold_mode()
    {
        $response = $this->actingAs($this->admin, 'admin')->putJson('/api/admin/meta/tracking-settings', [
            'purchase_event_mode' => 'hold',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('settings.purchase_control.mode', 'hold');

        $config = app(MetaTrackingConfigService::class);
        $this->assertEquals('hold', $config->getPurchaseEventMode());
    }

    // =========================================================================
    // 2. MAIN WEBSITE ORDER & FAIL-OPEN ORDER CREATION
    // =========================================================================

    public function test_main_website_order_in_instant_mode_dispatches_capi_immediately()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'events_received' => 1,
                'fbtrace_id'      => 'trace_instant_123',
            ], 200),
        ]);

        $order = Order::create([
            'invoice_no'      => 'ORD-INSTANT-101',
            'customer_name'   => 'Rahim Khan',
            'customer_phone'  => '01711223344',
            'customer_address'=> 'Dhanmondi, Dhaka',
            'city_type'       => 'inside_dhaka',
            'delivery_charge' => 70,
            'subtotal'        => 1200,
            'total_amount'    => 1270,
            'status'          => 'pending',
        ]);

        $service = app(MetaPurchaseControlService::class);
        $result = $service->handleMainWebsiteOrder(
            $order,
            ['phone' => '01711223344', 'name' => 'Rahim Khan'],
            [['product_id' => 1, 'title' => 'Chicken Booster 1L', 'quantity' => 1, 'price' => 1200]],
            1270.0,
            70.0
        );

        $this->assertTrue($result['success']);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '111111111111111/events') &&
                   $request['data'][0]['event_id'] === 'purchase_ORD-INSTANT-101';
        });

        $this->assertDatabaseHas('meta_tracking_events', [
            'pixel_id'      => '111111111111111',
            'event_id'      => 'purchase_ORD-INSTANT-101',
            'server_status' => 'sent',
            'order_source'  => 'MAIN_WEBSITE',
        ]);
    }

    public function test_main_website_order_in_delay_mode_creates_scheduled_queue_record()
    {
        MetaTrackingSetting::current()->update([
            'purchase_event_mode'    => 'delay',
            'purchase_delay_minutes' => 60,
        ]);
        app(MetaTrackingConfigService::class)->invalidateCache();

        Http::fake();

        $order = Order::create([
            'invoice_no'      => 'ORD-DELAY-202',
            'customer_name'   => 'Karim Ullah',
            'customer_phone'  => '01811223344',
            'customer_address'=> 'Gulshan, Dhaka',
            'city_type'       => 'inside_dhaka',
            'delivery_charge' => 70,
            'subtotal'        => 2400,
            'total_amount'    => 2470,
            'status'          => 'pending',
        ]);

        $service = app(MetaPurchaseControlService::class);
        $result = $service->handleMainWebsiteOrder(
            $order,
            ['phone' => '01811223344', 'name' => 'Karim Ullah'],
            [['product_id' => 2, 'title' => 'Chicken Booster 2L', 'quantity' => 2, 'price' => 1200]],
            2470.0,
            70.0
        );

        $this->assertTrue($result['success']);
        $this->assertTrue($result['delayed']);

        // Meta Graph API must NOT have been called yet
        Http::assertNothingSent();

        // Single authoritative queue record created with status scheduled
        $event = MetaTrackingEvent::where('event_id', 'purchase_ORD-DELAY-202')->first();
        $this->assertNotNull($event);
        $this->assertEquals('scheduled', $event->server_status);
        $this->assertEquals('delay', $event->purchase_mode);
        $this->assertEquals('MAIN_WEBSITE', $event->order_source);
        $this->assertNotNull($event->scheduled_at);
        $this->assertNull($event->sent_at);
        $this->assertTrue($event->scheduled_at->isAfter(now()->addMinutes(50)));
    }

    public function test_main_website_order_in_hold_mode_creates_held_queue_record()
    {
        MetaTrackingSetting::current()->update([
            'purchase_event_mode' => 'hold',
        ]);
        app(MetaTrackingConfigService::class)->invalidateCache();

        Http::fake();

        $order = Order::create([
            'invoice_no'      => 'ORD-HOLD-303',
            'customer_name'   => 'Sultana Begum',
            'customer_phone'  => '01911223344',
            'customer_address'=> 'Uttara, Dhaka',
            'city_type'       => 'inside_dhaka',
            'delivery_charge' => 70,
            'subtotal'        => 1200,
            'total_amount'    => 1270,
            'status'          => 'pending',
        ]);

        $service = app(MetaPurchaseControlService::class);
        $result = $service->handleMainWebsiteOrder(
            $order,
            ['phone' => '01911223344', 'name' => 'Sultana Begum'],
            [['product_id' => 1, 'title' => 'Chicken Booster 1L', 'quantity' => 1, 'price' => 1200]],
            1270.0,
            70.0
        );

        $this->assertTrue($result['success']);
        $this->assertTrue($result['held']);

        Http::assertNothingSent();

        $event = MetaTrackingEvent::where('event_id', 'purchase_ORD-HOLD-303')->first();
        $this->assertNotNull($event);
        $this->assertEquals('held', $event->server_status);
        $this->assertEquals('hold', $event->purchase_mode);
        $this->assertNull($event->scheduled_at);
        $this->assertNull($event->sent_at);
    }

    public function test_order_creation_is_fail_open_even_if_meta_throws_exception()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['error' => ['message' => 'Service Unavailable']], 503),
        ]);

        $order = Order::create([
            'invoice_no'      => 'ORD-FAIL-404',
            'customer_name'   => 'Anisur Rahman',
            'customer_phone'  => '01611223344',
            'customer_address'=> 'Mirpur, Dhaka',
            'city_type'       => 'inside_dhaka',
            'delivery_charge' => 70,
            'subtotal'        => 1200,
            'total_amount'    => 1270,
            'status'          => 'pending',
        ]);

        $service = app(MetaPurchaseControlService::class);
        $result = $service->handleMainWebsiteOrder(
            $order,
            ['phone' => '01611223344'],
            [],
            1270.0,
            70.0
        );

        // Even though CAPI returned 503, the order itself exists and is unaffected
        $this->assertDatabaseHas('orders', ['invoice_no' => 'ORD-FAIL-404']);
        $this->assertDatabaseHas('meta_tracking_events', [
            'event_id'      => 'purchase_ORD-FAIL-404',
            'server_status' => 'failed',
        ]);
    }

    // =========================================================================
    // 3. LANDING PAGE HANDOFF & SINGLE AUTHORITATIVE QUEUE
    // =========================================================================

    public function test_landing_order_sync_records_scheduled_event_in_delay_mode()
    {
        MetaTrackingSetting::current()->update([
            'purchase_event_mode'    => 'delay',
            'purchase_delay_minutes' => 30,
        ]);
        app(MetaTrackingConfigService::class)->invalidateCache();

        $response = $this->withHeaders([
            'X-Internal-Secret' => 'baby-fashion-internal-2024-secret',
        ])->postJson('/api/internal/sync-landing-order', [
            'order_number'    => 'CB-LP-9901',
            'customer_name'   => 'Landing Customer',
            'customer_phone'  => '01799887766',
            'customer_address'=> 'Chittagong, Bangladesh',
            'delivery_zone'   => 'outside',
            'delivery_charge' => 130,
            'subtotal'        => 1200,
            'total'           => 1330,
            'landing_page'    => '/product/chicken-booster',
        ]);

        $response->assertStatus(201);

        // Single authoritative queue in Laravel
        $event = MetaTrackingEvent::where('event_id', 'purchase_CB-LP-9901')->first();
        $this->assertNotNull($event);
        $this->assertEquals('LANDING', $event->order_source);
        $this->assertEquals('scheduled', $event->server_status);
        $this->assertEquals('delay', $event->purchase_mode);
        $this->assertNotNull($event->scheduled_at);
        $this->assertNull($event->sent_at);
    }

    public function test_landing_order_sync_records_held_event_in_hold_mode()
    {
        MetaTrackingSetting::current()->update([
            'purchase_event_mode' => 'hold',
        ]);
        app(MetaTrackingConfigService::class)->invalidateCache();

        $response = $this->withHeaders([
            'X-Internal-Secret' => 'baby-fashion-internal-2024-secret',
        ])->postJson('/api/internal/sync-landing-order', [
            'order_number'    => 'CB-LP-9902',
            'customer_name'   => 'Held Landing Customer',
            'customer_phone'  => '01799887755',
            'customer_address'=> 'Sylhet, Bangladesh',
            'delivery_zone'   => 'outside',
            'delivery_charge' => 130,
            'subtotal'        => 1200,
            'total'           => 1330,
            'landing_page'    => '/product/chicken-booster',
        ]);

        $response->assertStatus(201);

        $event = MetaTrackingEvent::where('event_id', 'purchase_CB-LP-9902')->first();
        $this->assertNotNull($event);
        $this->assertEquals('LANDING', $event->order_source);
        $this->assertEquals('held', $event->server_status);
        $this->assertEquals('hold', $event->purchase_mode);
        $this->assertNull($event->sent_at);
    }

    // =========================================================================
    // 4. DELAY SCHEDULER & DUE EVENT DISPATCH
    // =========================================================================

    public function test_artisan_command_dispatches_due_delayed_purchases_only()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['events_received' => 1, 'fbtrace_id' => 'trace_sched_01'], 200),
        ]);

        // 1. Due event (scheduled in the past)
        $dueEvent = MetaTrackingEvent::create([
            'pixel_id'             => $this->pixelA->pixel_id,
            'event_name'           => 'Purchase',
            'event_id'             => 'purchase_DUE_001',
            'order_id'             => 'DUE_001',
            'order_source'         => 'MAIN_WEBSITE',
            'action_source'        => 'website',
            'purchase_mode'        => 'delay',
            'server_status'        => 'scheduled',
            'scheduled_at'         => now()->subMinutes(10),
            'sent_at'              => null,
            'user_data'            => ['phone' => '01711111111'],
            'custom_data'          => ['value' => 1200, 'currency' => 'BDT'],
        ]);

        // 2. Future event (not yet due)
        $futureEvent = MetaTrackingEvent::create([
            'pixel_id'             => $this->pixelA->pixel_id,
            'event_name'           => 'Purchase',
            'event_id'             => 'purchase_FUTURE_002',
            'order_id'             => 'FUTURE_002',
            'order_source'         => 'MAIN_WEBSITE',
            'action_source'        => 'website',
            'purchase_mode'        => 'delay',
            'server_status'        => 'scheduled',
            'scheduled_at'         => now()->addMinutes(20),
            'sent_at'              => null,
            'user_data'            => ['phone' => '01722222222'],
            'custom_data'          => ['value' => 1200, 'currency' => 'BDT'],
        ]);

        // Run artisan command
        Artisan::call('meta:process-delayed-purchases', ['--limit' => 50]);

        $dueEvent->refresh();
        $futureEvent->refresh();

        // Due event dispatched and marked sent
        $this->assertEquals('sent', $dueEvent->server_status);
        $this->assertNotNull($dueEvent->sent_at);
        $this->assertEquals(1, $dueEvent->attempt_count);

        // Future event untouched
        $this->assertEquals('scheduled', $futureEvent->server_status);
        $this->assertNull($futureEvent->sent_at);
        $this->assertEquals(0, $futureEvent->attempt_count);

        Http::assertSentCount(1);
    }

    public function test_admin_manual_process_delayed_endpoint_triggers_processing()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['events_received' => 1, 'fbtrace_id' => 'trace_manual_01'], 200),
        ]);

        MetaTrackingEvent::create([
            'pixel_id'      => $this->pixelA->pixel_id,
            'event_name'    => 'Purchase',
            'event_id'      => 'purchase_MANUAL_01',
            'order_id'      => 'MANUAL_01',
            'purchase_mode' => 'delay',
            'server_status' => 'scheduled',
            'scheduled_at'  => now()->subMinutes(5),
            'sent_at'       => null,
            'user_data'     => ['phone' => '01733333333'],
            'custom_data'   => ['value' => 990, 'currency' => 'BDT'],
        ]);

        $response = $this->actingAs($this->admin, 'admin')->postJson('/api/admin/meta/purchases/process-delayed');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('result.processed', 1);
        $response->assertJsonPath('result.succeeded', 1);

        $event = MetaTrackingEvent::where('event_id', 'purchase_MANUAL_01')->first();
        $this->assertEquals('sent', $event->server_status);
    }

    // =========================================================================
    // 5. HOLD & RELEASE SEMANTICS
    // =========================================================================

    public function test_admin_can_release_held_purchase_event()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['events_received' => 1, 'fbtrace_id' => 'trace_rel_123'], 200),
        ]);

        $heldEvent = MetaTrackingEvent::create([
            'pixel_id'      => $this->pixelA->pixel_id,
            'event_name'    => 'Purchase',
            'event_id'      => 'purchase_HELD_REL_01',
            'order_id'      => 'HELD_REL_01',
            'order_source'  => 'MAIN_WEBSITE',
            'action_source' => 'website',
            'purchase_mode' => 'hold',
            'server_status' => 'held',
            'sent_at'       => null,
            'user_data'     => ['phone' => '01744444444'],
            'custom_data'   => ['value' => 1500, 'currency' => 'BDT'],
        ]);

        $response = $this->actingAs($this->admin, 'admin')->postJson("/api/admin/meta/purchases/{$heldEvent->id}/release");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('event.server_status', 'sent');

        $heldEvent->refresh();
        $this->assertEquals('sent', $heldEvent->server_status);
        $this->assertNotNull($heldEvent->sent_at);
        $this->assertNotNull($heldEvent->released_at);
        $this->assertEquals($this->admin->id, $heldEvent->released_by);
        $this->assertEquals('purchase_HELD_REL_01', $heldEvent->event_id);

        Http::assertSent(function ($request) {
            return $request['data'][0]['event_id'] === 'purchase_HELD_REL_01';
        });
    }

    public function test_releasing_non_held_or_already_sent_event_returns_422()
    {
        $sentEvent = MetaTrackingEvent::create([
            'pixel_id'      => $this->pixelA->pixel_id,
            'event_name'    => 'Purchase',
            'event_id'      => 'purchase_ALREADY_SENT',
            'order_id'      => 'ALREADY_SENT',
            'purchase_mode' => 'instant',
            'server_status' => 'sent',
            'sent_at'       => now(),
        ]);

        $response = $this->actingAs($this->admin, 'admin')->postJson("/api/admin/meta/purchases/{$sentEvent->id}/release");

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }

    public function test_held_event_remains_held_if_meta_dispatch_fails_on_release()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['error' => ['message' => 'Rate limited']], 429),
        ]);

        $heldEvent = MetaTrackingEvent::create([
            'pixel_id'      => $this->pixelA->pixel_id,
            'event_name'    => 'Purchase',
            'event_id'      => 'purchase_FAIL_REL',
            'order_id'      => 'FAIL_REL',
            'purchase_mode' => 'hold',
            'server_status' => 'held',
            'attempt_count' => 0,
            'sent_at'       => null,
            'user_data'     => ['phone' => '01755555555'],
        ]);

        $response = $this->actingAs($this->admin, 'admin')->postJson("/api/admin/meta/purchases/{$heldEvent->id}/release");

        $response->assertStatus(502);

        $heldEvent->refresh();
        $this->assertEquals('held', $heldEvent->server_status);
        $this->assertNull($heldEvent->sent_at);
        $this->assertEquals(1, $heldEvent->attempt_count);
        $this->assertStringContainsString('Rate limited', $heldEvent->error_message);
    }

    // =========================================================================
    // 6. RETRY SEMANTICS (BOUNDED & SAME EVENT_ID)
    // =========================================================================

    public function test_retry_failed_purchase_preserves_exact_same_event_id_and_pixel()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['events_received' => 1, 'fbtrace_id' => 'trace_retry_ok'], 200),
        ]);

        $failedEvent = MetaTrackingEvent::create([
            'pixel_id'      => $this->pixelA->pixel_id,
            'event_name'    => 'Purchase',
            'event_id'      => 'purchase_RETRY_001',
            'order_id'      => 'RETRY_001',
            'order_source'  => 'MAIN_WEBSITE',
            'purchase_mode' => 'delay',
            'server_status' => 'failed',
            'attempt_count' => 1,
            'sent_at'       => null,
            'user_data'     => ['phone' => '01766666666'],
            'custom_data'   => ['value' => 1400, 'currency' => 'BDT'],
        ]);

        $response = $this->actingAs($this->admin, 'admin')->postJson("/api/admin/meta/purchases/{$failedEvent->id}/retry");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $failedEvent->refresh();
        $this->assertEquals('sent', $failedEvent->server_status);
        $this->assertEquals('purchase_RETRY_001', $failedEvent->event_id);
        $this->assertEquals($this->pixelA->pixel_id, $failedEvent->pixel_id);
        $this->assertEquals(2, $failedEvent->attempt_count);
        $this->assertNotNull($failedEvent->sent_at);

        Http::assertSent(function ($request) {
            return $request['data'][0]['event_id'] === 'purchase_RETRY_001';
        });
    }

    public function test_retry_is_bounded_and_rejected_after_max_attempts()
    {
        $exhaustedEvent = MetaTrackingEvent::create([
            'pixel_id'      => $this->pixelA->pixel_id,
            'event_name'    => 'Purchase',
            'event_id'      => 'purchase_EXHAUSTED',
            'order_id'      => 'EXHAUSTED',
            'purchase_mode' => 'delay',
            'server_status' => 'failed',
            'attempt_count' => 5, // MAX_RETRY_ATTEMPTS reached
            'sent_at'       => null,
        ]);

        $response = $this->actingAs($this->admin, 'admin')->postJson("/api/admin/meta/purchases/{$exhaustedEvent->id}/retry");

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $this->assertStringContainsString('Maximum retry attempts', $response->json('message'));
    }

    // =========================================================================
    // 7. PIXEL SWITCHING EDGE CASE (SECTION 15)
    // =========================================================================

    public function test_held_event_dispatches_to_original_pixel_even_after_runtime_pixel_switch()
    {
        // 1. Event was created under Pixel Alpha
        $heldEvent = MetaTrackingEvent::create([
            'pixel_id'      => $this->pixelA->pixel_id,
            'event_name'    => 'Purchase',
            'event_id'      => 'purchase_ALPHA_BOUND',
            'order_id'      => 'ALPHA_BOUND',
            'purchase_mode' => 'hold',
            'server_status' => 'held',
            'sent_at'       => null,
            'user_data'     => ['phone' => '01777777777'],
            'custom_data'   => ['value' => 1200, 'currency' => 'BDT'],
        ]);

        // 2. Admin switches Active Pixel to Pixel Beta
        $this->pixelA->update(['is_active' => false]);
        $this->pixelB->update(['is_active' => true]);
        MetaTrackingSetting::current()->update(['active_pixel_id' => $this->pixelB->id]);
        app(MetaTrackingConfigService::class)->invalidateCache();

        $this->assertEquals($this->pixelB->pixel_id, app(MetaTrackingConfigService::class)->getActivePixelId());

        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['events_received' => 1, 'fbtrace_id' => 'trace_bound_ok'], 200),
        ]);

        // 3. Release held event
        $response = $this->actingAs($this->admin, 'admin')->postJson("/api/admin/meta/purchases/{$heldEvent->id}/release");

        $response->assertStatus(200);

        // Verification: The request MUST have gone to Pixel Alpha endpoint with Alpha token!
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '111111111111111/events') &&
                   $request->hasHeader('Authorization', 'Bearer EAAB_token_alpha_secret_value') &&
                   $request['data'][0]['event_id'] === 'purchase_ALPHA_BOUND';
        });
    }

    // =========================================================================
    // 8. SECURITY, RBAC & API CONTRACTS
    // =========================================================================

    public function test_moderator_cannot_release_or_retry_purchases()
    {
        $event = MetaTrackingEvent::create([
            'pixel_id'      => $this->pixelA->pixel_id,
            'event_name'    => 'Purchase',
            'event_id'      => 'purchase_MOD_TEST',
            'order_id'      => 'MOD_TEST',
            'purchase_mode' => 'hold',
            'server_status' => 'held',
        ]);

        $resRelease = $this->actingAs($this->moderator, 'admin')->postJson("/api/admin/meta/purchases/{$event->id}/release");
        $resRelease->assertStatus(403);

        $resRetry = $this->actingAs($this->moderator, 'admin')->postJson("/api/admin/meta/purchases/{$event->id}/retry");
        $resRetry->assertStatus(403);
    }

    public function test_admin_get_purchases_never_leaks_tokens_or_raw_pii()
    {
        MetaTrackingEvent::create([
            'pixel_id'      => $this->pixelA->pixel_id,
            'event_name'    => 'Purchase',
            'event_id'      => 'purchase_PII_TEST',
            'order_id'      => 'PII_TEST',
            'purchase_mode' => 'instant',
            'server_status' => 'sent',
            'user_data'     => ['phone' => '01799999999', 'ph' => hash('sha256', '8801799999999')],
            'sent_at'       => now(),
        ]);

        $response = $this->actingAs($this->admin, 'admin')->getJson('/api/admin/meta/purchases');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Never leak access tokens
        $this->assertStringNotContainsString('EAAB_', $content);
        $this->assertStringNotContainsString('secret_value', $content);

        // Raw unhashed phone should be scrubbed in safe array
        $this->assertStringNotContainsString('"phone"', $content);
    }
}
