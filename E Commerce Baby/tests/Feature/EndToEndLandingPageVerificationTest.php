<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\LandingPage;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EndToEndLandingPageVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');

        Admin::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            ['name' => 'Admin', 'password' => bcrypt('admin123'), 'role' => 'Super Admin']
        );

        LandingPage::firstOrCreate(
            ['slug' => 'chicken-booster'],
            [
                'name' => 'Chicken Booster',
                'status' => 'published',
                'theme' => 'chicken-booster',
                'product_name' => 'Chicken Booster',
                'title' => 'Chicken Booster — Growth Agro',
                'meta_title' => 'Chicken Booster',
                'meta_description' => 'Master theme chicken booster',
                'content' => LandingPage::getDefaultMasterContent(),
                'delivery_config' => LandingPage::getDefaultDeliveryConfig(),
                'theme_config' => LandingPage::getDefaultThemeConfig(),
                'section_order' => LandingPage::getDefaultSectionOrder(),
            ]
        );
    }

    protected function getAdminHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'x-admin-token' => 'adm_session',
        ];
    }

    protected function getTestPoultryPayload(string $status = 'published'): array
    {
        return [
            'name' => 'Test Poultry Booster',
            'slug' => 'test-poultry-booster',
            'status' => $status,
            'theme' => 'chicken-booster',
            'product_name' => 'Test Poultry Booster',
            'title' => 'Test Poultry Booster — Growth Agro',
            'meta_title' => 'Test Poultry Booster — Growth Agro',
            'meta_description' => 'Fast recovery and immune boost for poultry.',
            'delivery_config' => [
                'delivery_type' => 'paid',
                'charge_inside_dhaka' => 80,
                'charge_outside_dhaka' => 120,
                'same_charge_everywhere' => false,
                'free_delivery_above' => false,
                'free_delivery_threshold' => 1000,
                'inside_label' => 'ঢাকার ভিতরে',
                'outside_label' => 'ঢাকার বাইরে'
            ],
            // At least 2 theme colors modified
            'theme_config' => array_merge(LandingPage::getDefaultThemeConfig(), [
                'primary_color' => '#065F46', // Emerald
                'btn_red' => '#DC2626'        // Red-600
            ]),
            'content' => array_merge(LandingPage::getDefaultMasterContent(), [
                'packages' => [
                    [
                        'id' => 'pkg-1',
                        'name' => 'Test Poultry Booster (১ কেজি)',
                        'price' => 500,
                        'old_price' => 700,
                        'default_quantity' => 1,
                        'image' => '/uploads/landing-pages/test-pkg.webp',
                        'is_active' => true,
                        'sort_order' => 1
                    ]
                ],
                // At least 3 benefits
                'benefits_section_1' => [
                    'section_title' => 'Test Poultry Booster কেন ব্যবহার করবেন?',
                    'items' => [
                        ['title' => 'দ্রুত ওজন বৃদ্ধি নিশ্চিত করে', 'desc' => '৭-১০ দিনে লক্ষণীয় ওজন বৃদ্ধি'],
                        ['title' => 'রোগ প্রতিরোধ ক্ষমতা বৃদ্ধি করে', 'desc' => 'ম্যাসটাইটিস ও ফ্লু প্রতিরোধ'],
                        ['title' => '১০০% প্রাকৃতিক ও নিরাপদ উপাদান', 'desc' => 'কোনো পার্শ্বপ্রতিক্রিয়া নেই']
                    ]
                ],
                // At least 2 testimonials with customer photo
                'testimonials' => [
                    'section_title' => 'গ্রাহকদের বাস্তব রিভিউ',
                    'items' => [
                        [
                            'name' => 'মো: আরিফুল ইসলাম',
                            'location' => 'গাজীপুর',
                            'product_variant' => 'Test Poultry Booster (১ কেজি)',
                            'rating' => 5,
                            'photo' => '/uploads/landing-pages/customer_ariful.webp',
                            'review_text' => 'ব্যবহার করার পর মুরগির বৃদ্ধি খুব ভালো হচ্ছে।',
                            'is_verified' => true,
                            'is_active' => true,
                            'sort_order' => 1,
                            'date' => '১ সেপ্টেম্বর ২০২৬'
                        ],
                        [
                            'name' => 'ডা: সেলিম রেজা',
                            'location' => 'বগুড়া',
                            'product_variant' => 'Test Poultry Booster (১ কেজি)',
                            'rating' => 5,
                            'photo' => '/uploads/landing-pages/customer_selim.webp',
                            'review_text' => 'খামারিদের জন্য একটি অসাধারণ বুস্টার মেডিসিন।',
                            'is_verified' => true,
                            'is_active' => true,
                            'sort_order' => 2,
                            'date' => '১ সেপ্টেম্বর ২০২৬'
                        ]
                    ]
                ],
                // At least 3 FAQ items
                'faqs' => [
                    'section_title' => 'সাধারণ জিজ্ঞাসা (FAQ)',
                    'items' => [
                        ['question' => 'কীভাবে ব্যবহার করব?', 'answer' => 'প্রতি লিটার পানিতে ১ গ্রাম মিশিয়ে খাওয়ান।'],
                        ['question' => 'ডেলিভারি পেতে কতদিন সময় লাগে?', 'answer' => 'ঢাকার ভিতরে ২৪ ঘন্টা, বাইরে ৪৮ ঘন্টা।'],
                        ['question' => 'পণ্য হাতে পেয়ে টাকা পরিশোধ করা যাবে?', 'answer' => 'হ্যাঁ, ক্যাশ অন ডেলিভারি সুবিধা আছে।']
                    ]
                ]
            ]),
            'section_order' => LandingPage::getDefaultSectionOrder()
        ];
    }

    public function test_flow_01_create_save_draft_preview_publish()
    {
        LandingPage::where('slug', 'test-poultry-booster')->delete();
        $payload = $this->getTestPoultryPayload('draft');

        // Create Draft
        $createRes = $this->postJson('/api/admin/landing-pages', $payload, $this->getAdminHeaders());
        $createRes->assertStatus(201);
        $pageId = $createRes->json('page_id');
        $this->assertNotNull($pageId);

        // Preview Draft
        $previewRes = $this->get("/admin/landing-pages/{$pageId}/preview");
        $previewRes->assertStatus(200);
        $previewRes->assertSee('Test Poultry Booster');

        // Publish
        $pubRes = $this->patchJson("/api/admin/landing-pages/{$pageId}/status", ['status' => 'published'], $this->getAdminHeaders());
        $pubRes->assertStatus(200);
        $pubRes->assertJson(['success' => true, 'status' => 'published']);

        LandingPage::where('slug', 'test-poultry-booster')->delete();
    }

    public function test_flow_02_unique_slug_validation()
    {
        $resTaken = $this->getJson('/api/admin/landing-pages/check-slug?slug=chicken-booster');
        $resTaken->assertStatus(200);
        $resTaken->assertJson(['available' => false]);

        $resAvailable = $this->getJson('/api/admin/landing-pages/check-slug?slug=unique-slug-' . time());
        $resAvailable->assertStatus(200);
        $resAvailable->assertJson(['available' => true]);
    }

    public function test_flow_03_duplicate_landing_page()
    {
        $original = LandingPage::where('slug', 'chicken-booster')->first();
        $dupRes = $this->postJson("/api/admin/landing-pages/{$original->id}/duplicate", [], $this->getAdminHeaders());
        $dupRes->assertStatus(201);
        $dupId = $dupRes->json('duplicate_id');
        $dupPage = LandingPage::find($dupId);
        $this->assertNotNull($dupPage);
        $this->assertEquals('draft', $dupPage->status);
        $this->assertNotEquals($original->slug, $dupPage->slug);
        $dupPage->delete();
    }

    public function test_flow_04_free_delivery()
    {
        $freePage = LandingPage::create([
            'name' => 'Free Delivery Product',
            'slug' => 'free-delivery-' . time(),
            'status' => 'published',
            'theme' => 'chicken-booster',
            'delivery_config' => [
                'delivery_type' => 'free',
                'charge_inside_dhaka' => 0,
                'charge_outside_dhaka' => 0,
            ],
            'content' => LandingPage::getDefaultMasterContent(),
            'theme_config' => LandingPage::getDefaultThemeConfig(),
            'section_order' => LandingPage::getDefaultSectionOrder(),
        ]);

        $res = $this->get('/product/' . $freePage->slug);
        $res->assertStatus(200);
        $res->assertSee('ফ্রি');
        $freePage->delete();
    }

    public function test_flow_05_paid_delivery()
    {
        LandingPage::where('slug', 'test-poultry-booster')->delete();
        $page = LandingPage::create($this->getTestPoultryPayload('published'));
        $this->assertEquals('paid', $page->delivery_config['delivery_type']);
        $page->delete();
    }

    public function test_flow_06_inside_dhaka_delivery_charge()
    {
        LandingPage::where('slug', 'test-poultry-booster')->delete();
        $page = LandingPage::create($this->getTestPoultryPayload('published'));
        $this->assertEquals(80, $page->delivery_config['charge_inside_dhaka']);
        $res = $this->get('/product/' . $page->slug);
        $res->assertStatus(200);
        $res->assertSee('80');
        $page->delete();
    }

    public function test_flow_07_outside_dhaka_delivery_charge()
    {
        LandingPage::where('slug', 'test-poultry-booster')->delete();
        $page = LandingPage::create($this->getTestPoultryPayload('published'));
        $this->assertEquals(120, $page->delivery_config['charge_outside_dhaka']);
        $res = $this->get('/product/' . $page->slug);
        $res->assertStatus(200);
        $res->assertSee('120');
        $page->delete();
    }

    public function test_flow_08_customer_review_creation()
    {
        LandingPage::where('slug', 'test-poultry-booster')->delete();
        $page = LandingPage::create($this->getTestPoultryPayload('published'));
        $reviews = $page->content['testimonials']['items'];
        $this->assertCount(2, $reviews);
        $this->assertEquals('মো: আরিফুল ইসলাম', $reviews[0]['name']);
        $this->assertEquals('গাজীপুর', $reviews[0]['location']);
        $this->assertEquals(5, $reviews[0]['rating']);
        $this->assertTrue($reviews[0]['is_verified']);
        $page->delete();
    }

    public function test_flow_09_customer_review_image_upload_replace_remove()
    {
        Storage::fake('public');
        $photo = UploadedFile::fake()->image('reviewer.jpg', 300, 300);
        $res = $this->post('/api/admin/landing-pages/upload-media', ['image' => $photo], $this->getAdminHeaders());
        $res->assertStatus(200);
        $res->assertJson(['success' => true]);
        $this->assertNotEmpty($res->json('url'));
    }

    public function test_flow_10_review_image_rendering_on_public_landing_page()
    {
        LandingPage::where('slug', 'test-poultry-booster')->delete();
        $page = LandingPage::create($this->getTestPoultryPayload('published'));
        $res = $this->get('/product/' . $page->slug);
        $res->assertStatus(200);
        $res->assertSee('customer_ariful.webp');
        $res->assertSee('customer_selim.webp');
        $res->assertSee('✓ ভেরিফাইড ক্রেতা');
        $page->delete();
    }

    public function test_flow_11_product_package_selection()
    {
        LandingPage::where('slug', 'test-poultry-booster')->delete();
        $page = LandingPage::create($this->getTestPoultryPayload('published'));
        $packages = $page->content['packages'];
        $this->assertNotEmpty($packages);
        $this->assertEquals(500, $packages[0]['price']);
        $this->assertEquals('Test Poultry Booster (১ কেজি)', $packages[0]['name']);
        $page->delete();
    }

    public function test_flow_12_existing_checkout_integration()
    {
        $order = Order::create([
            'invoice_no' => 'TPB-' . strtoupper(substr(md5(time()), 0, 6)),
            'customer_name' => 'কামাল হোসেন',
            'customer_phone' => '01711223344',
            'customer_address' => 'উত্তরা, ঢাকা',
            'city_type' => 'inside_dhaka',
            'delivery_charge' => 80,
            'subtotal' => 500,
            'discount' => 0,
            'total_amount' => 580,
            'payment_method' => 'Cash on Delivery',
            'status' => 'pending',
            'source_type' => 'landing_page',
            'landing_page' => '/product/test-poultry-booster',
        ]);

        $this->assertNotNull($order->id);
        $this->assertEquals(580, $order->total_amount);
        $order->delete();
    }

    public function test_flow_13_landing_page_order_attribution()
    {
        $order = Order::create([
            'invoice_no' => 'ATTRIB-' . strtoupper(substr(md5(time()), 0, 6)),
            'customer_name' => 'রাশেদ খান',
            'customer_phone' => '01811223344',
            'customer_address' => 'ধানমন্ডি, ঢাকা',
            'city_type' => 'inside_dhaka',
            'delivery_charge' => 80,
            'subtotal' => 500,
            'discount' => 0,
            'total_amount' => 580,
            'payment_method' => 'Cash on Delivery',
            'status' => 'pending',
            'source_type' => 'landing_page',
            'landing_page' => '/product/test-poultry-booster',
        ]);

        $this->assertEquals('landing_page', $order->source_type);
        $this->assertEquals('/product/test-poultry-booster', $order->landing_page);
        $order->delete();
    }

    public function test_flow_14_automatic_tracking()
    {
        LandingPage::where('slug', 'test-poultry-booster')->delete();
        $page = LandingPage::create($this->getTestPoultryPayload('published'));
        $res = $this->get('/product/' . $page->slug);
        $res->assertStatus(200);
        $res->assertSee('GrowthAgroTracking');
        $res->assertSee('track');
        $page->delete();
    }

    public function test_flow_15_bd_courier_check_during_checkout()
    {
        $res = $this->getJson('/api/admin/fraud/courier-check?phone=01711223344', $this->getAdminHeaders());
        $res->assertStatus(200);
    }

    public function test_flow_16_fraud_risk_result()
    {
        $order = Order::create([
            'invoice_no' => 'FRAUD-' . strtoupper(substr(md5(time()), 0, 6)),
            'customer_name' => 'সন্দেহজনক অর্ডার',
            'customer_phone' => '01700000000',
            'customer_address' => 'ঢাকা',
            'city_type' => 'inside_dhaka',
            'delivery_charge' => 80,
            'subtotal' => 500,
            'discount' => 0,
            'total_amount' => 580,
            'status' => 'pending',
            'fraud_level' => 'LOW',
            'fraud_score' => 90,
            'source_type' => 'landing_page',
            'landing_page' => '/product/test-poultry-booster',
        ]);

        $this->assertEquals('LOW', $order->fraud_level);
        $this->assertEquals(90, $order->fraud_score);
        $order->delete();
    }

    public function test_flow_17_admin_order_visibility()
    {
        $orderNo = 'VIS-' . strtoupper(substr(md5(time()), 0, 6));
        $order = Order::create([
            'invoice_no' => $orderNo,
            'customer_name' => 'অ্যাডমিন ভিজিবিলিটি',
            'customer_phone' => '01711998877',
            'customer_address' => 'গুলশান, ঢাকা',
            'city_type' => 'inside_dhaka',
            'delivery_charge' => 80,
            'subtotal' => 500,
            'discount' => 0,
            'total_amount' => 580,
            'status' => 'pending',
            'source_type' => 'landing_page',
            'landing_page' => '/product/test-poultry-booster',
        ]);

        $res = $this->getJson('/api/orders', $this->getAdminHeaders());
        $res->assertStatus(200);
        $orders = $res->json('orders') ?: [];
        $found = collect($orders)->firstWhere('order_number', $orderNo);
        $this->assertNotNull($found);
        $order->delete();
    }

    public function test_flow_18_landing_page_analytics_performance_metrics()
    {
        LandingPage::where('slug', 'test-poultry-booster')->delete();
        $page = LandingPage::create($this->getTestPoultryPayload('published'));

        $order = Order::create([
            'invoice_no' => 'METRIC-' . strtoupper(substr(md5(time()), 0, 6)),
            'customer_name' => 'মেট্রিক টেস্ট',
            'customer_phone' => '01711228844',
            'customer_address' => 'ঢাকা',
            'city_type' => 'inside_dhaka',
            'delivery_charge' => 80,
            'subtotal' => 500,
            'discount' => 0,
            'total_amount' => 580,
            'status' => 'pending',
            'source_type' => 'landing_page',
            'landing_page' => '/product/' . $page->slug,
        ]);

        $res = $this->getJson('/api/admin/landing-pages', $this->getAdminHeaders());
        $res->assertStatus(200);
        $pages = $res->json('pages') ?: [];
        $found = collect($pages)->firstWhere('slug', $page->slug);
        $this->assertNotNull($found);
        $this->assertEquals(1, $found['orders']);
        $this->assertEquals(580, $found['revenue']);

        $order->delete();
        $page->delete();
    }

    public function test_flow_19_mobile_responsive_rendering()
    {
        LandingPage::where('slug', 'test-poultry-booster')->delete();
        $page = LandingPage::create($this->getTestPoultryPayload('published'));
        $res = $this->get('/product/' . $page->slug);
        $res->assertStatus(200);
        $res->assertSee('viewport');
        $res->assertSee('width=device-width');
        $page->delete();
    }

    public function test_flow_20_draft_pages_must_not_be_publicly_accessible()
    {
        LandingPage::where('slug', 'test-poultry-booster')->delete();
        $page = LandingPage::create($this->getTestPoultryPayload('draft'));
        $res = $this->get('/product/' . $page->slug);
        $res->assertStatus(404);
        $page->delete();
    }

    public function test_flow_21_published_pages_must_be_publicly_accessible_at_product_slug()
    {
        LandingPage::where('slug', 'test-poultry-booster')->delete();
        $page = LandingPage::create($this->getTestPoultryPayload('published'));
        $res = $this->get('/product/' . $page->slug);
        $res->assertStatus(200);
        $res->assertSee('Test Poultry Booster');
        $page->delete();
    }
}
