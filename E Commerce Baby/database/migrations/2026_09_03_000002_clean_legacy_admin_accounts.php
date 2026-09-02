<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cleans legacy admin accounts and ensures ONLY primary Super Admin (admin@gmail.com) remains.
     */
    public function up(): void
    {
        if (Schema::hasTable('admins')) {
            // Ensure primary Super Admin exists with proper role & active status
            DB::table('admins')->updateOrInsert(
                ['email' => 'admin@gmail.com'],
                [
                    'name'       => 'Super Admin',
                    'password'   => Hash::make('admin123'),
                    'role'       => 'super_admin',
                    'status'     => 'Active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Delete all other legacy/secondary admin accounts (Growth Agro Admin, captaincrown, etc.)
            DB::table('admins')
                ->where('email', '!=', 'admin@gmail.com')
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive down
    }
};
