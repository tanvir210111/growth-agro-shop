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
        Schema::create('tracking_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_uuid', 64)->unique();
            $table->foreignId('visitor_id')->constrained('tracking_visitors')->cascadeOnDelete();
            $table->timestamp('session_start');
            $table->timestamp('session_end')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->string('entry_url', 500)->nullable();
            $table->string('landing_page_path', 255)->nullable()->index();
            $table->string('page_type', 30)->nullable()->index();
            $table->string('referrer_url', 500)->nullable();
            $table->string('referrer_domain', 100)->nullable()->index();
            $table->string('channel', 50)->nullable()->index();
            $table->string('utm_source', 100)->nullable()->index();
            $table->string('utm_medium', 100)->nullable()->index();
            $table->string('utm_campaign', 150)->nullable()->index();
            $table->string('utm_content', 150)->nullable();
            $table->string('utm_term', 150)->nullable();
            $table->string('click_id', 150)->nullable()->index();
            $table->string('device_type', 20)->nullable()->index();
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_converted')->default(false)->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_sessions');
    }
};
