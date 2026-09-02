<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'is_new')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->boolean('is_new')->default(true)->after('status');
            });

            // Set all existing/historical orders to is_new = false
            DB::table('orders')->update(['is_new' => false]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('orders', 'is_new')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('is_new');
            });
        }
    }
};
