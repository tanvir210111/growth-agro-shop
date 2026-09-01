<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\LandingPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandingPageBuilderTest extends TestCase
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

    public function test_can_list_landing_pages_with_performance_metrics()
    {
        $response = $this->getJson('/api/admin/landing-pages', $this->getAdminHeaders());

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'count',
            'pages' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'status',
                    'theme',
                    'public_url',
                    'visitors',
                    'sessions',
                    'orders',
                    'revenue',
                    'conversion_rate',
                    'aov',
                ]
            ]
        ]);
    }

    public function test_can_fetch_master_defaults()
    {
        $response = $this->getJson('/api/admin/landing-pages/master-defaults', $this->getAdminHeaders());

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'content' => [
                'header',
                'hero',
                'packages',
                'benefits_section_1',
                'benefits_section_2',
                'video_reviews',
                'usage_guide',
                'testimonials',
                'faqs',
                'checkout',
                'footer',
            ],
            'delivery_config' => [
                'delivery_type',
                'charge_inside_dhaka',
                'charge_outside_dhaka',
            ],
            'theme_config' => [
                'primary_color',
                'secondary_color',
                'btn_red',
            ],
            'section_order'
        ]);
    }

    public function test_can_check_slug_availability()
    {
        $response = $this->getJson('/api/admin/landing-pages/check-slug?slug=chicken-booster');
        $response->assertStatus(200);
        $response->assertJson([
            'available' => false,
        ]);

        $response = $this->getJson('/api/admin/landing-pages/check-slug?slug=brand-new-custom-slug-' . time());
        $response->assertStatus(200);
        $response->assertJson([
            'available' => true,
        ]);
    }

    public function test_can_create_new_landing_page()
    {
        $slug = 'test-agro-tonic-' . time();
        $payload = [
            'name' => 'Poultry Liver Tonic',
            'slug' => $slug,
            'status' => 'published',
            'theme' => 'chicken-booster',
            'product_name' => 'Poultry Liver Tonic 500ml',
            'title' => 'Poultry Liver Tonic — Growth Agro',
            'meta_title' => 'Poultry Liver Tonic — Growth Agro',
            'meta_description' => 'Fast recovery for poultry liver and digestion.',
            'delivery_config' => [
                'delivery_type' => 'paid',
                'charge_inside_dhaka' => 60,
                'charge_outside_dhaka' => 120,
                'same_charge_everywhere' => false,
                'free_delivery_above' => true,
                'free_delivery_threshold' => 1500,
            ],
            'theme_config' => LandingPage::getDefaultThemeConfig(),
            'content' => LandingPage::getDefaultMasterContent(),
            'section_order' => LandingPage::getDefaultSectionOrder(),
        ];

        $response = $this->postJson('/api/admin/landing-pages', $payload, $this->getAdminHeaders());

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'slug' => $slug,
        ]);

        $this->assertDatabaseHas('landing_pages', [
            'slug' => $slug,
            'name' => 'Poultry Liver Tonic',
            'status' => 'published',
        ]);

        // Test public route rendering
        $publicRes = $this->get('/product/' . $slug);
        $publicRes->assertStatus(200);
        $publicRes->assertSee('Poultry Liver Tonic');
        $publicRes->assertSee('GrowthAgroTracking');
    }

    public function test_can_duplicate_landing_page()
    {
        $original = LandingPage::where('slug', 'chicken-booster')->first();
        $this->assertNotNull($original);

        $response = $this->postJson("/api/admin/landing-pages/{$original->id}/duplicate", [], $this->getAdminHeaders());

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);

        $dupId = $response->json('duplicate_id');
        $duplicate = LandingPage::find($dupId);
        $this->assertNotNull($duplicate);
        $this->assertStringContainsString('Copy', $duplicate->name);
        $this->assertEquals('draft', $duplicate->status);
    }

    public function test_can_toggle_landing_page_status()
    {
        $page = LandingPage::create([
            'name' => 'Status Test Page',
            'slug' => 'status-test-page-' . time(),
            'status' => 'draft',
            'theme' => 'chicken-booster',
            'content' => LandingPage::getDefaultMasterContent(),
            'delivery_config' => LandingPage::getDefaultDeliveryConfig(),
            'theme_config' => LandingPage::getDefaultThemeConfig(),
            'section_order' => LandingPage::getDefaultSectionOrder(),
        ]);

        $response = $this->patchJson("/api/admin/landing-pages/{$page->id}/status", ['status' => 'published'], $this->getAdminHeaders());
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'published',
        ]);

        $this->assertEquals('published', $page->fresh()->status);
    }

    public function test_preview_mode_renders_draft_page()
    {
        $draftPage = LandingPage::create([
            'name' => 'Draft Preview Product',
            'slug' => 'draft-preview-' . time(),
            'status' => 'draft',
            'theme' => 'chicken-booster',
            'content' => LandingPage::getDefaultMasterContent(),
            'delivery_config' => LandingPage::getDefaultDeliveryConfig(),
            'theme_config' => LandingPage::getDefaultThemeConfig(),
            'section_order' => LandingPage::getDefaultSectionOrder(),
        ]);

        // Accessing public URL without preview should 404
        $publicRes = $this->get('/product/' . $draftPage->slug);
        $publicRes->assertStatus(404);

        // Accessing preview route should 200
        $previewRes = $this->get("/admin/landing-pages/{$draftPage->id}/preview");
        $previewRes->assertStatus(200);
        $previewRes->assertSee('Draft Preview Product');
    }

    public function test_can_upload_media_image()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('review_avatar.jpg', 400, 400);

        $response = $this->post('/api/admin/landing-pages/upload-media', [
            'image' => $file,
        ], $this->getAdminHeaders());

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertNotNull($response->json('url'));
    }

    public function test_can_update_existing_landing_page()
    {
        $page = LandingPage::where('slug', 'chicken-booster')->first();
        $this->assertNotNull($page);

        $updatedData = [
            'name' => 'Chicken Booster Updated Edition',
            'product_name' => 'Chicken Booster Premium 2kg',
            'delivery_config' => [
                'delivery_type' => 'paid',
                'charge_inside_dhaka' => 80,
                'charge_outside_dhaka' => 150,
                'same_charge_everywhere' => false,
                'free_delivery_above' => true,
                'free_delivery_threshold' => 3000,
            ],
            'content' => array_merge(LandingPage::getDefaultMasterContent(), [
                'testimonials' => [
                    'section_title' => 'সরাসরি খামারিদের রিভিউ',
                    'items' => [
                        [
                            'name' => 'হাজী মো: কামরুল হাসান',
                            'location' => 'কুমিল্লা',
                            'rating' => 5,
                            'review_text' => '১০ দিনে ওজন বৃদ্ধি অনেক লক্ষণীয়!',
                            'photo' => '/uploads/landing-pages/test-photo.webp',
                            'is_verified' => true,
                        ]
                    ]
                ]
            ])
        ];

        $response = $this->putJson("/api/admin/landing-pages/{$page->id}", $updatedData, $this->getAdminHeaders());

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'slug' => 'chicken-booster',
        ]);

        $fresh = $page->fresh();
        $this->assertEquals('Chicken Booster Updated Edition', $fresh->name);
        $this->assertEquals(80, $fresh->delivery_config['charge_inside_dhaka']);
        $this->assertEquals(150, $fresh->delivery_config['charge_outside_dhaka']);
        $this->assertEquals('সরাসরি খামারিদের রিভিউ', $fresh->content['testimonials']['section_title']);

        // Check public render displays new data
        $publicRes = $this->get('/product/chicken-booster');
        $publicRes->assertStatus(200);
        $publicRes->assertSee('হাজী মো: কামরুল হাসান');
        $publicRes->assertSee('কুমিল্লা');
        $publicRes->assertSee('১০ দিনে ওজন বৃদ্ধি');
    }
}
