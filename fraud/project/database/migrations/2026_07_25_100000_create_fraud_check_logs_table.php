<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fraud_check_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('phone', 20)->index();
            $table->json('result')->nullable();
            $table->unsignedInteger('aggregate_success')->default(0);
            $table->unsignedInteger('aggregate_cancel')->default(0);
            $table->unsignedInteger('aggregate_total')->default(0);
            $table->decimal('success_ratio', 5, 2)->default(0);
            $table->decimal('cancel_ratio', 5, 2)->default(0);
            $table->string('checked_by', 20)->default('admin'); // admin|user
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_check_logs');
    }
};
