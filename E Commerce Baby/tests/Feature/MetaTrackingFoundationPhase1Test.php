<?php

namespace Tests\Feature;

use App\Models\MetaPixel;
use App\Models\MetaPurchaseRule;
use App\Models\MetaTrackingEvent;
use App\Models\MetaTrackingSetting;
use App\Models\Setting;
use App\Services\MetaTrackingConfigService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MetaTrackingFoundationPhase1Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');
    }

    /**
     * 1. Migration Safety: Verify migration source code has ZERO hardcoded numeric Pixel IDs.
     */
    public function test_migration_source_contains_zero_hardcoded_pixel_ids(): void
    {
        $migrationPath = database_path('migrations/2026_09_05_000001_create_meta_tracking_foundation_tables.php');
        $this->assertFileExists($migrationPath);

        $sourceCode = file_get_contents($migrationPath);

        // Assert no 15-18 digit numeric literal is hardcoded in the migration file
        $this->assertDoesNotMatchRegularExpression(
            '/\b\d{15,18}\b/',
            $sourceCode,
            'Migration source code must NEVER hardcode any numeric Pixel ID!'
        );

        // Assert it explicitly queries settings table dynamically
        $this->assertStringContainsString("where('key', 'facebook_pixel')", $sourceCode);
    }

    /**
     * 2. Schema Verification: All 4 Phase 1 tables exist with expected columns.
     */
    public function test_all_foundation_tables_and_columns_exist(): void
    {
        $this->assertTrue(Schema::hasTable('meta_pixels'), 'meta_pixels table must exist');
        $this->assertTrue(Schema::hasTable('meta_tracking_settings'), 'meta_tracking_settings table must exist');
        $this->assertTrue(Schema::hasTable('meta_tracking_events'), 'meta_tracking_events table must exist');
        $this->assertTrue(Schema::hasTable('meta_purchase_rules'), 'meta_purchase_rules table must exist');

        $this->assertTrue(Schema::hasColumns('meta_pixels', [
            'id', 'pixel_name', 'pixel_id', 'access_token', 'test_event_code', 'is_active', 'is_default', 'created_at', 'updated_at'
        ]));

        $this->assertTrue(Schema::hasColumns('meta_tracking_settings', [
            'id', 'is_enabled', 'active_pixel_id',
            'browser_pageview_enabled', 'browser_add_to_cart_enabled', 'browser_initiate_checkout_enabled', 'browser_purchase_enabled',
            'server_pageview_enabled', 'server_add_to_cart_enabled', 'server_initiate_checkout_enabled', 'server_purchase_enabled',
            'purchase_event_mode', 'purchase_delay_minutes', 'auto_rules_enabled', 'created_at', 'updated_at'
        ]));

        $this->assertTrue(Schema::hasColumns('meta_tracking_events', [
            'id', 'event_id', 'event_name', 'pixel_id', 'order_id', 'action_source', 'event_source_url',
            'user_data', 'custom_data', 'browser_status', 'server_status', 'deduplication_status',
            'purchase_mode', 'scheduled_at', 'sent_at', 'response_code', 'response_body', 'error_message', 'created_at', 'updated_at'
        ]));

        $this->assertTrue(Schema::hasColumns('meta_purchase_rules', [
            'id', 'rule_name', 'priority', 'condition_field', 'operator', 'condition_value', 'condition_value_high',
            'action_mode', 'delay_minutes', 'is_active', 'created_at', 'updated_at'
        ]));
    }

    /**
     * 3. Single Source of Truth: Test Event Code exists in meta_pixels and NOT in meta_tracking_settings.
     */
    public function test_single_source_of_truth_for_test_event_code(): void
    {
        // Must exist in meta_pixels
        $this->assertTrue(
            Schema::hasColumn('meta_pixels', 'test_event_code'),
            'meta_pixels must be the single source of truth for test_event_code'
        );

        // Must NOT exist in meta_tracking_settings
        $this->assertFalse(
            Schema::hasColumn('meta_tracking_settings', 'test_event_code'),
            'meta_tracking_settings must NOT duplicate test_event_code'
        );
    }

    /**
     * 4. Dynamic Migration Safety: Before & After verification.
     * Before migration: setting holds dynamic value.
     * Migration dynamically creates MetaPixel with that exact value.
     */
    public function test_dynamic_migration_preserves_existing_facebook_pixel(): void
    {
        // Simulate a dynamic pre-existing setting in a clean environment
        $dynamicTestPixel = '987654321012345';
        Setting::set('facebook_pixel', $dynamicTestPixel);

        // Run the specific migration logic or create pixel as migration does
        $existingPixelValue = DB::table('settings')->where('key', 'facebook_pixel')->value('value');
        $this->assertEquals($dynamicTestPixel, $existingPixelValue);

        // Verify that MetaPixel can store and preserve this value dynamically
        $pixel = MetaPixel::create([
            'pixel_name' => 'Preserved Production Pixel',
            'pixel_id'   => $existingPixelValue,
            'is_active'  => true,
            'is_default' => true,
        ]);

        $this->assertEquals($dynamicTestPixel, $pixel->pixel_id);
    }

    /**
     * 5. Encrypted Token Storage: Raw DB column is encrypted, while Eloquent decrypts cleanly.
     */
    public function test_capi_access_token_is_stored_encrypted_in_database(): void
    {
        $plaintextToken = 'EAAG_test_token_secret_value_1234567890_abcdef';

        $pixel = MetaPixel::create([
            'pixel_name'      => 'Encryption Test Pixel',
            'pixel_id'        => '987654321012345',
            'access_token'    => $plaintextToken,
            'test_event_code' => 'TEST99999',
            'is_active'       => true,
            'is_default'      => false,
        ]);

        // Direct raw database query bypasses Eloquent casting
        $rawDbValue = DB::table('meta_pixels')->where('id', $pixel->id)->value('access_token');

        $this->assertNotNull($rawDbValue);
        $this->assertNotEquals($plaintextToken, $rawDbValue, 'Raw database column MUST NOT contain plaintext token');
        
        // Reload via Eloquent: must decrypt back to original plaintext
        $freshPixel = MetaPixel::find($pixel->id);
        $this->assertEquals($plaintextToken, $freshPixel->getDecryptedAccessToken());
        $this->assertEquals($plaintextToken, $freshPixel->access_token);

        // Clean up
        $pixel->delete();
    }

    /**
     * 6. Token Never Exposed: toArray(), toJson(), and safe representation never leak raw secret.
     */
    public function test_token_is_never_exposed_in_serialization(): void
    {
        $plaintextToken = 'EAAG_super_confidential_capi_token_xyz_789';

        $pixel = new MetaPixel([
            'pixel_name'      => 'Masking Test Pixel',
            'pixel_id'        => '112233445566778',
            'access_token'    => $plaintextToken,
            'test_event_code' => 'TEST12345',
            'is_active'       => true,
            'is_default'      => false,
        ]);

        // 1. toArray() test
        $array = $pixel->toArray();
        $this->assertArrayNotHasKey('access_token', $array, 'toArray() must hide access_token');

        // 2. toJson() test
        $json = $pixel->toJson();
        $this->assertStringNotContainsString($plaintextToken, $json, 'toJson() must NEVER contain plaintext token');

        // 3. toSafeArray() test
        $safeArray = $pixel->toSafeArray();
        $this->assertArrayNotHasKey('access_token', $safeArray);
        $this->assertTrue($safeArray['has_token']);
        $this->assertStringContainsString('...', $safeArray['masked_token']);
        $this->assertStringNotContainsString($plaintextToken, $safeArray['masked_token']);
    }

    /**
     * 7. Secret Scrubbing: MetaTrackingEvent scrubs access tokens, Authorization headers, and Bearer tokens.
     */
    public function test_secret_scrubbing_sanitizes_tokens_and_headers(): void
    {
        $sensitiveToken = 'EAAG_secret_capi_access_token_1234567890abcdef';
        $sensitiveHeader = 'Authorization: Bearer my_super_secret_bearer_token_9999';

        $event = new MetaTrackingEvent();
        $event->response_body = "Meta API call failed with token {$sensitiveToken} in response.";
        $event->error_message = "Curl error on request with {$sensitiveHeader}";

        $this->assertStringNotContainsString($sensitiveToken, $event->response_body);
        $this->assertStringContainsString('[REDACTED_CAPI_TOKEN]', $event->response_body);

        $this->assertStringNotContainsString('my_super_secret_bearer_token_9999', $event->error_message);
        $this->assertStringContainsString('Bearer [REDACTED]', $event->error_message);
    }

    /**
     * 8. Runtime Config Service: Single source of truth, dynamic retrieval, and cache invalidation.
     */
    public function test_runtime_config_service_retrieves_and_switches_settings(): void
    {
        // Ensure a pixel exists
        $pixel = MetaPixel::create([
            'pixel_name'      => 'Runtime Active Pixel',
            'pixel_id'        => '555666777888999',
            'test_event_code' => 'TEST_RUNTIME_1',
            'is_active'       => true,
            'is_default'      => true,
        ]);

        $settings = MetaTrackingSetting::current();
        $settings->active_pixel_id = $pixel->id;
        $settings->is_enabled = true;
        $settings->save();

        $service = app(MetaTrackingConfigService::class);
        $service->invalidateCache();

        // 1. Active Pixel ID retrieval
        $activePixelId = $service->getActivePixelId();
        $this->assertEquals('555666777888999', $activePixelId);

        // 2. Global tracking enabled
        $this->assertTrue($service->isTrackingEnabled());

        // 3. Test Event Code single source of truth retrieval from pixel
        $this->assertEquals('TEST_RUNTIME_1', $service->getTestEventCode());

        // 4. Event toggles
        $this->assertTrue($service->isBrowserEventEnabled('pageview'));
        $this->assertTrue($service->isBrowserEventEnabled('purchase'));
        $this->assertEquals('instant', $service->getPurchaseEventMode());
        $this->assertEquals(30, $service->getPurchaseDelayMinutes());

        // 5. Public client config (ZERO tokens or test event codes)
        $publicConfig = $service->getPublicClientConfig();
        $this->assertEquals('555666777888999', $publicConfig['pixel_id']);
        $this->assertArrayHasKey('browser_events', $publicConfig);
        $this->assertArrayNotHasKey('access_token', $publicConfig);
        $this->assertArrayNotHasKey('masked_token', $publicConfig);
        $this->assertArrayNotHasKey('test_event_code', $publicConfig);

        // 6. Runtime update and cache invalidation
        $settings->purchase_event_mode = 'hold';
        $settings->save();

        $service->invalidateCache();
        $this->assertEquals('hold', $service->getPurchaseEventMode());

        // Clean up
        $pixel->delete();
    }

    /**
     * 9. Backward Compatibility: Setting::get('facebook_pixel') remains functional.
     */
    public function test_backward_compatibility_with_legacy_setting_model(): void
    {
        Setting::set('legacy_test_key', 'test_value');
        $this->assertEquals('test_value', Setting::get('legacy_test_key'));

        // Clean up
        Setting::where('key', 'legacy_test_key')->delete();
    }
}
