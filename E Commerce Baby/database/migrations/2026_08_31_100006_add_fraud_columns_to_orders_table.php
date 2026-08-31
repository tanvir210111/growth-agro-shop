<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add server-side fraud detection fields to the orders table.
     * All columns are nullable with safe defaults — existing orders are never touched.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'fraud_score')) {
                // 0–100 integer risk score. null = not yet scored.
                $table->unsignedTinyInteger('fraud_score')->nullable()->default(null)->after('ip_address');
            }

            if (!Schema::hasColumn('orders', 'fraud_level')) {
                // 'low', 'medium', 'high', or null = not yet evaluated.
                $table->string('fraud_level', 10)->nullable()->default(null)->after('fraud_score')->index();
            }

            if (!Schema::hasColumn('orders', 'fraud_reasons')) {
                // JSON array of human-readable risk reason strings.
                $table->json('fraud_reasons')->nullable()->default(null)->after('fraud_level');
            }

            if (!Schema::hasColumn('orders', 'courier_success_rate')) {
                // Success rate percentage from BD Courier (0.0 – 100.0). null = not checked.
                $table->decimal('courier_success_rate', 5, 2)->nullable()->default(null)->after('fraud_reasons');
            }

            if (!Schema::hasColumn('orders', 'courier_total_orders')) {
                $table->unsignedInteger('courier_total_orders')->nullable()->default(null)->after('courier_success_rate');
            }

            if (!Schema::hasColumn('orders', 'courier_delivered')) {
                $table->unsignedInteger('courier_delivered')->nullable()->default(null)->after('courier_total_orders');
            }

            if (!Schema::hasColumn('orders', 'courier_cancelled')) {
                $table->unsignedInteger('courier_cancelled')->nullable()->default(null)->after('courier_delivered');
            }

            if (!Schema::hasColumn('orders', 'courier_checked_at')) {
                // Timestamp when the courier check was last performed for this order's phone.
                $table->timestamp('courier_checked_at')->nullable()->default(null)->after('courier_cancelled');
            }
        });
    }

    /**
     * Remove fraud detection columns safely.
     * Each column is dropped only if it exists.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'fraud_score',
                'fraud_level',
                'fraud_reasons',
                'courier_success_rate',
                'courier_total_orders',
                'courier_delivered',
                'courier_cancelled',
                'courier_checked_at',
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
