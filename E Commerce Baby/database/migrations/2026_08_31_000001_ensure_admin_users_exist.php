<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('admins')) {
            // 1. Primary admin@gmail.com
            DB::table('admins')->updateOrInsert(
                ['email' => 'admin@gmail.com'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('admin123'),
                    'role' => 'super_admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // 2. Legacy captaincrown@admin.com
            DB::table('admins')->updateOrInsert(
                ['email' => 'captaincrown@admin.com'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('Aziz625713'),
                    'role' => 'super_admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // 3. Domain admin admin@growthagro.shop
            DB::table('admins')->updateOrInsert(
                ['email' => 'admin@growthagro.shop'],
                [
                    'name' => 'Growth Agro Admin',
                    'password' => Hash::make('Admin@Growth2026'),
                    'role' => 'super_admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive rollback needed
    }
};
