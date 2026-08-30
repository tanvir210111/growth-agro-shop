<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stolen_logs', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->nullable()->index();
            $table->string('ip')->nullable()->index();
            $table->string('reason')->nullable();
            $table->text('url')->nullable();
            $table->string('reported_time')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_read')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stolen_logs');
    }
};
