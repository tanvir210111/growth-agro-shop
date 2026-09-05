<?php

namespace Tests\Feature;

use App\Models\LandingPage;
use App\Models\MetaPixel;
use App\Models\MetaTrackingEvent;
use App\Models\MetaTrackingSetting;
use App\Models\Order;
use App\Services\MetaConversionApiService;
use App\Services\MetaEventIdService;
use App\Services\MetaTrackingConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaLandingNodeCapiPhase6Test extends TestCase
{
    use RefreshDatabase;

    protected MetaPixel $testPixel;
    protected MetaTrackingSetting $testSettings;
    protected MetaTrackingConfigService $configService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testPixel = MetaPixel::updateOrCreate(
            ['pixel_id' => '1793041018387711'],
            [
                'pixel_name'      => 'Test Growth Agro Pixel',
                'access_token'    => 'EAAG_PHASE6_TEST_ACCESS_TOKEN_SECRET',
                'test_event_code' => 'TEST66666',
                'is_active'       => true,
                'is_default'      => true,
            ]
        );

        $this->testSettings = MetaTrackingSetting::current();
        $this->testSettings->update([
            'is_enabled'                        => true,
            'active_pixel_id'                   => $this->testPixel->id,
            'browser_pageview_enabled'          => true,
            'browser_add_to_cart_enabled'       => true,
            'browser_initiate_checkout_enabled' => true,
            'browser_purchase_enabled'          => true,
            'server_pageview_enabled'           => true,
            'server_add_to_cart_enabled'        => true,
            'server_initiate_checkout_enabled'  => true,
            'server_purchase_enabled'           => true,
            'purchase_event_mode'               => 'instant',
        ]);

        $this->configService = app(MetaTrackingConfigService::class);
        $this->configService->invalidateCache();
    }

    /**
     * 1. GET /api/internal/meta-tracking-config requires X-Internal-Secret
     */
    public function test_internal_meta_tracking_config_requires_secret()
    {
        // Missing secret -> 403
        $resNoSecret = $this->getJson('/api/internal/meta-tracking-config');
        $resNoSecret->assertStatus(403)
                    ->assertJson(['success' => false]);

        // Wrong secret -> 403
        $resWrongSecret = $this->getJson('/api/internal/meta-tracking-config', [
            'X-Internal-Secret' => 'completely-wrong-token-abc',
        ]);
        $resWrongSecret->assertStatus(403)
                       ->assertJson(['success' => false]);
    }

    /**
     * 2. GET /api/internal/meta-tracking-config returns runtime configuration with correct secret
     */
    public function test_internal_meta_tracking_config_returns_runtime_configuration()
    {
        $internalSecret = env('INTERNAL_API_SECRET', 'baby-fashion-internal-2024-secret');

        $response = $this->getJson('/api/internal/meta-tracking-config', [
            'X-Internal-Secret' => $internalSecret,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success'         => true,
                     'is_enabled'      => true,
                     'active_pixel_id' => '1793041018387711',
                     'access_token'    => 'EAAG_PHASE6_TEST_ACCESS_TOKEN_SECRET',
                     'test_event_code' => 'TEST66666',
                     'server_events'   => [
                         'pageview'          => true,
                         'add_to_cart'       => true,
                         'initiate_checkout' => true,
                         'purchase'          => true,
                     ],
                 ]);
    }

    /**
     * 3. Runtime configuration switching naturally updates the internal endpoint
     */
    public function test_internal_config_reflects_runtime_switching()
    {
        $internalSecret = env('INTERNAL_API_SECRET', 'baby-fashion-internal-2024-secret');

        $secondPixel = MetaPixel::create([
            'pixel_id'        => '9988776655443322',
            'pixel_name'      => 'Secondary Pixel',
            'access_token'    => 'EAAG_NEW_SECONDARY_TOKEN',
            'test_event_code' => 'TEST_SWITCHED',
            'is_active'       => true,
            'is_default'      => false,
        ]);

        $this->testSettings->update(['active_pixel_id' => $secondPixel->id]);
        $this->configService->invalidateCache();

        $response = $this->getJson('/api/internal/meta-tracking-config', [
            'X-Internal-Secret' => $internalSecret,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success'         => true,
                     'active_pixel_id' => '9988776655443322',
                     'access_token'    => 'EAAG_NEW_SECONDARY_TOKEN',
                     'test_event_code' => 'TEST_SWITCHED',
                 ]);
    }

    /**
     * 4. POST /api/tracking/event respects X-CAPI-Dispatched to avoid double server dispatch
     */
    public function test_tracking_controller_skips_capi_when_dispatched_by_node()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['events_received' => 1], 200),
        ]);

        $payload = [
            'event_name'  => 'add_to_cart',
            'event_id'    => 'atc_node_dispatched_123',
            'entity_type' => 'landing_page',
            'entity_id'   => 'chicken-booster',
            'event_value' => 750,
        ];

        // Send with X-CAPI-Dispatched: 1
        $res = $this->postJson('/api/tracking/event', $payload, [
            'X-CAPI-Dispatched' => '1',
        ]);

        $res->assertStatus(200);

        // Meta Graph API MUST NOT have been called by Laravel because Node already dispatched it
        Http::assertNothingSent();
    }

    /**
     * 5. POST /api/tracking/event sends CAPI when X-CAPI-Dispatched is NOT set
     */
    public function test_tracking_controller_sends_capi_when_not_dispatched_by_node()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['events_received' => 1], 200),
        ]);

        $payload = [
            'event_name'  => 'add_to_cart',
            'event_id'    => 'atc_laravel_direct_456',
            'entity_type' => 'landing_page',
            'entity_id'   => 'chicken-booster',
            'event_value' => 750,
        ];

        // Send without X-CAPI-Dispatched header
        $res = $this->postJson('/api/tracking/event', $payload);
        $res->assertStatus(200);

        // Laravel dispatches CAPI
        Http::assertSent(function ($request) {
            return $request->data()['data'][0]['event_id'] === 'atc_laravel_direct_456';
        });
    }

    /**
     * 5b. Regression Test: Landing AddToCart forwarded from Node bridge (X-CAPI-Dispatched: 0)
     * verifies full chain: preservation of exact event_id, test_event_code, and meta_tracking_events sent status.
     */
    public function test_landing_add_to_cart_forwarded_from_node_dispatches_capi_with_exact_event_id_and_sets_sent_status()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'events_received' => 1,
                'fbtrace_id'      => 'trace_atc_node_bridge_test',
            ], 200),
        ]);

        $freshEventId = 'atc_' . time() . '_uniq9876';

        $payload = [
            'event_name'  => 'add_to_cart',
            'event_id'    => $freshEventId,
            'entity_type' => 'landing_page',
            'entity_id'   => 'chicken-booster',
            'event_value' => 1250,
            'url'         => 'https://growthagro.shop/product/chicken-booster',
            'properties'  => [
                'items_count' => 1,
                'currency'    => 'BDT',
            ],
        ];

        // Simulate Node forwarding to Laravel with X-CAPI-Dispatched: 0
        $res = $this->withHeaders([
            'X-CAPI-Dispatched' => '0',
            'X-Forwarded-For'   => '103.145.120.45',
            'User-Agent'        => 'Mozilla/5.0 NodeBridgeTest',
            'Referer'           => 'https://growthagro.shop/product/chicken-booster',
        ])->postJson('/api/tracking/event', $payload);

        $res->assertStatus(200)
            ->assertJson([
                'success'  => true,
                'event_id' => $freshEventId,
            ]);

        // Verify Meta Graph API call
        Http::assertSent(function ($request) use ($freshEventId) {
            $data = $request->data();
            $event = $data['data'][0] ?? [];
            return $request->hasHeader('Authorization', 'Bearer EAAG_PHASE6_TEST_ACCESS_TOKEN_SECRET')
                && ($event['event_id'] ?? null) === $freshEventId
                && ($event['event_name'] ?? null) === 'AddToCart'
                && ($data['test_event_code'] ?? null) === 'TEST66666';
        });

        // Verify database persistence in meta_tracking_events
        $record = \App\Models\MetaTrackingEvent::where('event_id', $freshEventId)->first();
        $this->assertNotNull($record, 'Tracking record must be persisted in meta_tracking_events');
        $this->assertEquals('AddToCart', $record->event_name);
        $this->assertEquals('tracked', $record->browser_status);
        $this->assertEquals('sent', $record->server_status);
        $this->assertEquals(200, $record->response_code);
    }

    /**
     * 5b. Regression Test: _fbp and _fbc cookies are preserved across Laravel cookie encryption
     * and included in outbound Meta CAPI user_data.
     */
    public function test_tracking_event_preserves_meta_fbp_and_fbc_cookies_in_capi_user_data()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'events_received' => 1,
                'fbtrace_id'      => 'trace_cookie_preservation_test',
            ], 200),
        ]);

        $freshEventId = 'atc_' . time() . '_cookie_test';
        $testFbp = 'fb.1.1788600000000.987654321';
        $testFbc = 'fb.1.1788600000000.abcdef98765';

        $payload = [
            'event_name'  => 'add_to_cart',
            'event_id'    => $freshEventId,
            'entity_type' => 'landing_page',
            'entity_id'   => 'chicken-booster',
            'event_value' => 1250,
            'url'         => 'https://growthagro.shop/product/chicken-booster',
            'properties'  => [
                'items_count' => 1,
                'currency'    => 'BDT',
            ],
        ];

        // Send request with unencrypted _fbp and _fbc cookies (as sent by browser/Node bridge)
        $res = $this->withCredentials()->withUnencryptedCookies([
            '_fbp' => $testFbp,
            '_fbc' => $testFbc,
        ])->withHeaders([
            'X-CAPI-Dispatched' => '0',
            'X-Forwarded-For'   => '103.145.120.45',
            'User-Agent'        => 'Mozilla/5.0 CookieTestAgent',
            'Referer'           => 'https://growthagro.shop/product/chicken-booster',
        ])->postJson('/api/tracking/event', $payload);

        $res->assertStatus(200)
            ->assertJson([
                'success'  => true,
                'event_id' => $freshEventId,
            ]);

        // Verify that Meta Graph API received fbp and fbc in user_data
        Http::assertSent(function ($request) use ($freshEventId, $testFbp, $testFbc) {
            $data = $request->data();
            $event = $data['data'][0] ?? [];
            $userData = $event['user_data'] ?? [];

            $fbpPresent = !empty($userData['fbp']);
            $fbcPresent = !empty($userData['fbc']);
            $fbpMatches = ($userData['fbp'] ?? null) === $testFbp;
            $fbcMatches = ($userData['fbc'] ?? null) === $testFbc;

            return ($event['event_id'] ?? null) === $freshEventId
                && ($event['event_name'] ?? null) === 'AddToCart'
                && $fbpPresent
                && $fbcPresent
                && $fbpMatches
                && $fbcMatches;
        });

        // Verify database record has user_data with fbp and fbc
        $record = \App\Models\MetaTrackingEvent::where('event_id', $freshEventId)->first();
        $this->assertNotNull($record, 'Tracking record must be persisted in meta_tracking_events');
        $this->assertNotEmpty($record->user_data['fbp'] ?? null, 'fbp must be present in user_data');
        $this->assertNotEmpty($record->user_data['fbc'] ?? null, 'fbc must be present in user_data');
        $this->assertEquals($testFbp, $record->user_data['fbp']);
        $this->assertEquals($testFbc, $record->user_data['fbc']);
    }

    /**
     * 6. Landing success view outputs exact deterministic purchase_{orderNumber} eventID
     */
    public function test_landing_success_view_outputs_deterministic_purchase_event_id()
    {
        $lp = LandingPage::create([
            'name'         => 'Chicken Booster Pro',
            'slug'         => 'chicken-booster',
            'status'       => 'published',
            'product_id'   => 'chicken-booster',
            'product_name' => 'Chicken Booster',
            'content'      => ['title' => 'Chicken Booster'],
        ]);

        $order = Order::create([
            'invoice_no'       => 'CB-20260905-XYZ789',
            'customer_name'    => 'Landing Test User',
            'customer_phone'   => '01712345678',
            'customer_address' => 'Tejgaon, Dhaka',
            'city_type'        => 'inside_dhaka',
            'delivery_charge'  => 60,
            'subtotal'         => 1500,
            'discount'         => 0,
            'total_amount'     => 1560,
            'status'           => 'pending',
            'payment_method'   => 'COD',
            'source_type'      => 'LANDING',
            'landing_page'     => '/product/chicken-booster',
        ]);

        $response = $this->get("/product/{$lp->slug}/success/{$order->invoice_no}");
        $response->assertStatus(200);

        // Assert purchase_{orderNumber} is rendered for Browser Meta Pixel
        $response->assertSee("const purchaseEventId = 'purchase_' + orderNo;", false);
        $response->assertSee("eventID: purchaseEventId", false);
        $response->assertSee("orderNo = \"{$order->invoice_no}\"", false);
    }

    /**
     * 7. Cross-slug protection blocks unauthorized success page access (HTTP 404)
     */
    public function test_cross_slug_success_page_returns_404()
    {
        $lp1 = LandingPage::create([
            'name'         => 'Chicken Booster',
            'slug'         => 'chicken-booster',
            'status'       => 'published',
            'product_id'   => 'chicken-booster',
            'product_name' => 'Chicken Booster',
            'content'      => ['title' => 'Chicken Booster'],
        ]);

        $lp2 = LandingPage::create([
            'name'         => 'Fish Booster',
            'slug'         => 'fish-booster',
            'status'       => 'published',
            'product_id'   => 'fish-booster',
            'product_name' => 'Fish Booster',
            'content'      => ['title' => 'Fish Booster'],
        ]);

        $order = Order::create([
            'invoice_no'       => 'FB-20260905-999',
            'customer_name'    => 'Fish Customer',
            'customer_phone'   => '01812345678',
            'customer_address' => 'Khulna',
            'city_type'        => 'outside_dhaka',
            'delivery_charge'  => 120,
            'subtotal'         => 800,
            'discount'         => 0,
            'total_amount'     => 920,
            'status'           => 'pending',
            'payment_method'   => 'COD',
            'source_type'      => 'LANDING',
            'landing_page'     => '/product/fish-booster',
        ]);

        // Accessing Fish Booster order under chicken-booster slug must abort 404
        $resMismatch = $this->get("/product/chicken-booster/success/{$order->invoice_no}");
        $resMismatch->assertStatus(404);
    }

    /**
     * 8. Browser Meta Pixel guards remain intact (disablePushState, autoConfig=false, single init)
     */
    public function test_browser_pixel_guards_remain_intact()
    {
        $lp = LandingPage::create([
            'name'         => 'Chicken Booster Pro',
            'slug'         => 'chicken-booster-pixel-guard',
            'status'       => 'published',
            'product_id'   => 'chicken-booster',
            'product_name' => 'Chicken Booster',
            'content'      => ['title' => 'Chicken Booster'],
        ]);

        $response = $this->get("/product/{$lp->slug}");
        $response->assertStatus(200);

        $content = $response->getContent();

        // Exactly one fbq('init')
        $this->assertEquals(1, substr_count($content, "fbq('init'"));
        // disablePushState = true
        $this->assertStringContainsString("fbq.disablePushState = true", $content);
        // autoConfig = false
        $this->assertStringContainsString("fbq('set', 'autoConfig', false", $content);
    }
}
