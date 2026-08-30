<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            if (!Schema::hasColumn('licenses', 'client_name')) {
                $table->string('client_name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('licenses', 'product_name')) {
                $table->string('product_name')->nullable()->after('client_name');
            }
            if (!Schema::hasColumn('licenses', 'email')) {
                $table->string('email')->nullable()->after('product_name');
            }
            if (!Schema::hasColumn('licenses', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('licenses', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            if (!Schema::hasColumn('licenses', 'last_verified_at')) {
                $table->timestamp('last_verified_at')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('licenses', 'last_ip')) {
                $table->string('last_ip')->nullable()->after('last_verified_at');
            }
            if (!Schema::hasColumn('licenses', 'verify_count')) {
                $table->unsignedInteger('verify_count')->default(0)->after('last_ip');
            }
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $cols = ['client_name', 'product_name', 'email', 'phone', 'notes', 'last_verified_at', 'last_ip', 'verify_count'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('licenses', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
