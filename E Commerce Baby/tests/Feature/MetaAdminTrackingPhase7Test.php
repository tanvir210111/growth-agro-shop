<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\MetaPixel;
use App\Models\MetaTrackingSetting;
use App\Services\MetaTrackingConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MetaAdminTrackingPhase7Test extends TestCase
{
    use RefreshDatabase;

    protected Admin $superAdmin;
    protected Admin $regularAdmin;
    protected Admin $moderator;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test admins with canonical roles
        $this->superAdmin = Admin::create([
            'name'     => 'Super Administrator',
            'email'    => 'superadmin@growthagro.test',
            'phone'    => '01700000001',
            'password' => 'secret12345',
            'role'     => Admin::ROLE_SUPER_ADMIN,
            'status'   => 'Active',
        ]);

        $this->regularAdmin = Admin::create([
            'name'     => 'Regular Admin',
            'email'    => 'admin@growthagro.test',
            'phone'    => '01700000002',
            'password' => 'secret12345',
            'role'     => Admin::ROLE_ADMIN,
            'status'   => 'Active',
        ]);

        $this->moderator = Admin::create([
            'name'     => 'Content Moderator',
            'email'    => 'mod@growthagro.test',
            'phone'    => '01700000003',
            'password' => 'secret12345',
            'role'     => Admin::ROLE_MODERATOR,
            'status'   => 'Active',
        ]);
    }

    /**
     * Helper to authenticate as specific admin.
     */
    protected function actingAsAdmin(Admin $admin): self
    {
        return $this->actingAs($admin, 'admin');
    }

    public function test_unauthenticated_user_cannot_access_meta_admin_apis(): void
    {
        $this->getJson('/api/admin/meta/pixels')->assertStatus(401);
        $this->getJson('/api/admin/meta/tracking-settings')->assertStatus(401);
        $this->postJson('/api/admin/meta/pixels', [
            'pixel_name' => 'Unauthorized Pixel',
            'pixel_id'   => '123456789012345',
        ])->assertStatus(401);
        $this->putJson('/api/admin/meta/pixels/1', ['pixel_name' => 'Edit'])->assertStatus(401);
        $this->postJson('/api/admin/meta/pixels/1/set-active')->assertStatus(401);
        $this->postJson('/api/admin/meta/pixels/1/set-default')->assertStatus(401);
        $this->deleteJson('/api/admin/meta/pixels/1')->assertStatus(401);
        $this->putJson('/api/admin/meta/tracking-settings', ['is_enabled' => false])->assertStatus(401);
    }

    public function test_moderator_cannot_mutate_meta_tracking_configuration(): void
    {
        $pixel = MetaPixel::create([
            'pixel_name' => 'Base Pixel',
            'pixel_id'   => '111111111111111',
            'is_active'  => true,
            'is_default' => true,
        ]);

        $this->actingAsAdmin($this->moderator)
            ->postJson('/api/admin/meta/pixels', [
                'pixel_name' => 'Mod Pixel',
                'pixel_id'   => '222222222222222',
            ])
            ->assertStatus(403);

        $this->actingAsAdmin($this->moderator)
            ->putJson("/api/admin/meta/pixels/{$pixel->id}", [
                'pixel_name' => 'Mod Edit',
                'pixel_id'   => '111111111111111',
            ])
            ->assertStatus(403);

        $this->actingAsAdmin($this->moderator)
            ->postJson("/api/admin/meta/pixels/{$pixel->id}/set-active")
            ->assertStatus(403);

        $this->actingAsAdmin($this->moderator)
            ->postJson("/api/admin/meta/pixels/{$pixel->id}/set-default")
            ->assertStatus(403);

        $this->actingAsAdmin($this->moderator)
            ->deleteJson("/api/admin/meta/pixels/{$pixel->id}")
            ->assertStatus(403);

        $this->actingAsAdmin($this->moderator)
            ->putJson('/api/admin/meta/tracking-settings', ['is_enabled' => false])
            ->assertStatus(403);
    }

    public function test_pixel_list_and_settings_never_expose_decrypted_token_or_masked_token(): void
    {
        $rawSecretToken = 'EAAG_VERY_SECRET_CAPI_ACCESS_TOKEN_1234567890_XYZ';

        $pixel = MetaPixel::create([
            'pixel_name'      => 'Sensitive Pixel',
            'pixel_id'        => '123456789012345',
            'access_token'    => $rawSecretToken,
            'test_event_code' => 'TEST12345',
            'is_active'       => true,
            'is_default'      => true,
        ]);

        $settings = MetaTrackingSetting::current();
        $settings->update(['active_pixel_id' => $pixel->id]);

        // 1. Check GET /api/admin/meta/pixels
        $response = $this->actingAsAdmin($this->superAdmin)
            ->getJson('/api/admin/meta/pixels')
            ->assertStatus(200);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['pixels']);

        $pixelEntry = $data['pixels'][0];
        $this->assertEquals('Sensitive Pixel', $pixelEntry['pixel_name']);
        $this->assertEquals('123456789012345', $pixelEntry['pixel_id']);
        $this->assertTrue($pixelEntry['has_token']);

        // CRITICAL INVARIANT: Neither access_token nor masked_token in response!
        $this->assertArrayNotHasKey('access_token', $pixelEntry);
        $this->assertArrayNotHasKey('masked_token', $pixelEntry);
        $this->assertStringNotContainsString($rawSecretToken, $response->content());

        // 2. Check GET /api/admin/meta/tracking-settings
        $settingsResponse = $this->actingAsAdmin($this->superAdmin)
            ->getJson('/api/admin/meta/tracking-settings')
            ->assertStatus(200);

        $settingsData = $settingsResponse->json('settings');
        $this->assertNotNull($settingsData['active_pixel']);
        $this->assertTrue($settingsData['active_pixel']['has_token']);
        $this->assertArrayNotHasKey('access_token', $settingsData['active_pixel']);
        $this->assertArrayNotHasKey('masked_token', $settingsData['active_pixel']);
        $this->assertStringNotContainsString($rawSecretToken, $settingsResponse->content());
    }

    public function test_pixel_creation_validation(): void
    {
        // 1. Missing name
        $this->actingAsAdmin($this->superAdmin)
            ->postJson('/api/admin/meta/pixels', [
                'pixel_name' => '',
                'pixel_id'   => '123456789012345',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['pixel_name']);

        // 2. Non-numeric Pixel ID
        $this->actingAsAdmin($this->superAdmin)
            ->postJson('/api/admin/meta/pixels', [
                'pixel_name' => 'Invalid ID Pixel',
                'pixel_id'   => 'not_a_numeric_id',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['pixel_id']);

        // 3. Valid creation
        $res = $this->actingAsAdmin($this->superAdmin)
            ->postJson('/api/admin/meta/pixels', [
                'pixel_name'      => 'Main E-Commerce Pixel',
                'pixel_id'        => '1793041018387711',
                'test_event_code' => 'TEST99999',
            ])
            ->assertStatus(201);

        $this->assertTrue($res->json('success'));
        $this->assertEquals('1793041018387711', $res->json('pixel.pixel_id'));
        $this->assertFalse($res->json('pixel.has_token'));
    }

    public function test_pixel_creation_with_encrypted_token(): void
    {
        $rawToken = 'EAAG_TEST_TOKEN_MIN_LENGTH_20_CHARS_VAL';

        $res = $this->actingAsAdmin($this->superAdmin)
            ->postJson('/api/admin/meta/pixels', [
                'pixel_name'   => 'Encrypted Pixel',
                'pixel_id'     => '987654321098765',
                'access_token' => $rawToken,
            ])
            ->assertStatus(201);

        $pixelId = $res->json('pixel.id');
        $pixelModel = MetaPixel::findOrFail($pixelId);

        // Raw database column is encrypted
        $rawDbValue = DB::table('meta_pixels')->where('id', $pixelId)->value('access_token');
        $this->assertNotEquals($rawToken, $rawDbValue);

        // Decrypted model accessor returns exact original token
        $this->assertEquals($rawToken, $pixelModel->getDecryptedAccessToken());
    }

    public function test_edit_pixel_blank_token_preserves_existing_token(): void
    {
        $existingToken = 'EAAG_EXISTING_SECURE_TOKEN_1234567890';
        $pixel = MetaPixel::create([
            'pixel_name'   => 'Original Pixel',
            'pixel_id'     => '123456789012345',
            'access_token' => $existingToken,
        ]);

        // Submit edit request with empty/null token
        $res = $this->actingAsAdmin($this->regularAdmin)
            ->putJson("/api/admin/meta/pixels/{$pixel->id}", [
                'pixel_name'   => 'Renamed Pixel',
                'pixel_id'     => '123456789012345',
                'access_token' => '', // Left blank to keep existing
            ])
            ->assertStatus(200);

        $this->assertTrue($res->json('success'));
        $this->assertEquals('Renamed Pixel', $res->json('pixel.pixel_name'));
        $this->assertTrue($res->json('pixel.has_token'));

        // Existing encrypted token is preserved!
        $pixel->refresh();
        $this->assertEquals($existingToken, $pixel->getDecryptedAccessToken());
    }

    public function test_edit_pixel_with_new_token_updates_token(): void
    {
        $originalToken = 'EAAG_ORIGINAL_TOKEN_1234567890_VAL';
        $newToken      = 'EAAG_REPLACED_TOKEN_0987654321_VAL';

        $pixel = MetaPixel::create([
            'pixel_name'   => 'Token Pixel',
            'pixel_id'     => '123456789012345',
            'access_token' => $originalToken,
        ]);

        $res = $this->actingAsAdmin($this->superAdmin)
            ->putJson("/api/admin/meta/pixels/{$pixel->id}", [
                'pixel_name'   => 'Token Pixel',
                'pixel_id'     => '123456789012345',
                'access_token' => $newToken,
            ])
            ->assertStatus(200);

        $pixel->refresh();
        $this->assertEquals($newToken, $pixel->getDecryptedAccessToken());
    }

    public function test_active_pixel_switching_runtime_resolution(): void
    {
        $pixelA = MetaPixel::create([
            'pixel_name'      => 'Pixel Alpha',
            'pixel_id'        => '111111111111111',
            'access_token'    => 'EAAG_TOKEN_FOR_PIXEL_A_123456789',
            'test_event_code' => 'TEST_A',
            'is_active'       => true,
            'is_default'      => true,
        ]);

        $pixelB = MetaPixel::create([
            'pixel_name'      => 'Pixel Beta',
            'pixel_id'        => '222222222222222',
            'access_token'    => 'EAAG_TOKEN_FOR_PIXEL_B_987654321',
            'test_event_code' => 'TEST_B',
            'is_active'       => false,
            'is_default'      => false,
        ]);

        $settings = MetaTrackingSetting::current();
        $settings->update(['active_pixel_id' => $pixelA->id]);

        $configService = app(MetaTrackingConfigService::class);
        $this->assertEquals('111111111111111', $configService->getActivePixelId());

        // Now Admin switches active pixel to Pixel B
        $response = $this->actingAsAdmin($this->regularAdmin)
            ->postJson("/api/admin/meta/pixels/{$pixelB->id}/set-active")
            ->assertStatus(200);

        $this->assertTrue($response->json('success'));

        // Verify Pixel B is active and Pixel A is deactivated
        $pixelA->refresh();
        $pixelB->refresh();
        $this->assertTrue($pixelB->is_active);
        $this->assertFalse($pixelA->is_active);

        // Verify config service resolves Pixel B dynamically
        $this->assertEquals('222222222222222', $configService->getActivePixelId());
        $this->assertEquals('TEST_B', $configService->getTestEventCode());
        $this->assertEquals('EAAG_TOKEN_FOR_PIXEL_B_987654321', $configService->getCapiAccessToken());

        // Verify internal Node endpoint resolves Pixel B
        $internalSecret = env('INTERNAL_API_SECRET', 'baby-fashion-internal-2024-secret');
        $nodeConfigResponse = $this->withHeaders(['X-Internal-Secret' => $internalSecret])
            ->getJson('/api/internal/meta-tracking-config')
            ->assertStatus(200);

        $this->assertEquals('222222222222222', $nodeConfigResponse->json('active_pixel_id'));
        $this->assertEquals('TEST_B', $nodeConfigResponse->json('test_event_code'));
    }

    public function test_default_pixel_enforcement(): void
    {
        $pixelA = MetaPixel::create([
            'pixel_name' => 'Pixel Alpha',
            'pixel_id'   => '111111111111111',
            'is_default' => true,
        ]);

        $pixelB = MetaPixel::create([
            'pixel_name' => 'Pixel Beta',
            'pixel_id'   => '222222222222222',
            'is_default' => false,
        ]);

        // Set Pixel B as default
        $this->actingAsAdmin($this->regularAdmin)
            ->postJson("/api/admin/meta/pixels/{$pixelB->id}/set-default")
            ->assertStatus(200);

        $pixelA->refresh();
        $pixelB->refresh();

        $this->assertTrue($pixelB->is_default);
        $this->assertFalse($pixelA->is_default);
        $this->assertEquals(1, MetaPixel::where('is_default', true)->count());
    }

    public function test_delete_active_pixel_is_blocked(): void
    {
        $pixelA = MetaPixel::create([
            'pixel_name' => 'Active Pixel',
            'pixel_id'   => '111111111111111',
            'is_active'  => true,
        ]);

        $pixelB = MetaPixel::create([
            'pixel_name' => 'Inactive Pixel',
            'pixel_id'   => '222222222222222',
            'is_active'  => false,
        ]);

        $settings = MetaTrackingSetting::current();
        $settings->update(['active_pixel_id' => $pixelA->id]);

        $this->actingAsAdmin($this->superAdmin)
            ->deleteJson("/api/admin/meta/pixels/{$pixelA->id}")
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot delete the currently active Pixel. Please switch active pixel first.',
            ]);

        $this->assertDatabaseHas('meta_pixels', ['id' => $pixelA->id]);
    }

    public function test_delete_only_configured_pixel_is_blocked(): void
    {
        $pixel = MetaPixel::create([
            'pixel_name' => 'Sole Pixel',
            'pixel_id'   => '111111111111111',
            'is_active'  => false,
        ]);

        $this->actingAsAdmin($this->superAdmin)
            ->deleteJson("/api/admin/meta/pixels/{$pixel->id}")
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot delete the only configured Pixel in the system.',
            ]);

        $this->assertDatabaseHas('meta_pixels', ['id' => $pixel->id]);
    }

    public function test_delete_inactive_pixel_succeeds(): void
    {
        $pixelA = MetaPixel::create([
            'pixel_name' => 'Active Pixel',
            'pixel_id'   => '111111111111111',
            'is_active'  => true,
        ]);

        $pixelB = MetaPixel::create([
            'pixel_name' => 'Inactive Pixel',
            'pixel_id'   => '222222222222222',
            'is_active'  => false,
        ]);

        $settings = MetaTrackingSetting::current();
        $settings->update(['active_pixel_id' => $pixelA->id]);

        $this->actingAsAdmin($this->superAdmin)
            ->deleteJson("/api/admin/meta/pixels/{$pixelB->id}")
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('meta_pixels', ['id' => $pixelB->id]);
        $this->assertDatabaseHas('meta_pixels', ['id' => $pixelA->id]);
    }

    public function test_update_tracking_settings_and_cache_invalidation(): void
    {
        $pixel = MetaPixel::create([
            'pixel_name'   => 'Active Tracking Pixel',
            'pixel_id'     => '123456789012345',
            'access_token' => 'EAAG_VALID_TEST_TOKEN_FOR_SETTINGS_CHECK',
            'is_active'    => true,
            'is_default'   => true,
        ]);

        $settings = MetaTrackingSetting::current();
        $settings->update(['active_pixel_id' => $pixel->id]);

        $res = $this->actingAsAdmin($this->regularAdmin)
            ->putJson('/api/admin/meta/tracking-settings', [
                'is_enabled'                        => true,
                'browser_pageview_enabled'          => true,
                'browser_add_to_cart_enabled'       => false,
                'browser_initiate_checkout_enabled' => true,
                'browser_purchase_enabled'          => true,
                'server_add_to_cart_enabled'        => false,
                'server_initiate_checkout_enabled'  => true,
                'server_purchase_enabled'           => true,
            ])
            ->assertStatus(200);

        $this->assertTrue($res->json('success'));
        $this->assertFalse($res->json('settings.browser_events.add_to_cart'));
        $this->assertFalse($res->json('settings.server_events.add_to_cart'));

        $configService = app(MetaTrackingConfigService::class);
        $this->assertFalse($configService->isBrowserEventEnabled('AddToCart'));
        $this->assertFalse($configService->isServerEventEnabled('AddToCart'));
        $this->assertTrue($configService->isServerEventToggleEnabled('purchase'));
        $this->assertTrue($configService->isServerEventEnabled('Purchase'));
    }

    public function test_invalid_active_pixel_cannot_be_selected(): void
    {
        $this->actingAsAdmin($this->regularAdmin)
            ->postJson('/api/admin/meta/pixels/999999/set-active')
            ->assertStatus(404);
    }

    public function test_token_is_scrubbed_from_secrets_and_never_exposed_in_public_client_config(): void
    {
        $rawToken = 'EAAG_VERY_SECRET_LONG_ACCESS_TOKEN_FOR_SECURITY_CHECK_123';
        $pixel = MetaPixel::create([
            'pixel_name'   => 'Secret Pixel',
            'pixel_id'     => '123456789012345',
            'access_token' => $rawToken,
            'is_active'    => true,
            'is_default'   => true,
        ]);

        $settings = MetaTrackingSetting::current();
        $settings->update(['active_pixel_id' => $pixel->id]);

        $configService = app(MetaTrackingConfigService::class);
        $publicConfig = $configService->getPublicClientConfig();

        $this->assertArrayNotHasKey('access_token', $publicConfig);
        $this->assertArrayNotHasKey('masked_token', $publicConfig);

        $scrubbed = $configService->scrubSecrets("Error connecting with Authorization: Bearer {$rawToken}");
        $this->assertStringNotContainsString($rawToken, $scrubbed);
    }
}
