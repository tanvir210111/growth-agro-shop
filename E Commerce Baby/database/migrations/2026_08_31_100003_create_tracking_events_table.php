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
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->nullable()->constrained('tracking_sessions')->cascadeOnDelete();
            $table->foreignId('visitor_id')->nullable()->constrained('tracking_visitors')->cascadeOnDelete();
            $table->string('event_name', 50)->index();
            $table->string('entity_type', 50)->nullable()->index();
            $table->string('entity_id', 100)->nullable()->index();
            $table->string('cta_identifier', 100)->nullable()->index();
            $table->string('page_path', 255)->nullable()->index();
            $table->decimal('event_value', 10, 2)->nullable();
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            // Composite index for fast chronological event analysis
            $table->index(['created_at', 'event_name'], 'tracking_events_created_event_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
    }
};
