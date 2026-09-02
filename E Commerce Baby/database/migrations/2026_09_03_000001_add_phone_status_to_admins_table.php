<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add phone and status columns to admins table (non-destructive).
     * Preserves all existing records and credentials.
     */
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'phone')) {
                $table->string('phone', 20)->nullable()->after('email');
            }
            if (!Schema::hasColumn('admins', 'status')) {
                $table->string('status', 20)->default('Active')->after('role');
            }
        });

        // Ensure all existing admins have Active status (safe backfill)
        DB::table('admins')->whereNull('status')->orWhere('status', '')->update(['status' => 'Active']);

        // Normalize existing roles to canonical 3-role system
        // 'super_admin' -> 'super_admin' (unchanged)
        // anything else -> 'admin' (safe fallback)
        DB::table('admins')
            ->whereNotIn('role', ['super_admin', 'admin', 'moderator'])
            ->update(['role' => 'admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumnIfExists('phone');
            $table->dropColumnIfExists('status');
        });
    }
};
