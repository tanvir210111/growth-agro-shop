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
        if (Schema::hasTable('meta_tracking_events')) {
            Schema::table('meta_tracking_events', function (Blueprint $table) {
                // Ensure unique compound index for event idempotency per pixel
                $table->unique(['pixel_id', 'event_name', 'event_id'], 'meta_pixel_event_id_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('meta_tracking_events')) {
            Schema::table('meta_tracking_events', function (Blueprint $table) {
                $table->dropUnique('meta_pixel_event_id_unique');
            });
        }
    }
};
