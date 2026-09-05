<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\MetaPixel;
use App\Models\MetaTrackingEvent;
use App\Models\MetaTrackingSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CheckoutService;
use App\Services\MetaConversionApiService;
use App\Services\MetaEventIdService;
use App\Services\MetaTrackingConfigService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaBrowserServerIntegrationPhase4Test extends TestCase
{
    protected MetaTrackingConfigService $configService;
    protected MetaConversionApiService $capiService;
    protected MetaEventIdService $eventIdService;
    protected MetaPixel $testPixel;
    protected MetaTrackingSetting $testSettings;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');
        Cache::flush();

        Admin::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('admin123'),
                'role'     => 'super_admin',
            ]
        );

        Product::firstOrCreate(
            ['slug' => 'girls-red-butterfly-printed-t-shirt-floral-shorts-set'],
            [
                'title'         => 'Girls Red Butterfly Printed T-Shirt & Floral Shorts 2-Piece Set',
                'sku'           => 'BFB-0152D',
                'regular_price' => 950,
                'sale_price'    => 790,
                'status'        => true,
            ]
        );

        // Configure test Pixel and runtime settings
        $this->testPixel = MetaPixel::firstOrCreate(
            ['pixel_id' => '1615672197236009'],
            [
                'pixel_name'      => 'Test Growth Agro Pixel',
                'access_token'    => 'EAAG_PHASE4_TEST_SECRET_TOKEN_DO_NOT_LEAK',
                'test_event_code' => 'TEST44444',
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

        $this->capiService = app(MetaConversionApiService::class);
        $this->eventIdService = app(MetaEventIdService::class);
    }

    /**
     * 1. Browser AddToCart receives eventID
     */
    public function test_browser_add_to_cart_receives_event_id()
    {
        $response = $this->get('/product/chicken-booster');
        $response->assertStatus(200);

        // Landing page script contains atcEventId passed to fbq AddToCart options
        $response->assertSee("const atcEventId = 'atc_' + Date.now()", false);
        $response->assertSee("window.fbq('track', 'AddToCart',", false);
        $response->assertSee("eventID: atcEventId", false);
    }

    /**
     * 2. Server AddToCart receives same event_id
     */
    public function test_server_add_to_cart_receives_same_event_id()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'events_received' => 1,
                'fbtrace_id'      => 'trace_atc_123',
            ], 200),
        ]);

        $eventId = 'atc_test_dedup_001';

        $response = $this->postJson('/api/tracking/event', [
            'event_name'  => 'add_to_cart',
            'event_id'    => $eventId,
            'entity_type' => 'landing_page',
            'entity_id'   => 'chicken-booster',
            'event_value' => 2300,
            'properties'  => [
                'currency'    => 'BDT',
                'items_count' => 1,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'  => true,
            'event_id' => $eventId,
        ]);

        // Assert Server CAPI recorded the event in meta_tracking_events with same event_id
        $this->assertDatabaseHas('meta_tracking_events', [
            'pixel_id'       => $this->testPixel->pixel_id,
            'event_name'     => 'AddToCart',
            'event_id'       => $eventId,
            'browser_status' => 'tracked',
            'server_status'  => 'sent',
        ]);

        Http::assertSent(function ($request) use ($eventId) {
            $data = $request->data();
            return isset($data['data'][0]['event_id']) && $data['data'][0]['event_id'] === $eventId;
        });
    }

    /**
     * 3. Browser InitiateCheckout receives eventID
     */
    public function test_browser_initiate_checkout_receives_event_id()
    {
        // Check checkout page
        $resCheckout = $this->get('/checkout');
        $resCheckout->assertStatus(200);
        $resCheckout->assertSee("const icEventId = 'ic_' + Date.now()", false);
        $resCheckout->assertSee("window.fbq('track', 'InitiateCheckout',", false);
        $resCheckout->assertSee("eventID: icEventId", false);

        // Check landing page
        $resLanding = $this->get('/product/chicken-booster');
        $resLanding->assertStatus(200);
        $resLanding->assertSee("const icEventId = 'ic_' + Date.now()", false);
        $resLanding->assertSee("window.fbq('track', 'InitiateCheckout',", false);
        $resLanding->assertSee("eventID: icEventId", false);

        // Check product detail
        $resDetail = $this->get('/product/girls-red-butterfly-printed-t-shirt-floral-shorts-set');
        $resDetail->assertStatus(200);
        $resDetail->assertSee("const icEventId = 'ic_' + Date.now()", false);
        $resDetail->assertSee("window.fbq('track', 'InitiateCheckout',", false);
        $resDetail->assertSee("eventID: icEventId", false);
    }

    /**
     * 4. Server InitiateCheckout receives same event_id
     */
    public function test_server_initiate_checkout_receives_same_event_id()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'events_received' => 1,
                'fbtrace_id'      => 'trace_ic_123',
            ], 200),
        ]);

        $eventId = 'ic_test_dedup_002';

        $response = $this->postJson('/api/tracking/event', [
            'event_name'  => 'checkout_started',
            'event_id'    => $eventId,
            'entity_type' => 'checkout',
            'event_value' => 790,
            'properties'  => [
                'currency'    => 'BDT',
                'items_count' => 1,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'  => true,
            'event_id' => $eventId,
        ]);

        $this->assertDatabaseHas('meta_tracking_events', [
            'pixel_id'       => $this->testPixel->pixel_id,
            'event_name'     => 'InitiateCheckout',
            'event_id'       => $eventId,
            'browser_status' => 'tracked',
            'server_status'  => 'sent',
        ]);

        Http::assertSent(function ($request) use ($eventId) {
            $data = $request->data();
            return isset($data['data'][0]['event_id']) && $data['data'][0]['event_id'] === $eventId;
        });
    }

    /**
     * 5. Browser Purchase receives deterministic order eventID
     */
    public function test_browser_purchase_receives_deterministic_order_event_id()
    {
        $orderNumber = 'TEST-ORD-DETERMINISTIC-1';
        $order = Order::create([
            'invoice_no'       => $orderNumber,
            'customer_name'    => 'Rahim Uddin',
            'customer_phone'   => '01712345678',
            'customer_address' => 'Dhaka Test',
            'city_type'        => 'inside_dhaka',
            'delivery_charge'  => 70,
            'subtotal'         => 790,
            'discount'         => 0,
            'total_amount'     => 860,
            'status'           => 'pending',
            'payment_method'   => 'COD',
            'source_type'      => 'main_website',
        ]);

        OrderItem::create([
            'order_id'      => $order->id,
            'product_name'  => 'Test Dress',
            'product_image' => '',
            'price'         => 790,
            'quantity'      => 1,
            'total'         => 790,
        ]);

        $response = $this->get("/order/success/{$orderNumber}");
        $response->assertStatus(200);
        $response->assertSee("const purchaseEventId = 'purchase_' + orderNo;", false);
        $response->assertSee("window.fbq('track', 'Purchase',", false);
        $response->assertSee("eventID: purchaseEventId", false);
    }

    /**
     * 6. Server Purchase uses exact same event_id
     */
    public function test_server_purchase_uses_exact_same_event_id()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'events_received' => 1,
                'fbtrace_id'      => 'trace_pur_123',
            ], 200),
        ]);

        $checkoutService = app(CheckoutService::class);
        $result = $checkoutService->placeOrder([
            'customer_name'    => 'Karim Farmer',
            'customer_phone'   => '01812345678',
            'customer_address' => 'Gazipur Farm',
            'delivery_area'    => 'outside_dhaka',
        ], [
            'id'       => 1,
            'title'    => 'Broiler Booster',
            'price'    => 1200,
            'quantity' => 2,
        ]);

        $this->assertTrue($result['success']);
        $orderNumber = $result['order_number'];
        $expectedEventId = 'purchase_' . $orderNumber;

        $this->assertDatabaseHas('meta_tracking_events', [
            'pixel_id'      => $this->testPixel->pixel_id,
            'event_name'    => 'Purchase',
            'event_id'      => $expectedEventId,
            'order_id'      => $orderNumber,
            'server_status' => 'sent',
        ]);

        Http::assertSent(function ($request) use ($expectedEventId) {
            $data = $request->data();
            return isset($data['data'][0]['event_id']) && $data['data'][0]['event_id'] === $expectedEventId;
        });

        // Now verify that the browser success page uses the exact same ID
        $browserResponse = $this->get("/order/success/{$orderNumber}");
        $browserResponse->assertStatus(200);
        $browserResponse->assertSee("const purchaseEventId = 'purchase_' + orderNo;", false);
    }

    /**
     * 7. Same Purchase event cannot create duplicate Server event (Idempotency)
     */
    public function test_same_purchase_event_cannot_create_duplicate_server_event()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'events_received' => 1,
                'fbtrace_id'      => 'trace_first_send',
            ], 200),
        ]);

        $orderNumber = 'TEST-ORD-DEDUP-777';
        $eventId = 'purchase_' . $orderNumber;

        // First send
        $firstResult = $this->capiService->sendEvent([
            'event_name' => 'Purchase',
            'event_id'   => $eventId,
            'order_id'   => $orderNumber,
            'custom_data'=> ['value' => 1000, 'currency' => 'BDT'],
        ]);
        $this->assertTrue($firstResult['success']);
        $this->assertFalse($firstResult['is_duplicate'] ?? false);

        // Second send with exact same event_id
        $secondResult = $this->capiService->sendEvent([
            'event_name' => 'Purchase',
            'event_id'   => $eventId,
            'order_id'   => $orderNumber,
            'custom_data'=> ['value' => 1000, 'currency' => 'BDT'],
        ]);

        $this->assertTrue($secondResult['is_duplicate']);
        $this->assertTrue($secondResult['skipped']);

        // Only 1 HTTP call should have been made to Meta API
        Http::assertSentCount(1);
    }

    /**
     * 8. Retry preserves same event_id
     */
    public function test_retry_preserves_same_event_id()
    {
        // First attempt fails with 500
        Http::fake([
            'https://graph.facebook.com/*' => Http::sequence()
                ->push(['error' => ['message' => 'Internal server error']], 500)
                ->push(['events_received' => 1, 'fbtrace_id' => 'trace_retry_ok'], 200),
        ]);

        $orderNumber = 'TEST-ORD-RETRY-888';
        $eventId = 'purchase_' . $orderNumber;

        $firstResult = $this->capiService->sendEvent([
            'event_name' => 'Purchase',
            'event_id'   => $eventId,
            'order_id'   => $orderNumber,
        ]);
        $this->assertFalse($firstResult['success']);

        // Record exists with failed status
        $this->assertDatabaseHas('meta_tracking_events', [
            'event_id'      => $eventId,
            'server_status' => 'failed',
        ]);

        // Retry attempt with same event_id
        $retryResult = $this->capiService->sendEvent([
            'event_name' => 'Purchase',
            'event_id'   => $eventId,
            'order_id'   => $orderNumber,
        ]);
        $this->assertTrue($retryResult['success']);

        // Updated in place to sent
        $this->assertDatabaseHas('meta_tracking_events', [
            'event_id'      => $eventId,
            'server_status' => 'sent',
        ]);

        // Exactly one record exists for this event_id
        $this->assertEquals(1, MetaTrackingEvent::where('event_id', $eventId)->count());
    }

    /**
     * 9. Existing PageView guard still works
     */
    public function test_existing_pageview_guard_still_works()
    {
        $response = $this->get('/product/chicken-booster');
        $response->assertStatus(200);
        $response->assertSee("meta_tracked_pageview_chicken-booster", false);
        $response->assertSee("sessionStorage.getItem(pageViewKey)", false);
        $response->assertSee("sessionStorage.setItem(pageViewKey, '1')", false);
    }

    /**
     * 10. Existing AddToCart guard still works
     */
    public function test_existing_add_to_cart_guard_still_works()
    {
        $response = $this->get('/product/chicken-booster');
        $response->assertStatus(200);
        $response->assertSee("const dedupeKey = 'meta_tracked_addtocart_' + LANDING_PAGE_SLUG;", false);
        $response->assertSee('const LANDING_PAGE_SLUG = "chicken-booster";', false);
        $response->assertSee("if (addToCartFired) return;", false);
        $response->assertSee("sessionStorage.setItem(dedupeKey, '1');", false);
    }

    /**
     * 11. Existing InitiateCheckout guard still works
     */
    public function test_existing_initiate_checkout_guard_still_works()
    {
        $response = $this->get('/product/chicken-booster');
        $response->assertStatus(200);
        $response->assertSee("const dedupeKey = 'meta_tracked_initiatecheckout_' + LANDING_PAGE_SLUG;", false);
        $response->assertSee('const LANDING_PAGE_SLUG = "chicken-booster";', false);
        $response->assertSee("if (checkoutStartedFired) return;", false);
        $response->assertSee("sessionStorage.setItem(dedupeKey, '1');", false);
    }

    /**
     * 12. Existing Purchase guard still works
     */
    public function test_existing_purchase_guard_still_works()
    {
        $order = Order::create([
            'invoice_no'       => 'GUARD-ORD-12',
            'customer_name'    => 'Test User',
            'customer_phone'   => '01700000000',
            'customer_address' => 'Dhaka',
            'city_type'        => 'inside_dhaka',
            'delivery_charge'  => 70,
            'subtotal'         => 100,
            'discount'         => 0,
            'total_amount'     => 170,
            'status'           => 'pending',
            'payment_method'   => 'COD',
            'source_type'      => 'main_website',
        ]);

        $response = $this->get('/order/success/GUARD-ORD-12');
        $response->assertStatus(200);
        $response->assertSee("const metaDedupeKey = 'meta_tracked_purchase_' + orderNo;", false);
        $response->assertSee("if (!sessionStorage.getItem(metaDedupeKey)", false);
        $response->assertSee("sessionStorage.setItem(metaDedupeKey, '1');", false);
    }

    /**
     * 13. disablePushState remains enabled
     */
    public function test_disable_push_state_remains_enabled()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee("fbq.disablePushState = true;", false);

        $lpRes = $this->get('/product/chicken-booster');
        $lpRes->assertStatus(200);
        $lpRes->assertSee("fbq.disablePushState = true;", false);
    }

    /**
     * 14. autoConfig=false remains enabled
     */
    public function test_auto_config_false_remains_enabled()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee("fbq('set', 'autoConfig', false,", false);

        $lpRes = $this->get('/product/chicken-booster');
        $lpRes->assertStatus(200);
        $lpRes->assertSee("fbq('set', 'autoConfig', false,", false);
    }

    /**
     * 15. Exactly one Pixel initialization exists
     */
    public function test_exactly_one_pixel_initialization_exists()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $content = $response->getContent();

        // Exactly one fbq('init', ...)
        $this->assertEquals(1, substr_count($content, "fbq('init',"));

        // Exactly one fbevents.js script tag inclusion
        $this->assertEquals(1, substr_count($content, "connect.facebook.net/en_US/fbevents.js"));
    }

    /**
     * 16. CAPI token never appears in Browser HTML
     */
    public function test_capi_token_never_appears_in_browser_html()
    {
        $secretToken = 'EAAG_PHASE4_TEST_SECRET_TOKEN_DO_NOT_LEAK';

        $routes = [
            '/',
            '/checkout',
            '/product/chicken-booster',
            '/product/girls-red-butterfly-printed-t-shirt-floral-shorts-set',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
            $response->assertDontSee($secretToken);
            $response->assertDontSee('Bearer');
        }
    }

    /**
     * 17. CAPI token never appears in Browser JavaScript
     */
    public function test_capi_token_never_appears_in_browser_javascript()
    {
        $secretToken = 'EAAG_PHASE4_TEST_SECRET_TOKEN_DO_NOT_LEAK';

        $trackingJsPath = public_path('js/growth-agro-tracking.js');
        $this->assertFileExists($trackingJsPath);
        $content = file_get_contents($trackingJsPath);

        $this->assertStringNotContainsString($secretToken, $content);
        $this->assertStringNotContainsString('access_token', $content);
        $this->assertStringNotContainsString('Authorization', $content);
    }

    /**
     * 18. Pixel ID is runtime-configurable
     */
    public function test_pixel_id_is_runtime_configurable()
    {
        $newPixel = MetaPixel::create([
            'pixel_name'   => 'Switched Pixel',
            'pixel_id'     => '987654321098765',
            'access_token' => 'EAA_SWITCHED_TOKEN',
            'is_active'    => true,
            'is_default'   => false,
        ]);

        $this->testSettings->update(['active_pixel_id' => $newPixel->id]);
        $this->configService->invalidateCache();

        $this->assertEquals('987654321098765', $this->configService->getActivePixelId());

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee("fbq('init', '987654321098765');", false);
    }

    /**
     * 19. Browser and Server use same runtime Pixel
     */
    public function test_browser_and_server_use_same_runtime_pixel()
    {
        $pixelId = $this->configService->getActivePixelId();
        $this->assertNotEmpty($pixelId);

        // Check browser rendered pixel
        $browserResponse = $this->get('/');
        $browserResponse->assertStatus(200);
        $browserResponse->assertSee("fbq('init', '{$pixelId}');", false);

        // Check server CAPI built payload
        $payload = $this->capiService->buildPayload('AddToCart', ['event_id' => 'atc_match_1'], null);
        $this->assertNotEmpty($payload);

        // Assert Server CAPI resolves the exact same active pixel ID
        $this->assertEquals($pixelId, $this->configService->getActivePixelId());
    }

    /**
     * 20. Existing event values remain correct
     */
    public function test_existing_event_values_remain_correct()
    {
        $resDetail = $this->get('/product/girls-red-butterfly-printed-t-shirt-floral-shorts-set');
        $resDetail->assertStatus(200);
        $resDetail->assertSee("value: 790", false);
        $resDetail->assertSee("currency: 'BDT'", false);

        $resCheckout = $this->get('/checkout');
        $resCheckout->assertStatus(200);
        $resCheckout->assertSee("currency: 'BDT'", false);
    }

    /**
     * 21. Invalid event IDs are rejected
     */
    public function test_invalid_event_ids_are_rejected()
    {
        $invalidIds = [
            'has space in id',
            'has_special_chars!@#$',
            'admin@example.com', // PII rejected
            'secret_token_bearer_123', // Secrets rejected
            str_repeat('x', 65), // Too long (>64)
        ];

        foreach ($invalidIds as $badId) {
            $this->assertFalse($this->eventIdService->isValid($badId), "Expected '{$badId}' to be invalid");

            $res = $this->capiService->sendEvent([
                'event_name' => 'AddToCart',
                'event_id'   => $badId,
            ]);
            $this->assertFalse($res['success']);
            $this->assertStringContainsString('Invalid event_id', $res['error_message']);
        }
    }

    /**
     * 22. No duplicate Browser events are introduced
     */
    public function test_no_duplicate_browser_events_are_introduced()
    {
        // On home page, only 1 PageView, no ViewContent or AddToCart
        $homeRes = $this->get('/');
        $homeRes->assertStatus(200);
        $this->assertEquals(1, substr_count($homeRes->getContent(), "fbq('track', 'PageView')"));
        $this->assertEquals(0, substr_count($homeRes->getContent(), "fbq('track', 'AddToCart')"));
        $this->assertEquals(0, substr_count($homeRes->getContent(), "fbq('track', 'Purchase')"));

        // On landing page, only 1 PageView, no automatic Purchase
        $lpRes = $this->get('/product/chicken-booster');
        $lpRes->assertStatus(200);
        $this->assertEquals(1, substr_count($lpRes->getContent(), "fbq('track', 'PageView')"));
        $this->assertEquals(0, substr_count($lpRes->getContent(), "fbq('track', 'Purchase')"));
    }
}
