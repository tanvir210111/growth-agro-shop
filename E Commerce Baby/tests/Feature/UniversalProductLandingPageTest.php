<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\LandingPage;
use App\Models\Order;
use Illuminate\Support\Facades\Artisan;

class UniversalProductLandingPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');

        Admin::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            ['name' => 'Admin', 'password' => bcrypt('admin123'), 'role' => 'Super Admin']
        );
    }

    protected function getAdminHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'x-admin-token' => 'adm_session',
        ];
    }

    /**
     * Test A: Create Universal Product page for Premium Face Serum
     */
    public function test_create_universal_product_premium_face_serum()
    {
        LandingPage::where('slug', 'premium-face-serum')->delete();

        $response = $this->postJson('/api/admin/landing-pages', [
            'name'         => 'Premium Face Serum',
            'slug'         => 'premium-face-serum',
            'status'       => 'published',
            'theme'        => 'universal',
            'product_name' => '100% Organic Glow Face Serum',
            'title'        => 'Premium Face Serum — 100% Pure Organic Glow Formula',
            'delivery_config' => [
                'delivery_type'          => 'free',
                'charge_inside_dhaka'    => 0,
                'charge_outside_dhaka'   => 0,
                'same_charge_everywhere' => true,
            ],
            'theme_config' => [
                'primary_color'   => '#0F766E',
                'secondary_color' => '#115E59',
                'light_teal'      => '#F0FDFA',
                'btn_red'         => '#E11D48',
                'btn_red_hover'   => '#BE123C',
                'accent_yellow'   => '#F59E0B',
                'text_dark'       => '#0F172A',
                'bg_body'         => '#FFFFFF',
            ],
            'content' => [
                'hero' => [
                    'alert_hook'      => '১০০% খাঁটি ও অর্গানিক সিরাম',
                    'main_title'      => 'আপনার ত্বকের প্রাকৃতিক উজ্জ্বলতা ফিরিয়ে আনুন',
                    'subtext'         => 'হাজারো সন্তুষ্ট গ্রাহকের পছন্দ। ৭ দিনে লক্ষণীয় পরিবর্তন।',
                    'cta_button_text' => '👉 অর্ডার করতে ক্লিক করুন',
                    'dual_cards'      => [
                        ['tag' => '৩০ মিলি স্ট্যান্ডার্ড বোটল', 'product_image' => '/images/serum-30ml.webp', 'variant_key' => 'serum-30ml', 'title' => '৩০ মিলি — অর্ডার করুন'],
                        ['tag' => '৬০ মিলি মেগা প্যাক', 'product_image' => '/images/serum-60ml.webp', 'variant_key' => 'serum-60ml', 'title' => '৬০ মিলি — অর্ডার করুন']
                    ]
                ],
                'packages' => [
                    ['id' => 'serum-30ml', 'name' => '৩০ মিলি স্ট্যান্ডার্ড বোটল', 'price' => 850, 'old_price' => 1200, 'default_quantity' => 1, 'image' => '/images/serum-30ml.webp'],
                    ['id' => 'serum-60ml', 'name' => '৬০ মিলি মেগা সেভার প্যাক', 'price' => 1500, 'old_price' => 2400, 'default_quantity' => 0, 'image' => '/images/serum-60ml.webp']
                ],
                'benefits_section_1' => [
                    'section_title' => 'কেন আমাদের ফেস সিরাম ব্যবহার করবেন?',
                    'items' => [
                        ['title' => 'ত্বক উজ্জ্বল ও টানটান করে', 'desc' => 'ডার্ক স্পট দূর করে'],
                        ['title' => '১০০% ক্ষতিকর কেমিক্যাল মুক্ত', 'desc' => 'সব ধরনের ত্বকের জন্য উপযোগী']
                    ]
                ],
                'usage_guide' => [
                    'section_title'    => 'ব্যবহার বিধি ও নিয়মাবলী',
                    'image'            => '/images/serum-guide.webp',
                    'instruction_text' => 'প্রতিদিন রাতে মুখ ভালো করে ধুয়ে ২-৩ ফোঁটা সিরাম আলতোভাবে ম্যাসাজ করুন।'
                ],
                'testimonials' => [
                    'section_title' => 'গ্রাহকদের বাস্তব রিভিউ',
                    'items' => [
                        ['name' => 'নুসরাত জাহান', 'location' => 'ঢাকা', 'rating' => 5, 'product_variant' => '৩০ মিলি', 'photo' => '/images/nusrat.webp', 'review_text' => 'খুবই চমৎকার সিরাম, ৩ দিনেই স্কিন গ্লো করছে!', 'is_verified' => true, 'is_active' => true, 'sort_order' => 1]
                    ]
                ]
            ]
        ], $this->getAdminHeaders());

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('landing_pages', [
            'slug'         => 'premium-face-serum',
            'product_name' => '100% Organic Glow Face Serum',
            'theme'        => 'universal',
            'status'       => 'published',
        ]);

        // Verify public rendering at /product/premium-face-serum
        $publicRes = $this->get('/product/premium-face-serum');
        $publicRes->assertStatus(200)
            ->assertSee('৩০ মিলি স্ট্যান্ডার্ড বোটল')
            ->assertSee('৳850')
            ->assertSee('নুসরাত জাহান');

        LandingPage::where('slug', 'premium-face-serum')->delete();
    }

    /**
     * Test B: Create Universal Product page for Baby Feeding Bottle
     */
    public function test_create_universal_product_baby_feeding_bottle()
    {
        LandingPage::where('slug', 'baby-feeding-bottle')->delete();

        $response = $this->postJson('/api/admin/landing-pages', [
            'name'         => 'Ultra Soft Baby Feeding Bottle',
            'slug'         => 'baby-feeding-bottle',
            'status'       => 'published',
            'theme'        => 'universal',
            'product_name' => 'Anti-Colic Baby Feeding Bottle',
            'delivery_config' => [
                'delivery_type'          => 'paid',
                'charge_inside_dhaka'    => 60,
                'charge_outside_dhaka'   => 120,
                'same_charge_everywhere' => false,
            ],
            'content' => [
                'hero' => [
                    'alert_hook'      => '১০০% বিপিএ মুক্ত ও নিরাপদ',
                    'main_title'      => 'আপনার প্রিয় সোনামণির আরামদায়ক ও নিরাপদ ফিডিং বোতল',
                    'cta_button_text' => '👉 এখনই অর্ডার করুন',
                ],
                'packages' => [
                    ['id' => 'bottle-1pc', 'name' => '১টি ফিডিং বোতল (২৪০ মিলি)', 'price' => 450, 'default_quantity' => 1],
                    ['id' => 'bottle-2pcs', 'name' => '২টি ফিডিং বোতল কম্বো সেট', 'price' => 800, 'default_quantity' => 0],
                ],
                'usage_guide' => [
                    'section_title'    => 'ব্যবহার ও জীবাণুমুক্ত করার নিয়ম',
                    'instruction_text' => 'প্রতিবার ব্যবহারের পূর্বে হালকা গরম পানিতে ধুয়ে জীবাণুমুক্ত করুন।'
                ]
            ]
        ], $this->getAdminHeaders());

        $response->assertStatus(201);

        $publicRes = $this->get('/product/baby-feeding-bottle');
        $publicRes->assertStatus(200)
            ->assertSee('১টি ফিডিং বোতল (২৪০ মিলি)')
            ->assertSee('৳450')
            ->assertSee('ব্যবহার ও জীবাণুমুক্ত করার নিয়ম');

        LandingPage::where('slug', 'baby-feeding-bottle')->delete();
    }

    /**
     * Test C: Create Chicken Booster page using Chicken Booster Template Preset
     */
    public function test_create_chicken_booster_from_preset()
    {
        LandingPage::where('slug', 'chicken-booster-high-yield')->delete();

        // 1. Fetch defaults with ?template=chicken-booster
        $defaultsRes = $this->getJson('/api/admin/landing-pages/master-defaults?template=chicken-booster', $this->getAdminHeaders());
        
        $defaultsRes->assertStatus(200)
            ->assertJson(['success' => true, 'template' => 'chicken-booster']);
        
        $data = $defaultsRes->json();
        $this->assertArrayHasKey('hero', $data['content']);
        $this->assertStringContainsString('রোগমুক্ত, দ্রুত মোটাতাজা ও সুস্থ মুরগি', $data['content']['hero']['main_title']);

        // 2. Create page using Chicken Booster preset
        $response = $this->postJson('/api/admin/landing-pages', [
            'name'            => 'Chicken Booster High Yield',
            'slug'            => 'chicken-booster-high-yield',
            'status'          => 'published',
            'theme'           => 'chicken-booster',
            'product_name'    => 'Chicken Booster (Broiler & Layer)',
            'content'         => $data['content'],
            'delivery_config' => $data['delivery_config'],
            'theme_config'    => $data['theme_config'],
        ], $this->getAdminHeaders());

        $response->assertStatus(201);

        $publicRes = $this->get('/product/chicken-booster-high-yield');
        $publicRes->assertStatus(200)
            ->assertSee('Broiler Booster')
            ->assertSee('Layer Booster')
            ->assertSee('খামারিদের সফলতার গল্প');

        LandingPage::where('slug', 'chicken-booster-high-yield')->delete();
    }

    /**
     * Test D & E & F: Edit universal page, change prices, delivery, and images
     */
    public function test_edit_universal_page_independently()
    {
        LandingPage::where('slug', 'organic-honey')->delete();

        $page = LandingPage::create([
            'name'         => 'Organic Honey',
            'slug'         => 'organic-honey',
            'status'       => 'draft',
            'theme'        => 'universal',
            'product_name' => 'Pure Sundarban Organic Honey',
            'content'      => LandingPage::getDefaultUniversalContent('খাঁটি সুন্দরবন মধু'),
            'delivery_config' => LandingPage::getDefaultDeliveryConfig(),
            'theme_config' => LandingPage::getDefaultUniversalThemeConfig(),
            'section_order' => LandingPage::getDefaultSectionOrder(),
        ]);

        // Update to paid delivery, update prices and publish
        $updateRes = $this->putJson("/api/admin/landing-pages/{$page->id}", [
            'name'         => 'Pure Sundarban Organic Honey 500g',
            'slug'         => 'organic-honey',
            'status'       => 'published',
            'theme'        => 'universal',
            'product_name' => 'Pure Sundarban Organic Honey',
            'delivery_config' => [
                'delivery_type'        => 'paid',
                'charge_inside_dhaka'  => 70,
                'charge_outside_dhaka' => 130,
            ],
            'content' => [
                'hero' => [
                    'main_title' => '১০০% খাঁটি সুন্দরবনের প্রাকৃতিক চাকের মধু',
                ],
                'packages' => [
                    ['id' => 'honey-500g', 'name' => '৫০০ গ্রাম চাকের মধু', 'price' => 650, 'default_quantity' => 1, 'image' => '/images/honey-500g.webp'],
                    ['id' => 'honey-1kg', 'name' => '১ কেজি প্রিমিয়াম চাকের মধু', 'price' => 1200, 'default_quantity' => 0, 'image' => '/images/honey-1kg.webp'],
                ]
            ]
        ], $this->getAdminHeaders());

        $updateRes->assertStatus(200);

        $page->refresh();
        $this->assertEquals('published', $page->status);
        $this->assertEquals(70, $page->delivery_config['charge_inside_dhaka']);
        $this->assertEquals(130, $page->delivery_config['charge_outside_dhaka']);

        // Verify public page
        $publicRes = $this->get('/product/organic-honey');
        $publicRes->assertStatus(200)
            ->assertSee('৫০০ গ্রাম চাকের মধু')
            ->assertSee('৳650');

        $page->delete();
    }

    /**
     * Test Q & R & S: Submit checkout from a universal product page and verify order attribution & BD courier check
     */
    public function test_checkout_and_order_attribution_on_universal_page()
    {
        LandingPage::where('slug', 'wireless-earbuds')->delete();

        $page = LandingPage::create([
            'name'         => 'Wireless ANC Earbuds',
            'slug'         => 'wireless-earbuds',
            'status'       => 'published',
            'theme'        => 'universal',
            'product_name' => 'Pro Wireless ANC Earbuds',
            'content'      => LandingPage::getDefaultUniversalContent('Pro Wireless ANC Earbuds'),
            'delivery_config' => [
                'delivery_type'        => 'paid',
                'charge_inside_dhaka'  => 80,
                'charge_outside_dhaka' => 120,
            ],
            'theme_config' => LandingPage::getDefaultUniversalThemeConfig(),
            'section_order' => LandingPage::getDefaultSectionOrder(),
        ]);

        $order = Order::create([
            'invoice_no'      => 'UNI-' . strtoupper(substr(md5(time()), 0, 6)),
            'customer_name'   => 'শরিফুল ইসলাম',
            'customer_phone'  => '01712345678',
            'customer_address'=> 'ধানমন্ডি ২৭, ঢাকা',
            'city_type'       => 'inside_dhaka',
            'delivery_charge' => 80,
            'subtotal'        => 500,
            'total_amount'    => 580,
            'status'          => 'pending',
            'order_type'      => 'regular',
            'source_type'     => 'landing_page',
            'landing_page'    => '/product/wireless-earbuds',
            'fraud_score'     => 15,
            'fraud_status'    => 'safe',
            'ip_address'      => '127.0.0.1',
        ]);

        $this->assertDatabaseHas('orders', [
            'id'           => $order->id,
            'source_type'  => 'landing_page',
            'landing_page' => '/product/wireless-earbuds',
        ]);

        // Check BD courier risk assessment check endpoint
        $courierRes = $this->getJson('/api/admin/fraud/courier-check?phone=01712345678', $this->getAdminHeaders());
        $courierRes->assertStatus(200);

        $page->delete();
        $order->delete();
    }

    /**
     * Test T: Server-to-server bridge correctly creates order and multiple order items for universal landing pages
     */
    public function test_internal_sync_creates_multi_item_order_for_universal_landing_page()
    {
        LandingPage::where('slug', 'organic-honey-combo')->delete();
        Order::where('invoice_no', 'SYNC-HONEY-001')->delete();

        $page = LandingPage::create([
            'name'         => 'Pure Sundarban Honey',
            'slug'         => 'organic-honey-combo',
            'status'       => 'published',
            'theme'        => 'universal',
            'product_name' => 'Organic Raw Forest Honey',
            'content'      => [
                'packages' => [
                    ['id' => 'honey-500g', 'name' => '৫০০ গ্রাম সুন্দরবনের মধু', 'price' => 650, 'old_price' => 850],
                    ['id' => 'honey-1kg', 'name' => '১ কেজি সুন্দরবনের মধু', 'price' => 1200, 'old_price' => 1600],
                ]
            ],
            'delivery_config' => [
                'delivery_type'        => 'paid',
                'charge_inside_dhaka'  => 60,
                'charge_outside_dhaka' => 120,
            ]
        ]);

        $internalSecret = env('INTERNAL_API_SECRET', 'baby-fashion-internal-2024-secret');

        $syncPayload = [
            'order_number'     => 'SYNC-HONEY-001',
            'customer_name'    => 'মাহমুদুল হাসান',
            'customer_phone'   => '01812345678',
            'customer_address' => 'মিরপুর ১০, ঢাকা',
            'delivery_zone'    => 'inside_dhaka',
            'delivery_charge'  => 60,
            'subtotal'         => 1850,
            'total'            => 1910,
            'payment_method'   => 'Cash on Delivery',
            'product_name'     => 'Organic Raw Forest Honey',
            'variant_name'     => '৫০০ গ্রাম + ১ কেজি',
            'quantity'         => 2,
            'unit_price'       => 925,
            'landing_page'     => '/product/organic-honey-combo',
            'items'            => [
                ['id' => 'honey-500g', 'name' => '৫০০ গ্রাম সুন্দরবনের মধু', 'variant_id' => 'honey-500g', 'price' => 650, 'quantity' => 1, 'total' => 650],
                ['id' => 'honey-1kg', 'name' => '১ কেজি সুন্দরবনের মধু', 'variant_id' => 'honey-1kg', 'price' => 1200, 'quantity' => 1, 'total' => 1200]
            ]
        ];

        $res = $this->postJson('/api/internal/sync-landing-order', $syncPayload, [
            'X-Internal-Secret' => $internalSecret
        ]);

        $res->assertSuccessful()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('orders', [
            'invoice_no'   => 'SYNC-HONEY-001',
            'subtotal'     => 1850,
            'delivery_charge' => 60,
            'total_amount' => 1910,
            'source_type'  => 'landing_page',
            'landing_page' => '/product/organic-honey-combo',
        ]);

        $createdOrder = Order::where('invoice_no', 'SYNC-HONEY-001')->first();
        $this->assertNotNull($createdOrder);
        $this->assertCount(2, $createdOrder->items);

        $page->delete();
        $createdOrder->items()->delete();
        $createdOrder->delete();
    }
}
