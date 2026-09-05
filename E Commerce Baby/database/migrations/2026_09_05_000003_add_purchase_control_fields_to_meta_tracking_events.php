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
                if (!Schema::hasColumn('meta_tracking_events', 'order_source')) {
                    $table->string('order_source', 30)->nullable()->index()->after('order_id');
                }
                if (!Schema::hasColumn('meta_tracking_events', 'released_at')) {
                    $table->timestamp('released_at')->nullable()->after('sent_at');
                }
                if (!Schema::hasColumn('meta_tracking_events', 'released_by')) {
                    $table->unsignedBigInteger('released_by')->nullable()->after('released_at');
                }
                if (!Schema::hasColumn('meta_tracking_events', 'attempt_count')) {
                    $table->unsignedSmallInteger('attempt_count')->default(0)->after('response_code');
                }
                if (!Schema::hasColumn('meta_tracking_events', 'last_attempt_at')) {
                    $table->timestamp('last_attempt_at')->nullable()->after('attempt_count');
                }
                if (!Schema::hasColumn('meta_tracking_events', 'hold_reason')) {
                    $table->string('hold_reason', 100)->nullable()->after('purchase_mode');
                }
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
                $cols = [];
                foreach (['order_source', 'released_at', 'released_by', 'attempt_count', 'last_attempt_at', 'hold_reason'] as $col) {
                    if (Schema::hasColumn('meta_tracking_events', $col)) {
                        $cols[] = $col;
                    }
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
