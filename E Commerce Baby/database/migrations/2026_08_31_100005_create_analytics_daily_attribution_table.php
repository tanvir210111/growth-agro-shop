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
        Schema::create('analytics_daily_attribution', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->index();
            $table->string('channel', 50)->nullable()->index();
            $table->string('utm_source', 100)->nullable()->index();
            $table->string('utm_campaign', 100)->nullable()->index();
            $table->string('landing_page', 150)->nullable()->index();
            $table->unsignedInteger('visitors_count')->default(0);
            $table->unsignedInteger('page_views')->default(0);
            $table->unsignedInteger('cta_clicks')->default(0);
            $table->unsignedInteger('add_to_cart_count')->default(0);
            $table->unsignedInteger('checkout_started_count')->default(0);
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('conversion_rate', 8, 4)->default(0);
            $table->timestamps();

            // Composite unique index for fast daily metric aggregation and upserts
            $table->unique(
                ['report_date', 'channel', 'utm_source', 'utm_campaign', 'landing_page'],
                'daily_attr_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_attribution');
    }
};
