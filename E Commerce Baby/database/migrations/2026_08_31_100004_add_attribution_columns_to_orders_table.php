<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'visitor_id')) {
                $table->unsignedBigInteger('visitor_id')->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('orders', 'session_id')) {
                $table->unsignedBigInteger('session_id')->nullable()->after('visitor_id')->index();
            }
            if (!Schema::hasColumn('orders', 'source_type')) {
                $table->string('source_type', 30)->default('storefront')->after('payment_method')->index();
            }
            if (!Schema::hasColumn('orders', 'landing_page')) {
                $table->string('landing_page', 255)->nullable()->after('source_type')->index();
            }
            if (!Schema::hasColumn('orders', 'utm_source')) {
                $table->string('utm_source', 100)->nullable()->after('landing_page')->index();
            }
            if (!Schema::hasColumn('orders', 'utm_medium')) {
                $table->string('utm_medium', 100)->nullable()->after('utm_source')->index();
            }
            if (!Schema::hasColumn('orders', 'utm_campaign')) {
                $table->string('utm_campaign', 150)->nullable()->after('utm_medium')->index();
            }
            if (!Schema::hasColumn('orders', 'utm_content')) {
                $table->string('utm_content', 150)->nullable()->after('utm_campaign');
            }
            if (!Schema::hasColumn('orders', 'referrer_domain')) {
                $table->string('referrer_domain', 100)->nullable()->after('utm_content')->index();
            }
            if (!Schema::hasColumn('orders', 'click_id')) {
                $table->string('click_id', 150)->nullable()->after('referrer_domain')->index();
            }
            if (!Schema::hasColumn('orders', 'device_type')) {
                $table->string('device_type', 20)->nullable()->after('click_id')->index();
            }
            if (!Schema::hasColumn('orders', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('device_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columnsToDrop = [
                'visitor_id',
                'session_id',
                'source_type',
                'landing_page',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_content',
                'referrer_domain',
                'click_id',
                'device_type',
                'ip_address',
            ];

            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
