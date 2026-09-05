<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Meta Pixels Table (Supports multiple pixels, single source of truth for test_event_code)
        if (!Schema::hasTable('meta_pixels')) {
            Schema::create('meta_pixels', function (Blueprint $table) {
                $table->id();
                $table->string('pixel_name');
                $table->string('pixel_id', 30)->index();
                $table->text('access_token')->nullable(); // Encrypted CAPI Token
                $table->string('test_event_code', 50)->nullable(); // Sole source of truth for Test Event Code
                $table->boolean('is_active')->default(true)->index();
                $table->boolean('is_default')->default(false)->index();
                $table->timestamps();
            });
        }

        // 2. Meta Tracking Global Settings (Single active configuration row)
        if (!Schema::hasTable('meta_tracking_settings')) {
            Schema::create('meta_tracking_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_enabled')->default(true);
                $table->unsignedBigInteger('active_pixel_id')->nullable()->index();
                
                // Browser Event Toggles
                $table->boolean('browser_pageview_enabled')->default(true);
                $table->boolean('browser_add_to_cart_enabled')->default(true);
                $table->boolean('browser_initiate_checkout_enabled')->default(true);
                $table->boolean('browser_purchase_enabled')->default(true);

                // Server Event (CAPI) Toggles
                $table->boolean('server_pageview_enabled')->default(true);
                $table->boolean('server_add_to_cart_enabled')->default(true);
                $table->boolean('server_initiate_checkout_enabled')->default(true);
                $table->boolean('server_purchase_enabled')->default(true);

                // Purchase Event Control
                $table->string('purchase_event_mode', 20)->default('instant'); // instant, delay, hold
                $table->unsignedInteger('purchase_delay_minutes')->default(30);

                // Customer History Rule Engine Toggle
                $table->boolean('auto_rules_enabled')->default(false);

                $table->timestamps();

                $table->foreign('active_pixel_id')
                    ->references('id')
                    ->on('meta_pixels')
                    ->nullOnDelete();
            });
        }

        // 3. Meta Tracking Event Logs & Deduplication Stream
        if (!Schema::hasTable('meta_tracking_events')) {
            Schema::create('meta_tracking_events', function (Blueprint $table) {
                $table->id();
                $table->string('event_id', 100)->index();
                $table->string('event_name', 50)->index();
                $table->string('pixel_id', 30)->nullable()->index();
                $table->string('order_id', 100)->nullable()->index();
                $table->string('action_source', 30)->default('website');
                $table->string('event_source_url', 500)->nullable();
                $table->json('user_data')->nullable(); // Hashed PII only
                $table->json('custom_data')->nullable(); // Order amounts, items, currency
                $table->string('browser_status', 30)->default('pending'); // pending, sent, failed, n/a
                $table->string('server_status', 30)->default('pending'); // pending, sent, failed, held, skipped
                $table->string('deduplication_status', 30)->default('pending'); // pending, active, deduplicated, browser_only, server_only
                $table->string('purchase_mode', 20)->nullable(); // instant, delay, hold
                $table->timestamp('scheduled_at')->nullable()->index();
                $table->timestamp('sent_at')->nullable()->index();
                $table->unsignedSmallInteger('response_code')->nullable();
                $table->text('response_body')->nullable(); // Scrubbed of all credentials
                $table->text('error_message')->nullable(); // Scrubbed of all credentials
                $table->timestamps();
            });
        }

        // 4. Meta Purchase Rules Table (Configurable customer-history based rules)
        if (!Schema::hasTable('meta_purchase_rules')) {
            Schema::create('meta_purchase_rules', function (Blueprint $table) {
                $table->id();
                $table->string('rule_name');
                $table->integer('priority')->default(0)->index();
                $table->string('condition_field', 50); // e.g. return_ratio, cancelled_ratio, fraud_level
                $table->string('operator', 10); // <, <=, >, >=, ==, between
                $table->string('condition_value', 50);
                $table->string('condition_value_high', 50)->nullable();
                $table->string('action_mode', 20)->default('instant'); // instant, delay, hold
                $table->unsignedInteger('delay_minutes')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        // 5. Automatic Dynamic Preservation of Existing Production Setting (Zero hardcoded Pixel IDs)
        if (Schema::hasTable('settings')) {
            $existingPixelValue = DB::table('settings')->where('key', 'facebook_pixel')->value('value');
            $cleanPixelId = !empty($existingPixelValue) ? trim((string)$existingPixelValue) : null;

            $existingAddToCart = DB::table('settings')->where('key', 'landing_meta_add_to_cart_enabled')->value('value');
            $existingInitiateCheckout = DB::table('settings')->where('key', 'landing_meta_initiate_checkout_enabled')->value('value');

            $isAddToCartEnabled = ($existingAddToCart === null || $existingAddToCart === '' || $existingAddToCart === '1' || $existingAddToCart === 1 || $existingAddToCart === true);
            $isInitiateCheckoutEnabled = ($existingInitiateCheckout === null || $existingInitiateCheckout === '' || $existingInitiateCheckout === '1' || $existingInitiateCheckout === 1 || $existingInitiateCheckout === true);

            $activePixelRecordId = null;
            if (!empty($cleanPixelId) && preg_match('/^\d{14,18}$/', $cleanPixelId)) {
                $activePixelRecordId = DB::table('meta_pixels')->insertGetId([
                    'pixel_name'      => 'Default Production Pixel',
                    'pixel_id'        => $cleanPixelId,
                    'access_token'    => null,
                    'test_event_code' => null,
                    'is_active'       => true,
                    'is_default'      => true,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            if (DB::table('meta_tracking_settings')->count() === 0) {
                DB::table('meta_tracking_settings')->insert([
                    'is_enabled'                        => true,
                    'active_pixel_id'                   => $activePixelRecordId,
                    'browser_pageview_enabled'          => true,
                    'browser_add_to_cart_enabled'       => $isAddToCartEnabled,
                    'browser_initiate_checkout_enabled' => $isInitiateCheckoutEnabled,
                    'browser_purchase_enabled'          => true,
                    'server_pageview_enabled'           => true,
                    'server_add_to_cart_enabled'        => true,
                    'server_initiate_checkout_enabled'  => true,
                    'server_purchase_enabled'           => true,
                    'purchase_event_mode'               => 'instant',
                    'purchase_delay_minutes'            => 30,
                    'auto_rules_enabled'                => false,
                    'created_at'                        => now(),
                    'updated_at'                        => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_purchase_rules');
        Schema::dropIfExists('meta_tracking_events');
        Schema::dropIfExists('meta_tracking_settings');
        Schema::dropIfExists('meta_pixels');
    }
};
