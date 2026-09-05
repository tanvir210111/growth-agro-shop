<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\MetaPixel;
use App\Models\MetaTrackingEvent;
use App\Models\MetaTrackingSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CheckoutService;
use App\Services\MetaCapiUserDataService;
use App\Services\MetaConversionApiService;
use App\Services\MetaEventIdService;
use App\Services\MetaTrackingConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaCapiAdvancedMatchingPhase5Test extends TestCase
{
    protected MetaCapiUserDataService $userDataService;
    protected MetaConversionApiService $capiService;
    protected MetaTrackingConfigService $configService;
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

        $this->testPixel = MetaPixel::firstOrCreate(
            ['pixel_id' => '1615672197236009'],
            [
                'pixel_name'      => 'Test Growth Agro Pixel',
                'access_token'    => 'EAAG_PHASE5_TEST_ACCESS_TOKEN_SECRET',
                'test_event_code' => 'TEST55555',
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

        $this->userDataService = app(MetaCapiUserDataService::class);
        $this->capiService = app(MetaConversionApiService::class);
        $this->eventIdService = app(MetaEventIdService::class);
    }

    /**
     * 1. Email normalization
     */
    public function test_email_normalization()
    {
        $normalized1 = $this->userDataService->normalizeEmail('  Customer.Test@Example.COM  ');
        $this->assertEquals('customer.test@example.com', $normalized1);

        $normalized2 = $this->userDataService->normalizeEmail(" user.name@domain.com \n");
        $this->assertEquals('user.name@domain.com', $normalized2);

        $this->assertNull($this->userDataService->normalizeEmail(''));
        $this->assertNull($this->userDataService->normalizeEmail('not-an-email'));
    }

    /**
     * 2. Email SHA-256 hashing
     */
    public function test_email_sha256_hashing()
    {
        $email = 'Customer.Test@Example.COM';
        $normalized = $this->userDataService->normalizeEmail($email);
        $expectedHash = hash('sha256', 'customer.test@example.com');

        $result = $this->userDataService->buildUserData(['email' => $email]);

        $this->assertArrayHasKey('em', $result);
        $this->assertIsArray($result['em']);
        $this->assertEquals([$expectedHash], $result['em']);
    }

    /**
     * 3. Phone normalization
     */
    public function test_phone_normalization()
    {
        // 11-digit local format with dashes/formatting stripped, digits preserved cleanly
        $norm1 = $this->userDataService->normalizePhone('01712-345678');
        $this->assertEquals('01712345678', $norm1);

        // With +88 prefix, spaces and dashes: digits extracted accurately
        $norm2 = $this->userDataService->normalizePhone('  +88 01812-345 678 ');
        $this->assertEquals('8801812345678', $norm2);

        // International format preserved cleanly
        $norm3 = $this->userDataService->normalizePhone('8801912345678');
        $this->assertEquals('8801912345678', $norm3);

        // Too short or invalid
        $this->assertNull($this->userDataService->normalizePhone('123'));
        $this->assertNull($this->userDataService->normalizePhone(''));
    }

    /**
     * 4. Phone SHA-256 hashing
     */
    public function test_phone_sha256_hashing()
    {
        $phone = '01712-345678';
        $normalized = $this->userDataService->normalizePhone($phone);
        $expectedHash = hash('sha256', '01712345678');

        $result = $this->userDataService->buildUserData(['phone' => $phone]);

        $this->assertArrayHasKey('ph', $result);
        $this->assertEquals([$expectedHash], $result['ph']);
    }

    /**
     * 5. First name normalization and hashing
     */
    public function test_first_name_normalization_and_hashing()
    {
        $norm = $this->userDataService->normalizeName('  Mohammad!  ');
        $this->assertEquals('mohammad', $norm);

        $expectedHash = hash('sha256', 'mohammad');
        $result = $this->userDataService->buildUserData(['first_name' => '  Mohammad!  ']);

        $this->assertArrayHasKey('fn', $result);
        $this->assertEquals([$expectedHash], $result['fn']);
    }

    /**
     * 6. Last name normalization and hashing
     */
    public function test_last_name_normalization_and_hashing()
    {
        $norm = $this->userDataService->normalizeName('  Rahman #1  ');
        $this->assertEquals('rahman 1', $norm);

        $expectedHash = hash('sha256', 'rahman 1');
        $result = $this->userDataService->buildUserData(['last_name' => '  Rahman #1  ']);

        $this->assertArrayHasKey('ln', $result);
        $this->assertEquals([$expectedHash], $result['ln']);
    }

    /**
     * 7. City normalization and hashing
     */
    public function test_city_normalization_and_hashing()
    {
        $norm = $this->userDataService->normalizeCity('inside_dhaka');
        $this->assertEquals('dhaka', $norm);

        $expectedHash = hash('sha256', 'dhaka');
        $result = $this->userDataService->buildUserData(['city' => 'inside_dhaka']);

        $this->assertArrayHasKey('ct', $result);
        $this->assertEquals([$expectedHash], $result['ct']);
    }

    /**
     * 8. State normalization and hashing
     */
    public function test_state_normalization_and_hashing()
    {
        $norm = $this->userDataService->normalizeState('  Dhaka Division  ');
        $this->assertEquals('dhaka division', $norm);

        $expectedHash = hash('sha256', 'dhaka division');
        $result = $this->userDataService->buildUserData(['state' => '  Dhaka Division  ']);

        $this->assertArrayHasKey('st', $result);
        $this->assertEquals([$expectedHash], $result['st']);
    }

    /**
     * 9. Country normalization and hashing
     */
    public function test_country_normalization_and_hashing()
    {
        $norm = $this->userDataService->normalizeCountry('Bangladesh');
        $this->assertEquals('bd', $norm);

        $expectedHash = hash('sha256', 'bd');
        $result = $this->userDataService->buildUserData(['country' => 'Bangladesh']);

        $this->assertArrayHasKey('country', $result);
        $this->assertEquals([$expectedHash], $result['country']);
    }

    /**
     * 10. External_id handling
     */
    public function test_external_id_handling()
    {
        // Valid external ID (e.g. order number or customer ID)
        $norm = $this->userDataService->normalizeExternalId('GA-ORD-2026-999');
        $this->assertEquals('ga-ord-2026-999', $norm);

        $expectedHash = hash('sha256', 'ga-ord-2026-999');
        $result = $this->userDataService->buildUserData(['external_id' => 'GA-ORD-2026-999']);
        $this->assertArrayHasKey('external_id', $result);
        $this->assertEquals([$expectedHash], $result['external_id']);

        // Strictly rejects raw email or raw phone as external_id
        $this->assertNull($this->userDataService->normalizeExternalId('customer@example.com'));
        $this->assertNull($this->userDataService->normalizeExternalId('01712345678'));
        $this->assertNull($this->userDataService->normalizeExternalId('EAAG_token_secret_xyz'));
    }

    /**
     * 11. fbp remains un-hashed
     */
    public function test_fbp_remains_un_hashed()
    {
        $fbpValue = 'fb.1.1558571054389.1098115397';
        $result = $this->userDataService->buildUserData(['fbp' => $fbpValue]);

        $this->assertArrayHasKey('fbp', $result);
        $this->assertEquals($fbpValue, $result['fbp']);
        $this->assertNotEquals(hash('sha256', $fbpValue), $result['fbp']);
    }

    /**
     * 12. fbc remains un-hashed
     */
    public function test_fbc_remains_un_hashed()
    {
        $fbcValue = 'fb.1.1554763741205.AbCdEfGhIjKlMnOpQrStUvWxYz1234567890';
        $result = $this->userDataService->buildUserData(['fbc' => $fbcValue]);

        $this->assertArrayHasKey('fbc', $result);
        $this->assertEquals($fbcValue, $result['fbc']);
        $this->assertNotEquals(hash('sha256', $fbcValue), $result['fbc']);
    }

    /**
     * 13. client_ip_address remains correctly represented
     */
    public function test_client_ip_address_remains_correctly_represented()
    {
        $ip = '103.205.180.25';
        $result = $this->userDataService->buildUserData(['client_ip_address' => $ip]);

        $this->assertArrayHasKey('client_ip_address', $result);
        $this->assertEquals($ip, $result['client_ip_address']);
        $this->assertNotEquals(hash('sha256', $ip), $result['client_ip_address']);
    }

    /**
     * 14. client_user_agent remains correctly represented
     */
    public function test_client_user_agent_remains_correctly_represented()
    {
        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
        $result = $this->userDataService->buildUserData(['client_user_agent' => $ua]);

        $this->assertArrayHasKey('client_user_agent', $result);
        $this->assertEquals($ua, $result['client_user_agent']);
        $this->assertNotEquals(hash('sha256', $ua), $result['client_user_agent']);
    }

    /**
     * 15. Unavailable fields are omitted
     */
    public function test_unavailable_fields_are_omitted()
    {
        $result = $this->userDataService->buildUserData([
            'phone' => '01712345678',
        ]);

        $this->assertArrayHasKey('ph', $result);
        $this->assertArrayNotHasKey('em', $result);
        $this->assertArrayNotHasKey('fn', $result);
        $this->assertArrayNotHasKey('ln', $result);
        $this->assertArrayNotHasKey('ct', $result);
        $this->assertArrayNotHasKey('st', $result);
        $this->assertArrayNotHasKey('country', $result);
        $this->assertArrayNotHasKey('external_id', $result);
        $this->assertArrayNotHasKey('fbp', $result);
        $this->assertArrayNotHasKey('fbc', $result);
    }

    /**
     * 16. Empty fields are omitted
     */
    public function test_empty_fields_are_omitted()
    {
        $result = $this->userDataService->buildUserData([
            'email'             => '',
            'phone'             => null,
            'first_name'        => '   ',
            'client_ip_address' => '',
            'fbp'               => null,
        ]);

        $this->assertEmpty($result);
    }

    /**
     * 17. Raw email never appears in user_data output
     */
    public function test_raw_email_never_appears_in_user_data_output()
    {
        $rawEmail = 'sensitive_customer@growthagro.shop';
        $result = $this->userDataService->buildUserData(['email' => $rawEmail]);

        $json = json_encode($result);
        $this->assertStringNotContainsString($rawEmail, $json);
        $this->assertStringContainsString(hash('sha256', $rawEmail), $json);
    }

    /**
     * 18. Raw phone never appears in user_data output
     */
    public function test_raw_phone_never_appears_in_user_data_output()
    {
        $rawPhone = '01812345678';
        $result = $this->userDataService->buildUserData(['phone' => $rawPhone]);

        $json = json_encode($result);
        $this->assertStringNotContainsString($rawPhone, $json);
        $this->assertStringContainsString(hash('sha256', '01812345678'), $json);
    }

    /**
     * 19. Raw PII never appears in MetaTrackingEvent records
     */
    public function test_raw_pii_never_appears_in_meta_tracking_event_records()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'events_received' => 1,
                'fbtrace_id'      => 'trace_adv_19',
            ], 200),
        ]);

        $rawPhone = '01799887766';
        $rawEmail = 'secret_farmer@agri.com';
        $rawName = 'Abdul Karim';

        $res = $this->capiService->sendEvent([
            'event_name' => 'Purchase',
            'event_id'   => 'purchase_PII_TEST_19',
            'order_id'   => 'PII_TEST_19',
            'user_data'  => [
                'phone'      => $rawPhone,
                'email'      => $rawEmail,
                'first_name' => $rawName,
            ],
            'custom_data'=> ['value' => 500, 'currency' => 'BDT'],
        ]);

        $this->assertTrue($res['success']);

        $eventRecord = MetaTrackingEvent::where('event_id', 'purchase_PII_TEST_19')->first();
        $this->assertNotNull($eventRecord);

        $storedJson = json_encode($eventRecord->user_data);
        $this->assertStringNotContainsString($rawPhone, $storedJson);
        $this->assertStringNotContainsString($rawEmail, $storedJson);
        $this->assertStringNotContainsString($rawName, $storedJson);

        // Assert hashed representations exist
        $this->assertStringContainsString(hash('sha256', '01799887766'), $storedJson);
        $this->assertStringContainsString(hash('sha256', 'secret_farmer@agri.com'), $storedJson);
    }

    /**
     * 20. CAPI token never appears in user_data
     */
    public function test_capi_token_never_appears_in_user_data()
    {
        $token = 'EAAG_PHASE5_TEST_ACCESS_TOKEN_SECRET';
        $result = $this->userDataService->buildUserData([
            'phone'        => '01712345678',
            'access_token' => $token,
            'token'        => $token,
        ]);

        $json = json_encode($result);
        $this->assertStringNotContainsString($token, $json);
        $this->assertArrayNotHasKey('access_token', $result);
        $this->assertArrayNotHasKey('token', $result);
    }

    /**
     * 21. Authorization header never appears in user_data
     */
    public function test_authorization_header_never_appears_in_user_data()
    {
        $result = $this->userDataService->buildUserData([
            'phone'         => '01712345678',
            'Authorization' => 'Bearer EAAG_SECRET_TOKEN',
            'authorization' => 'Bearer EAAG_SECRET_TOKEN',
        ]);

        $json = json_encode($result);
        $this->assertStringNotContainsString('Bearer', $json);
        $this->assertArrayNotHasKey('Authorization', $result);
        $this->assertArrayNotHasKey('authorization', $result);
    }

    /**
     * 22. Purchase can build user_data from authoritative order information
     */
    public function test_purchase_can_build_user_data_from_authoritative_order_information()
    {
        $order = Order::create([
            'invoice_no'       => 'GA-AUTH-ORD-22',
            'customer_name'    => 'Kabir Hossain',
            'customer_phone'   => '01612345678',
            'customer_address' => 'Dhaka Farm Road',
            'city_type'        => 'inside_dhaka',
            'delivery_charge'  => 70,
            'subtotal'         => 1500,
            'discount'         => 0,
            'total_amount'     => 1570,
            'status'           => 'pending',
            'payment_method'   => 'COD',
            'source_type'      => 'main_website',
        ]);

        $userData = $this->userDataService->fromOrder($order);

        $this->assertArrayHasKey('ph', $userData);
        $this->assertEquals([hash('sha256', '01612345678')], $userData['ph']);

        $this->assertArrayHasKey('fn', $userData);
        $this->assertEquals([hash('sha256', 'kabir')], $userData['fn']);

        $this->assertArrayHasKey('ln', $userData);
        $this->assertEquals([hash('sha256', 'hossain')], $userData['ln']);

        $this->assertArrayHasKey('ct', $userData);
        $this->assertEquals([hash('sha256', 'dhaka')], $userData['ct']);

        $this->assertArrayHasKey('country', $userData);
        $this->assertEquals([hash('sha256', 'bd')], $userData['country']);

        $this->assertArrayHasKey('external_id', $userData);
        $this->assertEquals([hash('sha256', 'ga-auth-ord-22')], $userData['external_id']);
    }

    /**
     * 23. AddToCart works without customer PII
     */
    public function test_add_to_cart_works_without_customer_pii()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['events_received' => 1], 200),
        ]);

        $res = $this->capiService->sendEvent([
            'event_name' => 'AddToCart',
            'event_id'   => 'atc_no_pii_23',
            'user_data'  => [
                'client_ip_address' => '127.0.0.1',
                'client_user_agent' => 'Mozilla/5.0 Test Agent',
            ],
            'custom_data'=> ['value' => 500, 'currency' => 'BDT'],
        ]);

        $this->assertTrue($res['success']);

        Http::assertSent(function ($request) {
            $user = $request->data()['data'][0]['user_data'];
            $this->assertEquals('127.0.0.1', $user['client_ip_address']);
            $this->assertEquals('Mozilla/5.0 Test Agent', $user['client_user_agent']);
            $this->assertArrayNotHasKey('ph', $user);
            $this->assertArrayNotHasKey('em', $user);
            return true;
        });
    }

    /**
     * 24. InitiateCheckout works without customer PII
     */
    public function test_initiate_checkout_works_without_customer_pii()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['events_received' => 1], 200),
        ]);

        $res = $this->capiService->sendEvent([
            'event_name' => 'InitiateCheckout',
            'event_id'   => 'ic_no_pii_24',
            'user_data'  => [
                'client_ip_address' => '192.168.1.1',
            ],
            'custom_data'=> ['value' => 790, 'currency' => 'BDT'],
        ]);

        $this->assertTrue($res['success']);

        Http::assertSent(function ($request) {
            $user = $request->data()['data'][0]['user_data'];
            $this->assertEquals('192.168.1.1', $user['client_ip_address']);
            $this->assertArrayNotHasKey('ph', $user);
            $this->assertArrayNotHasKey('em', $user);
            return true;
        });
    }

    /**
     * 25. Existing event_id remains unchanged
     */
    public function test_existing_event_id_remains_unchanged()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response(['events_received' => 1], 200),
        ]);

        $eventId = 'ic_custom_phase5_id_25';

        $this->capiService->sendEvent([
            'event_name' => 'InitiateCheckout',
            'event_id'   => $eventId,
            'user_data'  => ['phone' => '01700000000'],
        ]);

        Http::assertSent(function ($request) use ($eventId) {
            return $request->data()['data'][0]['event_id'] === $eventId;
        });
    }

    /**
     * 26. Existing Purchase event_id remains unchanged
     */
    public function test_existing_purchase_event_id_remains_unchanged()
    {
        $orderNumber = 'GA-ORD-CHECK-26';
        $expectedPurchaseEventId = 'purchase_' . $orderNumber;

        $generated = $this->eventIdService->generatePurchaseEventId($orderNumber);
        $this->assertEquals($expectedPurchaseEventId, $generated);
    }

    /**
     * 27. Runtime Pixel configuration remains unchanged
     */
    public function test_runtime_pixel_configuration_remains_unchanged()
    {
        $activePixel = $this->configService->getActivePixel();
        $this->assertNotNull($activePixel);
        $this->assertEquals('1615672197236009', $activePixel->pixel_id);
        $this->assertEquals('EAAG_PHASE5_TEST_ACCESS_TOKEN_SECRET', $this->configService->getCapiAccessToken());
    }

    /**
     * 28. MetaConversionApiService still sends valid payload
     */
    public function test_meta_conversion_api_service_still_sends_valid_payload()
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'events_received' => 1,
                'fbtrace_id'      => 'trace_valid_payload_28',
            ], 200),
        ]);

        $res = $this->capiService->sendEvent([
            'event_name' => 'Purchase',
            'event_id'   => 'purchase_VALID_28',
            'order_id'   => 'VALID_28',
            'user_data'  => [
                'phone'             => '01711122233',
                'first_name'        => 'Kamal',
                'client_ip_address' => '127.0.0.1',
                'fbp'               => 'fb.1.12345.67890',
            ],
            'custom_data'=> [
                'currency' => 'BDT',
                'value'    => 2000,
            ],
        ]);

        $this->assertTrue($res['success']);
        $this->assertEquals(1, $res['events_received']);

        Http::assertSent(function ($request) {
            $data = $request->data()['data'][0];
            $this->assertEquals('Purchase', $data['event_name']);
            $this->assertEquals('purchase_VALID_28', $data['event_id']);
            $this->assertEquals('fb.1.12345.67890', $data['user_data']['fbp']);
            $this->assertEquals([hash('sha256', '01711122233')], $data['user_data']['ph']);
            $this->assertEquals(2000, $data['custom_data']['value']);
            return true;
        });
    }

    /**
     * 29. No duplicate hashing
     */
    public function test_no_duplicate_hashing()
    {
        $singleHash = hash('sha256', '8801711122233');

        // Pass an already-hashed phone string
        $result = $this->userDataService->buildUserData(['phone' => $singleHash]);

        $this->assertArrayHasKey('ph', $result);
        $this->assertEquals([$singleHash], $result['ph']);

        // Verify it was NOT hashed again
        $this->assertNotEquals([hash('sha256', $singleHash)], $result['ph']);
    }

    /**
     * 30. No PII appears in logs or errors
     */
    public function test_no_pii_appears_in_logs_or_errors()
    {
        $rawEmail = 'secret_user@example.com';
        $rawPhone = '01712345678';

        // Trigger an HTTP failure
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'error' => [
                    'message' => "Invalid request for {$rawEmail} and {$rawPhone}",
                ]
            ], 400),
        ]);

        $res = $this->capiService->sendEvent([
            'event_name' => 'AddToCart',
            'event_id'   => 'atc_error_30',
            'user_data'  => [
                'email' => $rawEmail,
                'phone' => $rawPhone,
            ],
        ]);

        $this->assertFalse($res['success']);

        $eventRecord = MetaTrackingEvent::where('event_id', 'atc_error_30')->first();
        $this->assertNotNull($eventRecord);

        // Stored user_data contains only hashes
        $userDataStr = json_encode($eventRecord->user_data);
        $this->assertStringNotContainsString($rawEmail, $userDataStr);
        $this->assertStringNotContainsString($rawPhone, $userDataStr);

        // Scrubbed error message
        $this->assertStringNotContainsString($this->testPixel->getDecryptedAccessToken(), $eventRecord->error_message ?? '');
    }
}
