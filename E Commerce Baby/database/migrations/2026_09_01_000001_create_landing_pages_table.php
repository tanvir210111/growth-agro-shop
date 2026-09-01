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
        if (!Schema::hasTable('landing_pages')) {
            Schema::create('landing_pages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('status')->default('draft'); // draft, published, unpublished
                $table->string('theme')->default('chicken-booster');
                $table->string('product_id')->nullable();
                $table->string('product_name')->nullable();
                $table->string('title')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->longText('content')->nullable(); // JSON: hero, benefits, videos, usage, packages, etc.
                $table->longText('delivery_config')->nullable(); // JSON: free/paid, inside/outside charge, threshold
                $table->longText('theme_config')->nullable(); // JSON: colors, typography, layout tokens
                $table->longText('seo_config')->nullable(); // JSON: og tags, canonical, robots
                $table->longText('section_order')->nullable(); // JSON: array of section keys & active states
                $table->timestamp('published_at')->nullable();
                $table->timestamps();

                $table->index('slug');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_pages');
    }
};
