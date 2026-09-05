<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 9: Add rule snapshot columns to meta_tracking_events.
 *
 * rule_id   — Snapshot of the matched rule's primary key at event creation time.
 * rule_name — Snapshot of the matched rule's name at event creation time.
 *
 * These are snapshots, NOT foreign keys.
 * Deleting or editing a rule MUST NOT affect existing events.
 * These columns are written once at INSERT and never updated by rule changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('meta_tracking_events')) {
            Schema::table('meta_tracking_events', function (Blueprint $table) {
                if (!Schema::hasColumn('meta_tracking_events', 'rule_id')) {
                    // Snapshot: NOT a foreign key. Intentionally no ->constrained().
                    $table->unsignedBigInteger('rule_id')
                        ->nullable()
                        ->after('hold_reason')
                        ->comment('Snapshot of matched rule ID at event creation. No FK — immutable after insert.');
                }

                if (!Schema::hasColumn('meta_tracking_events', 'rule_name')) {
                    $table->string('rule_name', 255)
                        ->nullable()
                        ->after('rule_id')
                        ->comment('Snapshot of matched rule name at event creation. Immutable after insert.');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('meta_tracking_events')) {
            Schema::table('meta_tracking_events', function (Blueprint $table) {
                $cols = [];
                foreach (['rule_id', 'rule_name'] as $col) {
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
