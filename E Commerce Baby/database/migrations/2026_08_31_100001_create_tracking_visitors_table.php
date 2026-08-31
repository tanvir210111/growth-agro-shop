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
        Schema::create('tracking_visitors', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_uuid', 64)->unique();
            $table->timestamp('first_seen_at')->nullable()->index();
            $table->timestamp('last_seen_at')->nullable();
            $table->string('customer_phone', 20)->nullable()->index();
            $table->string('first_source', 100)->nullable();
            $table->string('first_utm_campaign', 150)->nullable();
            $table->string('first_landing_page', 255)->nullable();
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_visitors');
    }
};
