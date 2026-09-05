<?php

namespace Tests\Feature;

use App\Models\MetaPixel;
use App\Models\MetaTrackingEvent;
use App\Models\MetaTrackingSetting;
use App\Services\MetaConversionApiService;
use App\Services\MetaTrackingConfigService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class MetaConversionApiPhase2Test extends TestCase
{
    protected MetaTrackingConfigService $configService;
    protected MetaConversionApiService $apiService;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');

        $this->configService = app(MetaTrackingConfigService::class);
        $this->apiService = app(MetaConversionApiService::class);

        // Clear runtime caches
        $this->configService->invalidateCache();
    }

    /**
     * Helper to create a dynamic test pixel without hardcoding any ID.
     */
    protected function createDynamicTestPixel(array $overrides = []): MetaPixel
    {
        // Generate dynamic 15-digit numeric ID
        $dynamicId = '88' . str_pad((string) mt_rand(1000000000000, 9999999999999), 13, '0', STR_PAD_LEFT);
        $dynamicToken = 'EAAG_test_dyn_' . bin2hex(random_bytes(16));

        $pixel = MetaPixel::create(array_merge([
            'pixel_name'      => 'Dynamic Test Pixel',
            'pixel_id'        => $dynamicId,
            'access_token'    => $dynamicToken,
            'test_event_code' => 'TEST_DYNAMIC_CODE',
            'is_active'       => true,
            'is_default'      => true,
        ], $overrides));

        $settings = MetaTrackingSetting::current();
        $settings->update([
            'active_pixel_id'                   => $pixel->id,
            'is_enabled'                        => true,
            'server_pageview_enabled'           => true,
            'server_add_to_cart_enabled'        => true,
            'server_initiate_checkout_enabled'  => true,
            'server_purchase_enabled'           => true,
            'browser_pageview_enabled'          => true,
            'browser_add_to_cart_enabled'       => true,
            'browser_initiate_checkout_enabled' => true,
            'browser_purchase_enabled'          => true,
        ]);

        $this->configService->invalidateCache();

        return $pixel;
    }

    /**
     * 1. Valid PageView payload sent to Meta CAPI.
     */
    public function test_sends_valid_pageview_event(): void
    {
        $pixel = $this->createDynamicTestPixel();

        Http::fake([
            "https://graph.facebook.com/*/{$pixel->pixel_id}/events" => Http::response([
                'events_received' => 1,
                'fbtrace_id'      => 'trace_pv_123',
            ], 200),
        ]);

        $result = $this->apiService->sendEvent([
            'event_name'       => 'PageView',
            'event_id'         => 'pv_evt_001',
            'event_source_url' => 'https://growthagro.shop/shop',
            'user_data'        => [
                'client_ip_address' => '103.145.1.2',
                'client_user_agent' => 'Mozilla/5.0 Test Agent',
            ],
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['http_status']);
        $this->assertEquals('PageView', $result['event_name']);
        $this->assertEquals('pv_evt_001', $result['event_id']);
        $this->assertEquals('trace_pv_123', $result['meta_event_id']);

        Http::assertSent(function ($request) use ($pixel) {
            $data = $request->data()['data'][0];
            return $data['event_name'] === 'PageView'
                && $data['event_id'] === 'pv_evt_001'
                && $data['event_source_url'] === 'https://growthagro.shop/shop'
                && $data['action_source'] === 'website'
                && $data['user_data']['client_ip_address'] === '103.145.1.2';
        });
    }

    /**
     * 2. Valid AddToCart payload with custom data.
     */
    public function test_sends_valid_add_to_cart_event(): void
    {
        $pixel = $this->createDynamicTestPixel();

        Http::fake([
            "https://graph.facebook.com/*/{$pixel->pixel_id}/events" => Http::response([
                'events_received' => 1,
                'fbtrace_id'      => 'trace_atc_456',
            ], 200),
        ]);

        $result = $this->apiService->sendEvent([
            'event_name'  => 'AddToCart',
            'event_id'    => 'atc_evt_002',
            'custom_data' => [
                'value'        => 790,
                'currency'     => 'BDT',
                'content_ids'  => ['BFB-0152D'],
                'content_type' => 'product',
            ],
        ]);

        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) {
            $data = $request->data()['data'][0];
            return $data['event_name'] === 'AddToCart'
                && $data['custom_data']['value'] == 790
                && $data['custom_data']['currency'] === 'BDT'
                && $data['custom_data']['content_ids'] === ['BFB-0152D'];
        });
    }

    /**
     * 3. Valid InitiateCheckout payload.
     */
    public function test_sends_valid_initiate_checkout_event(): void
    {
        $pixel = $this->createDynamicTestPixel();

        Http::fake([
            "https://graph.facebook.com/*/{$pixel->pixel_id}/events" => Http::response([
                'events_received' => 1,
            ], 200),
        ]);

        $result = $this->apiService->sendEvent([
            'event_name'  => 'InitiateCheckout',
            'event_id'    => 'ic_evt_003',
            'custom_data' => [
                'value'     => 1580,
                'currency'  => 'BDT',
                'num_items' => 2,
            ],
        ]);

        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) {
            $data = $request->data()['data'][0];
            return $data['event_name'] === 'InitiateCheckout'
                && $data['custom_data']['num_items'] === 2;
        });
    }

    /**
     * 4. Valid Purchase payload with hashed user data and custom data.
     */
    public function test_sends_valid_purchase_event_with_hashed_user_data(): void
    {
        $pixel = $this->createDynamicTestPixel();

        Http::fake([
            "https://graph.facebook.com/*/{$pixel->pixel_id}/events" => Http::response([
                'events_received' => 1,
                'fbtrace_id'      => 'trace_pur_789',
            ], 200),
        ]);

        $rawEmail = 'TestCustomer@example.com';
        $rawPhone = '01712345678';

        $result = $this->apiService->sendEvent([
            'event_name'  => 'Purchase',
            'event_id'    => 'purchase_ORD-999',
            'order_id'    => 'ORD-999',
            'user_data'   => [
                'email'             => $rawEmail,
                'phone'             => $rawPhone,
                'first_name'        => ' Rahim ',
                'city'              => 'Dhaka',
                'client_ip_address' => '127.0.0.1',
            ],
            'custom_data' => [
                'value'    => 2100.50,
                'currency' => 'BDT',
                'order_id' => 'ORD-999',
            ],
        ]);

        $this->assertTrue($result['success']);

        $expectedHashedEmail = hash('sha256', 'testcustomer@example.com');
        $expectedHashedPhone = hash('sha256', '01712345678');
        $expectedHashedName = hash('sha256', 'rahim');
        $expectedHashedCity = hash('sha256', 'dhaka');

        Http::assertSent(function ($request) use ($expectedHashedEmail, $expectedHashedPhone, $expectedHashedName, $expectedHashedCity) {
            $data = $request->data()['data'][0];
            return $data['event_name'] === 'Purchase'
                && $data['event_id'] === 'purchase_ORD-999'
                && $data['user_data']['em'][0] === $expectedHashedEmail
                && $data['user_data']['ph'][0] === $expectedHashedPhone
                && $data['user_data']['fn'][0] === $expectedHashedName
                && $data['user_data']['ct'][0] === $expectedHashedCity
                && $data['user_data']['client_ip_address'] === '127.0.0.1'
                && $data['custom_data']['value'] == 2100.50;
        });
    }

    /**
     * 5 & 6. Active Pixel ID and Access Token come from encrypted runtime configuration.
     */
    public function test_pixel_id_and_token_are_resolved_from_runtime_config(): void
    {
        $pixel = $this->createDynamicTestPixel();

        Http::fake([
            "https://graph.facebook.com/*/{$pixel->pixel_id}/events" => Http::response(['events_received' => 1], 200),
        ]);

        $this->apiService->sendEvent(['event_name' => 'PageView']);

        Http::assertSent(function ($request) use ($pixel) {
            // Must target active pixel ID endpoint
            $urlMatches = str_contains($request->url(), "/{$pixel->pixel_id}/events");
            // Must send Bearer token matching decrypted token
            $authHeader = $request->header('Authorization')[0] ?? '';
            $tokenMatches = $authHeader === "Bearer {$pixel->getDecryptedAccessToken()}";

            return $urlMatches && $tokenMatches;
        });
    }

    /**
     * 7. Test Event Code is included when configured.
     */
    public function test_test_event_code_included_when_configured(): void
    {
        $pixel = $this->createDynamicTestPixel(['test_event_code' => 'TEST_SPECIFIC_CODE_77']);

        Http::fake([
            "https://graph.facebook.com/*/{$pixel->pixel_id}/events" => Http::response(['events_received' => 1], 200),
        ]);

        $this->apiService->sendEvent(['event_name' => 'PageView']);

        Http::assertSent(function ($request) {
            return ($request->data()['test_event_code'] ?? null) === 'TEST_SPECIFIC_CODE_77';
        });
    }

    /**
     * 8. Test Event Code omitted when not configured.
     */
    public function test_test_event_code_omitted_when_null(): void
    {
        $pixel = $this->createDynamicTestPixel(['test_event_code' => null]);

        Http::fake([
            "https://graph.facebook.com/*/{$pixel->pixel_id}/events" => Http::response(['events_received' => 1], 200),
        ]);

        $this->apiService->sendEvent(['event_name' => 'PageView']);

        Http::assertSent(function ($request) {
            return !array_key_exists('test_event_code', $request->data());
        });
    }

    /**
     * 9. Disabled server event toggle skips CAPI dispatch without calling Meta.
     */
    public function test_disabled_server_event_is_skipped(): void
    {
        $this->createDynamicTestPixel();

        // Turn off server Purchase toggle
        $settings = MetaTrackingSetting::current();
        $settings->server_purchase_enabled = false;
        $settings->save();
        $this->configService->invalidateCache();

        Http::fake();

        $result = $this->apiService->sendEvent(['event_name' => 'Purchase']);

        $this->assertFalse($result['success']);
        $this->assertTrue($result['skipped']);
        $this->assertStringContainsString("disabled in settings", $result['error_message']);

        Http::assertNothingSent();
    }

    /**
     * 10. Invalid event name is safely rejected.
     */
    public function test_invalid_event_name_is_rejected(): void
    {
        $this->createDynamicTestPixel();

        Http::fake();

        $result = $this->apiService->sendEvent(['event_name' => 'NonExistentCustomEvent']);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString("Unsupported event name", $result['error_message']);

        Http::assertNothingSent();
    }

    /**
     * 11. Missing active Pixel configuration handled gracefully.
     */
    public function test_missing_pixel_configuration_handled(): void
    {
        MetaPixel::query()->delete();
        $settings = MetaTrackingSetting::current();
        $settings->update(['active_pixel_id' => null]);
        $this->configService->invalidateCache();

        Http::fake();

        $result = $this->apiService->sendEvent(['event_name' => 'PageView']);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString("No active Meta Pixel ID configured", $result['error_message']);
        Http::assertNothingSent();
    }

    /**
     * 12. Missing CAPI token handled gracefully.
     */
    public function test_missing_capi_token_handled(): void
    {
        $this->createDynamicTestPixel(['access_token' => null]);

        Http::fake();

        $result = $this->apiService->sendEvent(['event_name' => 'PageView']);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString("No Meta CAPI access token configured", $result['error_message']);
        Http::assertNothingSent();
    }

    /**
     * 13. Meta HTTP 200 handled.
     */
    public function test_meta_http_200_handled(): void
    {
        $pixel = $this->createDynamicTestPixel();

        Http::fake([
            "https://graph.facebook.com/*/{$pixel->pixel_id}/events" => Http::response([
                'events_received' => 1,
                'fbtrace_id'      => 'trace_ok_200',
            ], 200),
        ]);

        $result = $this->apiService->sendEvent(['event_name' => 'PageView']);

        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['http_status']);
        $this->assertEquals('trace_ok_200', $result['meta_event_id']);
        $this->assertEquals(1, $result['events_received']);
    }

    /**
     * 14. Meta HTTP 4xx handled and sanitized.
     */
    public function test_meta_http_4xx_handled(): void
    {
        $pixel = $this->createDynamicTestPixel();

        Http::fake([
            "https://graph.facebook.com/*/{$pixel->pixel_id}/events" => Http::response([
                'error' => [
                    'message'    => 'Invalid parameter format',
                    'type'       => 'OAuthException',
                    'code'       => 100,
                    'fbtrace_id' => 'trace_err_400',
                ]
            ], 400),
        ]);

        $result = $this->apiService->sendEvent(['event_name' => 'AddToCart']);

        $this->assertFalse($result['success']);
        $this->assertEquals(400, $result['http_status']);
        $this->assertStringContainsString('Invalid parameter format', $result['error_message']);
        $this->assertEquals('trace_err_400', $result['meta_event_id']);
    }

    /**
     * 15. Meta HTTP 5xx handled.
     */
    public function test_meta_http_5xx_handled(): void
    {
        $pixel = $this->createDynamicTestPixel();

        Http::fake([
            "https://graph.facebook.com/*/{$pixel->pixel_id}/events" => Http::response('Internal Server Error', 500),
        ]);

        $result = $this->apiService->sendEvent(['event_name' => 'InitiateCheckout']);

        $this->assertFalse($result['success']);
        $this->assertEquals(500, $result['http_status']);
        $this->assertNotNull($result['error_message']);
    }

    /**
     * 16. Connection Timeout handled without fatal exception.
     */
    public function test_meta_timeout_handled(): void
    {
        $pixel = $this->createDynamicTestPixel();

        Http::fake([
            "https://graph.facebook.com/*/{$pixel->pixel_id}/events" => fn () => throw new ConnectionException('cURL error 28: Operation timed out after 10000 milliseconds with 0 bytes received'),
        ]);

        $result = $this->apiService->sendEvent(['event_name' => 'Purchase']);

        $this->assertFalse($result['success']);
        $this->assertNull($result['http_status']);
        $this->assertStringContainsString('timed out', $result['error_message']);
    }

    /**
     * 17 & 18. Token and Authorization headers never appear in logged error messages or event tables.
     */
    public function test_token_and_auth_headers_never_appear_in_logs_or_records(): void
    {
        $sensitiveToken = 'EAAG_test_secret_leak_1234567890abcdef';
        $pixel = $this->createDynamicTestPixel(['access_token' => $sensitiveToken]);

        Http::fake([
            "https://graph.facebook.com/*/{$pixel->pixel_id}/events" => Http::response([
                'error' => [
                    'message' => "OAuthException with token {$sensitiveToken} and Bearer {$sensitiveToken}",
                ]
            ], 401),
        ]);

        $result = $this->apiService->sendEvent([
            'event_name' => 'Purchase',
            'event_id'   => 'sec_test_001',
            'order_id'   => 'ORD-SEC-001',
        ]);

        // Error message must not contain the sensitive token
        $this->assertStringNotContainsString($sensitiveToken, $result['error_message']);

        // Database record must not contain the sensitive token in response_body or error_message
        $record = MetaTrackingEvent::where('event_id', 'sec_test_001')->first();
        $this->assertNotNull($record);
        $this->assertStringNotContainsString($sensitiveToken, (string) $record->error_message);
        $this->assertStringNotContainsString($sensitiveToken, (string) $record->response_body);
    }

    /**
     * 19. Response sanitization works on error payloads.
     */
    public function test_response_sanitization_scrubs_secrets(): void
    {
        $sensitiveString = 'Error occurred for access_token=EAAG_super_sensitive_token_string_99999999';
        $scrubbed = MetaTrackingEvent::scrubSecrets($sensitiveString);

        $this->assertStringNotContainsString('EAAG_super_sensitive_token_string_99999999', $scrubbed);
        $this->assertStringContainsString('[REDACTED_CAPI_TOKEN]', $scrubbed);
    }

    /**
     * 20. Zero hardcoded numeric Pixel IDs in new service source code.
     */
    public function test_no_hardcoded_pixel_id_in_service_source(): void
    {
        $servicePath = app_path('Services/MetaConversionApiService.php');
        $this->assertFileExists($servicePath);

        $sourceCode = file_get_contents($servicePath);

        $this->assertDoesNotMatchRegularExpression(
            '/\b\d{15,18}\b/',
            $sourceCode,
            'MetaConversionApiService source code must NEVER hardcode any numeric Pixel ID!'
        );
    }

    /**
     * 21. Runtime Configuration Switching:
     * Configuration A -> Meta service targets Pixel A.
     * Switch database to Pixel B -> Meta service targets Pixel B without code change.
     */
    public function test_runtime_configuration_switching_without_code_change(): void
    {
        // 1. Setup Pixel A
        $pixelA = $this->createDynamicTestPixel(['pixel_name' => 'Pixel Alpha']);
        
        Http::fake([
            "https://graph.facebook.com/*/{$pixelA->pixel_id}/events" => Http::response(['events_received' => 1], 200),
        ]);

        $this->apiService->sendEvent(['event_name' => 'PageView']);

        Http::assertSent(fn ($req) => str_contains($req->url(), "/{$pixelA->pixel_id}/events"));

        // 2. Setup Pixel B and switch active configuration in database
        $pixelB = MetaPixel::create([
            'pixel_name'      => 'Pixel Beta',
            'pixel_id'        => '77' . str_pad((string) mt_rand(1000000000000, 9999999999999), 13, '0', STR_PAD_LEFT),
            'access_token'    => 'EAAG_beta_token_' . bin2hex(random_bytes(16)),
            'test_event_code' => 'TEST_BETA',
            'is_active'       => true,
            'is_default'      => false,
        ]);

        $settings = MetaTrackingSetting::current();
        $settings->active_pixel_id = $pixelB->id;
        $settings->save();

        // Invalidate cache (as happens when Admin saves settings)
        $this->configService->invalidateCache();

        Http::fake([
            "https://graph.facebook.com/*/{$pixelB->pixel_id}/events" => Http::response(['events_received' => 1], 200),
        ]);

        $this->apiService->sendEvent(['event_name' => 'PageView']);

        // Assert future events now target Pixel B
        Http::assertSent(fn ($req) => str_contains($req->url(), "/{$pixelB->pixel_id}/events"));
    }
}
