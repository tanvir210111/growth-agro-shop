<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Phase 12 — Admin Management Tests
 *
 * Tests: DB-backed CRUD, role-based authorization, password reset.
 * NEVER tests for password being returned in API responses.
 */
class AdminManagementPhase12Test extends TestCase
{
    use RefreshDatabase;

    private Admin $superAdmin;
    private Admin $adminUser;
    private Admin $moderatorUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the 3 role tiers
        $this->superAdmin = Admin::firstOrCreate(
            ['email' => 'superadmin@test.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('superpass123'),
                'role'     => Admin::ROLE_SUPER_ADMIN,
                'status'   => 'Active',
            ]
        );

        $this->adminUser = Admin::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('adminpass123'),
                'role'     => Admin::ROLE_ADMIN,
                'status'   => 'Active',
            ]
        );

        $this->moderatorUser = Admin::firstOrCreate(
            ['email' => 'moderator@test.com'],
            [
                'name'     => 'Moderator User',
                'password' => Hash::make('modpass123'),
                'role'     => Admin::ROLE_MODERATOR,
                'status'   => 'Active',
            ]
        );
    }

    // =========================================================================
    // TEST 1: Unauthenticated requests are rejected (401)
    // =========================================================================

    public function test_unauthenticated_cannot_list_admins(): void
    {
        $response = $this->getJson('/api/admin/admins');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_cannot_create_admin(): void
    {
        $response = $this->postJson('/api/admin/admins', [
            'name'     => 'New Admin',
            'email'    => 'new@test.com',
            'phone'    => '01712345678',
            'password' => 'password123',
            'role'     => 'admin',
        ]);
        $response->assertStatus(401);
    }

    // =========================================================================
    // TEST 2: LIST ADMINS
    // =========================================================================

    public function test_super_admin_can_list_admins(): void
    {
        $response = $this->withAdminToken($this->superAdmin)
            ->getJson('/api/admin/admins');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'count', 'admins' => [['id', 'name', 'email', 'role', 'status']]]);
    }

    public function test_admin_can_list_admins(): void
    {
        $response = $this->withAdminToken($this->adminUser)
            ->getJson('/api/admin/admins');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_moderator_cannot_list_admins_forbidden(): void
    {
        $response = $this->withAdminToken($this->moderatorUser)
            ->getJson('/api/admin/admins');

        $response->assertStatus(403);
    }

    // =========================================================================
    // TEST 3: PASSWORD NEVER RETURNED IN RESPONSE
    // =========================================================================

    public function test_password_never_returned_in_list_response(): void
    {
        $response = $this->withAdminToken($this->superAdmin)
            ->getJson('/api/admin/admins');

        $response->assertStatus(200);
        $admins = $response->json('admins');
        foreach ($admins as $admin) {
            $this->assertArrayNotHasKey('password', $admin, 'Password MUST NOT be returned in API response');
            $this->assertArrayNotHasKey('remember_token', $admin, 'remember_token MUST NOT be returned in API response');
        }
    }

    public function test_password_never_returned_after_create(): void
    {
        $response = $this->withAdminToken($this->superAdmin)
            ->postJson('/api/admin/admins', [
                'name'     => 'New Test Admin',
                'email'    => 'newtest@test.com',
                'phone'    => '01700000001',
                'password' => 'password123',
                'role'     => 'admin',
                'status'   => 'Active',
            ]);

        $response->assertStatus(201);
        $this->assertArrayNotHasKey('password', $response->json('admin'));
        $this->assertArrayNotHasKey('remember_token', $response->json('admin'));
    }

    // =========================================================================
    // TEST 4: CREATE ADMIN (Super Admin only)
    // =========================================================================

    public function test_super_admin_can_create_admin(): void
    {
        $response = $this->withAdminToken($this->superAdmin)
            ->postJson('/api/admin/admins', [
                'name'     => 'New Admin',
                'email'    => 'newadmin@test.com',
                'phone'    => '01712345678',
                'password' => 'password123',
                'role'     => Admin::ROLE_ADMIN,
                'status'   => 'Active',
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('admin.email', 'newadmin@test.com')
            ->assertJsonPath('admin.role', Admin::ROLE_ADMIN);

        $this->assertDatabaseHas('admins', ['email' => 'newadmin@test.com']);
    }

    public function test_non_super_admin_cannot_create_admin(): void
    {
        $response = $this->withAdminToken($this->adminUser)
            ->postJson('/api/admin/admins', [
                'name'     => 'Unauthorized Admin',
                'email'    => 'unauth@test.com',
                'phone'    => '01712345678',
                'password' => 'password123',
                'role'     => Admin::ROLE_ADMIN,
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('admins', ['email' => 'unauth@test.com']);
    }

    public function test_create_admin_validates_required_fields(): void
    {
        $response = $this->withAdminToken($this->superAdmin)
            ->postJson('/api/admin/admins', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    public function test_create_admin_validates_duplicate_email(): void
    {
        $this->withAdminToken($this->superAdmin)
            ->postJson('/api/admin/admins', [
                'name'     => 'First Admin',
                'email'    => 'duplicate@test.com',
                'phone'    => '01712345678',
                'password' => 'password123',
                'role'     => Admin::ROLE_ADMIN,
            ]);

        $response = $this->withAdminToken($this->superAdmin)
            ->postJson('/api/admin/admins', [
                'name'     => 'Second Admin',
                'email'    => 'duplicate@test.com',
                'phone'    => '01712345679',
                'password' => 'password123',
                'role'     => Admin::ROLE_ADMIN,
            ]);

        $response->assertStatus(422);
    }

    public function test_password_is_stored_hashed_in_database(): void
    {
        $plainPassword = 'secretpass123';

        $this->withAdminToken($this->superAdmin)
            ->postJson('/api/admin/admins', [
                'name'     => 'Hash Test Admin',
                'email'    => 'hashtest@test.com',
                'phone'    => '01700000002',
                'password' => $plainPassword,
                'role'     => Admin::ROLE_ADMIN,
            ]);

        $admin = Admin::where('email', 'hashtest@test.com')->first();
        $this->assertNotNull($admin);
        $this->assertNotEquals($plainPassword, $admin->password, 'Password must NOT be stored as plaintext');
        $this->assertTrue(Hash::check($plainPassword, $admin->password), 'Password must be verifiable via Hash::check');
    }

    // =========================================================================
    // TEST 5: UPDATE ADMIN
    // =========================================================================

    public function test_super_admin_can_update_any_admin(): void
    {
        $response = $this->withAdminToken($this->superAdmin)
            ->patchJson("/api/admin/admins/{$this->adminUser->id}", [
                'name'  => 'Updated Admin Name',
                'phone' => '01799999999',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('admin.name', 'Updated Admin Name');
    }

    public function test_admin_cannot_update_super_admin(): void
    {
        $response = $this->withAdminToken($this->adminUser)
            ->patchJson("/api/admin/admins/{$this->superAdmin->id}", [
                'name' => 'Trying to Modify Super Admin',
            ]);

        $response->assertStatus(403);
    }

    public function test_moderator_cannot_update_any_admin(): void
    {
        $response = $this->withAdminToken($this->moderatorUser)
            ->patchJson("/api/admin/admins/{$this->adminUser->id}", [
                'name' => 'Moderator Trying to Edit',
            ]);

        $response->assertStatus(403);
    }

    // =========================================================================
    // TEST 6: RESET PASSWORD
    // =========================================================================

    public function test_super_admin_can_reset_any_password(): void
    {
        $response = $this->withAdminToken($this->superAdmin)
            ->postJson("/api/admin/admins/{$this->adminUser->id}/reset-password", [
                'new_password'              => 'newpass123',
                'new_password_confirmation' => 'newpass123',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify password is actually updated and hashed
        $updatedAdmin = Admin::find($this->adminUser->id);
        $this->assertTrue(Hash::check('newpass123', $updatedAdmin->password));
        $this->assertFalse(Hash::check('adminpass123', $updatedAdmin->password), 'Old password should no longer work');
    }

    public function test_moderator_cannot_reset_passwords(): void
    {
        $response = $this->withAdminToken($this->moderatorUser)
            ->postJson("/api/admin/admins/{$this->adminUser->id}/reset-password", [
                'new_password'              => 'hackedpass123',
                'new_password_confirmation' => 'hackedpass123',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_cannot_reset_super_admin_password(): void
    {
        $response = $this->withAdminToken($this->adminUser)
            ->postJson("/api/admin/admins/{$this->superAdmin->id}/reset-password", [
                'new_password'              => 'hijacked123',
                'new_password_confirmation' => 'hijacked123',
            ]);

        $response->assertStatus(403);
    }

    public function test_reset_password_validates_minimum_length(): void
    {
        $response = $this->withAdminToken($this->superAdmin)
            ->postJson("/api/admin/admins/{$this->adminUser->id}/reset-password", [
                'new_password'              => 'short',
                'new_password_confirmation' => 'short',
            ]);

        $response->assertStatus(422);
    }

    public function test_reset_password_validates_password_confirmation(): void
    {
        $response = $this->withAdminToken($this->superAdmin)
            ->postJson("/api/admin/admins/{$this->adminUser->id}/reset-password", [
                'new_password'              => 'password123',
                'new_password_confirmation' => 'different456',
            ]);

        $response->assertStatus(422);
    }

    // =========================================================================
    // TEST 7: DELETE ADMIN
    // =========================================================================

    public function test_super_admin_can_delete_admin(): void
    {
        $toDelete = Admin::create([
            'name'     => 'To Delete',
            'email'    => 'todelete@test.com',
            'password' => Hash::make('pass123456'),
            'role'     => Admin::ROLE_MODERATOR,
            'status'   => 'Active',
        ]);

        $response = $this->withAdminToken($this->superAdmin)
            ->deleteJson("/api/admin/admins/{$toDelete->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('admins', ['id' => $toDelete->id]);
    }

    public function test_super_admin_cannot_delete_self(): void
    {
        $response = $this->withAdminToken($this->superAdmin)
            ->deleteJson("/api/admin/admins/{$this->superAdmin->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('admins', ['id' => $this->superAdmin->id]);
    }

    public function test_non_super_admin_cannot_delete(): void
    {
        $response = $this->withAdminToken($this->adminUser)
            ->deleteJson("/api/admin/admins/{$this->moderatorUser->id}");

        $response->assertStatus(403);
    }

    // =========================================================================
    // TEST 8: ROLE CONSTANTS IN MODEL
    // =========================================================================

    public function test_admin_model_has_correct_role_constants(): void
    {
        $this->assertEquals('super_admin', Admin::ROLE_SUPER_ADMIN);
        $this->assertEquals('admin', Admin::ROLE_ADMIN);
        $this->assertEquals('moderator', Admin::ROLE_MODERATOR);
        $this->assertEquals(['super_admin', 'admin', 'moderator'], Admin::VALID_ROLES);
    }

    public function test_admin_model_role_helpers_work(): void
    {
        $this->assertTrue($this->superAdmin->isSuperAdmin());
        $this->assertFalse($this->adminUser->isSuperAdmin());
        $this->assertFalse($this->moderatorUser->isSuperAdmin());

        $this->assertTrue($this->superAdmin->isAdmin());
        $this->assertTrue($this->adminUser->isAdmin());
        $this->assertFalse($this->moderatorUser->isAdmin());
    }

    public function test_admin_model_role_labels(): void
    {
        $this->assertEquals('Super Admin', $this->superAdmin->role_label);
        $this->assertEquals('Admin', $this->adminUser->role_label);
        $this->assertEquals('Moderator', $this->moderatorUser->role_label);
    }

    public function test_to_safe_array_never_includes_password(): void
    {
        $safe = $this->superAdmin->toSafeArray();
        $this->assertArrayNotHasKey('password', $safe);
        $this->assertArrayNotHasKey('remember_token', $safe);
        $this->assertArrayHasKey('id', $safe);
        $this->assertArrayHasKey('name', $safe);
        $this->assertArrayHasKey('email', $safe);
        $this->assertArrayHasKey('role', $safe);
        $this->assertArrayHasKey('role_label', $safe);
        $this->assertArrayHasKey('status', $safe);
    }

    // =========================================================================
    // TEST 9: MIGRATION — phone and status columns exist
    // =========================================================================

    public function test_admins_table_has_phone_and_status_columns(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('admins', 'phone'),
            'admins table must have phone column'
        );
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('admins', 'status'),
            'admins table must have status column'
        );
    }

    // =========================================================================
    // HELPER: Set admin token header for authenticated requests
    // =========================================================================

    private function withAdminToken(Admin $admin): self
    {
        // Use a valid-length token format that authenticateAdmin accepts
        $token = 'adm_' . str_repeat('a', 32);
        // Temporarily mock the Admin::first() return to this admin by setting session
        $this->actingAs($admin, 'admin');
        return $this->withHeaders([
            'x-admin-token' => $token,
            'Accept'        => 'application/json',
        ]);
    }
}
