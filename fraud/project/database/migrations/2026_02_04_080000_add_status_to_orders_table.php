<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * orders টেবিলে status কলাম যোগ করা।
     * Domain/Hosting অর্ডার ও POS সেল - সব জায়গায় status ব্যবহার করা হয়।
     * আগে এই কলাম ছিল না বলে "Unknown column 'status'" এরর হচ্ছিল।
     */
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        if (!Schema::hasColumn('orders', 'status')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('status', 50)->default('Pending')->after('payment_method');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'status')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
