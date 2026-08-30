<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * user_hostings.status এ 'Expired' সেট করতে গিয়ে Data truncated এরর হয়
     * যদি কলাম ENUM হয় এবং 'Expired' না থাকে। কলামকে VARCHAR(50) করলে সব স্ট্যাটাস কাজ করবে।
     */
    public function up(): void
    {
        if (!Schema::hasTable('user_hostings') || !Schema::hasColumn('user_hostings', 'status')) {
            return;
        }

        DB::statement("ALTER TABLE user_hostings MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Active'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_hostings') || !Schema::hasColumn('user_hostings', 'status')) {
            return;
        }

        // Revert to ENUM with common values (existing data must be one of these)
        DB::statement("ALTER TABLE user_hostings MODIFY COLUMN status ENUM('Active','Pending','Suspended','Expired') NOT NULL DEFAULT 'Active'");
    }
};
