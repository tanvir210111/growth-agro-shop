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
        // 1. Admins Table
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Admin');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('super_admin');
            $table->string('avatar')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Categories Table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('handle')->unique();
            $table->string('image')->nullable();
            $table->string('banner_image')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 3. Products Table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('sku')->nullable()->unique();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('category_handle')->nullable();
            $table->decimal('regular_price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->integer('stock')->default(50);
            $table->string('featured_image')->nullable();
            $table->string('hover_image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->json('sizes')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new_arrival')->default(false);
            $table->boolean('is_bestseller')->default(false);
            $table->boolean('is_clearance')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 4. Orders Table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('customer_address');
            $table->string('city_type')->default('inside_dhaka'); // inside_dhaka, outside_dhaka
            $table->decimal('delivery_charge', 10, 2)->default(70);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('payment_method')->default('COD');
            $table->string('status')->default('pending'); // pending, processing, shipped, delivered, cancelled, returned
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // 5. Order Items Table
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->string('product_image')->nullable();
            $table->string('size')->nullable()->default('Standard');
            $table->decimal('price', 10, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });

        // 6. Sliders Table
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('image');
            $table->string('link')->nullable();
            $table->string('button_text')->default('SHOP NOW >');
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 7. Settings Table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('sliders');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('admins');
    }
};
