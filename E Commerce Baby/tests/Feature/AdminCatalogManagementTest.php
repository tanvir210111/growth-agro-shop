<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\LandingPage;
use App\Models\Product;
use App\Models\Slider;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminCatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::firstOrCreate(
            ['email' => 'admin@growthagro.shop'],
            [
                'name'     => 'Admin User',
                'password' => bcrypt('AdminSecret123!'),
                'role'     => 'Super Admin',
            ]
        );
    }

    /* =========================================================================
     * 1. CATEGORY MANAGEMENT TESTS
     * ========================================================================= */

    public function test_unauthenticated_user_cannot_access_category_crud()
    {
        $response = $this->getJson('/api/admin/categories');
        $response->assertStatus(401);

        $createRes = $this->postJson('/api/admin/categories', ['title' => 'New Category']);
        $createRes->assertStatus(401);
    }

    public function test_authenticated_admin_can_list_and_create_category()
    {
        $response = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->postJson('/api/admin/categories', [
                'title'       => 'Poultry Medicine',
                'handle'      => 'poultry-medicine',
                'description' => 'Quality medicines for poultry farming',
                'sort_order'  => 1,
                'status'      => true,
            ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('categories', ['handle' => 'poultry-medicine', 'title' => 'Poultry Medicine']);

        $listRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->getJson('/api/admin/categories');
        $listRes->assertStatus(200);
        $listRes->assertJsonFragment(['title' => 'Poultry Medicine']);
    }

    public function test_duplicate_category_handle_is_rejected()
    {
        Category::create(['title' => 'Existing Cat', 'handle' => 'existing-cat', 'status' => true]);

        $response = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->postJson('/api/admin/categories', [
                'title'  => 'Duplicate Cat',
                'handle' => 'existing-cat',
            ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_toggle_category_status_and_update()
    {
        $cat = Category::create(['title' => 'Dairy Feed', 'handle' => 'dairy-feed', 'status' => true]);

        $toggleRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->patchJson("/api/admin/categories/{$cat->id}/status");
        $toggleRes->assertStatus(200);
        $this->assertFalse((bool)$cat->fresh()->status);

        $updateRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->putJson("/api/admin/categories/{$cat->id}", [
                'title' => 'Updated Dairy Feed',
            ]);
        $updateRes->assertStatus(200);
        $this->assertEquals('Updated Dairy Feed', $cat->fresh()->title);
    }

    public function test_category_with_assigned_products_cannot_be_deleted()
    {
        $cat = Category::create(['title' => 'Aqua Care', 'handle' => 'aqua-care', 'status' => true]);
        Product::create([
            'title'           => 'Fish Feed Pellet 1KG',
            'slug'            => 'fish-feed-pellet-1kg',
            'sku'             => 'AQUA-01',
            'category_id'     => $cat->id,
            'regular_price'   => 500,
            'sale_price'      => 500,
            'status'          => true,
        ]);

        $delRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->deleteJson("/api/admin/categories/{$cat->id}");
        $delRes->assertStatus(422);
        $this->assertDatabaseHas('categories', ['id' => $cat->id]);
    }

    /* =========================================================================
     * 2. PRODUCT MANAGEMENT TESTS
     * ========================================================================= */

    public function test_unauthenticated_user_cannot_access_product_crud()
    {
        $res = $this->getJson('/api/admin/products');
        $res->assertStatus(401);

        $createRes = $this->postJson('/api/admin/products', [
            'title' => 'Unauthorized Product',
            'sku'   => 'UNAUTH-01',
            'regular_price' => 100,
        ]);
        $createRes->assertStatus(401);
    }

    public function test_admin_can_create_product_with_validation_and_options()
    {
        $cat = Category::create(['title' => 'Livestock Nutrition', 'handle' => 'livestock-nutrition', 'status' => true]);

        $response = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->postJson('/api/admin/products', [
                'title'             => 'Super Calf Growth Premix',
                'slug'              => 'super-calf-growth-premix',
                'sku'               => 'NUTR-CGP-01',
                'category_id'       => $cat->id,
                'regular_price'     => 1500,
                'sale_price'        => 1200,
                'stock'             => 40,
                'sizes'             => ['1 KG Pack', '5 KG Bag'],
                'short_description' => 'Complete calf vitamins and minerals.',
                'description'       => '<p>High potency micro-encapsulated formula.</p>',
                'is_new_arrival'    => true,
                'is_bestseller'     => true,
                'status'            => true,
            ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('products', [
            'sku'            => 'NUTR-CGP-01',
            'slug'           => 'super-calf-growth-premix',
            'is_new_arrival' => 1,
            'is_bestseller'  => 1,
        ]);
    }

    public function test_duplicate_product_sku_is_rejected()
    {
        Product::create([
            'title'         => 'Prod A',
            'slug'          => 'prod-a',
            'sku'           => 'UNIQUE-SKU-99',
            'regular_price' => 200,
            'sale_price'    => 200,
            'status'        => true,
        ]);

        $response = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->postJson('/api/admin/products', [
                'title'         => 'Prod B',
                'slug'          => 'prod-b',
                'sku'           => 'UNIQUE-SKU-99',
                'regular_price' => 300,
            ]);

        $response->assertStatus(422);
    }

    public function test_invalid_price_and_negative_stock_are_rejected()
    {
        $response = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->postJson('/api/admin/products', [
                'title'         => 'Bad Product',
                'sku'           => 'BAD-01',
                'regular_price' => -50,
                'stock'         => -10,
            ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_update_product_and_toggle_status()
    {
        $p = Product::create([
            'title'         => 'Initial Title',
            'slug'          => 'initial-title',
            'sku'           => 'INIT-01',
            'regular_price' => 100,
            'sale_price'    => 100,
            'status'        => true,
        ]);

        $updateRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->putJson("/api/admin/products/{$p->id}", [
                'title'         => 'Updated Title',
                'sku'           => 'INIT-01',
                'regular_price' => 150,
            ]);
        $updateRes->assertStatus(200);
        $this->assertEquals('Updated Title', $p->fresh()->title);

        $toggleRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->patchJson("/api/admin/products/{$p->id}/status");
        $toggleRes->assertStatus(200);
        $this->assertFalse((bool)$p->fresh()->status);
    }

    /* =========================================================================
     * 3. MEDIA UPLOAD TESTS (ISOLATION FROM LANDING PAGES)
     * ========================================================================= */

    public function test_product_media_upload_is_isolated_and_validated()
    {
        $fakeImage = UploadedFile::fake()->image('product_test.jpg', 600, 600);

        $response = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->postJson('/api/admin/products/upload-media', [
                'image' => $fakeImage,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $url = $response->json('url');
        $this->assertStringStartsWith('/uploads/products/', $url);

        // Verify it was NOT saved in landing-pages upload directory
        $this->assertStringNotContainsString('landing-pages', $url);

        // Cleanup created test file
        $createdFilePath = public_path(ltrim($url, '/'));
        if (File::exists($createdFilePath)) {
            File::delete($createdFilePath);
        }
    }

    public function test_unsafe_executable_media_upload_is_rejected()
    {
        $fakePhpFile = UploadedFile::fake()->create('malicious.php', 10, 'text/x-php');

        $response = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->postJson('/api/admin/products/upload-media', [
                'image' => $fakePhpFile,
            ]);

        $response->assertStatus(422);
    }

    /* =========================================================================
     * 4. SLIDER MANAGEMENT TESTS
     * ========================================================================= */

    public function test_admin_can_create_and_manage_hero_slider()
    {
        $response = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->postJson('/api/admin/sliders', [
                'title'       => 'Summer Campaign Special',
                'subtitle'    => 'Up to 30% Off',
                'image'       => '/uploads/sliders/summer.jpg',
                'link'        => '/shop',
                'button_text' => 'Shop Now',
                'sort_order'  => 1,
                'status'      => true,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('sliders', ['title' => 'Summer Campaign Special']);

        $listRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->getJson('/api/admin/sliders');
        $listRes->assertStatus(200);
        $listRes->assertJsonFragment(['title' => 'Summer Campaign Special']);
    }

    /* =========================================================================
     * 5. STOREFRONT SETTINGS & PROMO BANNERS TESTS
     * ========================================================================= */

    public function test_admin_can_get_and_update_storefront_branding_and_promos()
    {
        $getRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->getJson('/api/admin/settings/storefront');
        $getRes->assertStatus(200);

        $updateRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->postJson('/api/admin/settings/storefront', [
                'site_name'            => 'Growth Agro Official',
                'site_title'           => 'Growth Agro Official | Premium E-Commerce',
                'support_phone'        => '01700-998877',
                'support_email'        => 'sales@growthagro.shop',
                'promo_banner_1_title' => 'SPECIAL ORGANIC FEED',
                'promo_banner_1_link'  => '/category/poultry-feed',
            ]);

        $updateRes->assertStatus(200);
        $this->assertEquals('Growth Agro Official', Setting::get('site_name'));
        $this->assertEquals('01700-998877', Setting::get('support_phone'));
        $this->assertEquals('SPECIAL ORGANIC FEED', Setting::get('promo_banner_1_title'));
    }

    /* =========================================================================
     * 6. STOREFRONT INTEGRATION & LANDING PAGE PRECEDENCE
     * ========================================================================= */

    public function test_admin_created_product_appears_on_storefront_and_cart()
    {
        $cat = Category::create(['title' => 'Vitamins', 'handle' => 'vitamins', 'status' => true]);

        $product = Product::create([
            'title'             => 'Liquid Vitamin AD3E 500ml',
            'slug'              => 'liquid-vitamin-ad3e-500ml',
            'sku'               => 'VIT-AD3E-500',
            'category_id'       => $cat->id,
            'category_handle'   => 'vitamins',
            'regular_price'     => 600,
            'sale_price'        => 520,
            'sizes'             => ['500ml Bottle', '1 Liter Bottle'],
            'short_description' => 'Concentrated oral vitamin supplement.',
            'status'            => true,
            'is_new_arrival'    => true,
            'is_bestseller'     => true,
        ]);

        // 1. Appears in homepage new arrivals
        $homeRes = $this->get('/');
        $homeRes->assertStatus(200);
        $homeRes->assertSee('Liquid Vitamin AD3E 500ml');

        // 2. Product detail page loads with options
        $prodRes = $this->get('/product/liquid-vitamin-ad3e-500ml');
        $prodRes->assertStatus(200);
        $prodRes->assertSee('Liquid Vitamin AD3E 500ml');
        $prodRes->assertSee('500ml Bottle');

        // 3. Add to cart works seamlessly
        $cartRes = $this->postJson('/cart/add', [
            'product_id' => $product->id,
            'size'       => '500ml Bottle',
            'quantity'   => 1,
        ]);
        $cartRes->assertStatus(200);
        $cartRes->assertJsonPath('success', true);
        $cartRes->assertJsonPath('cart.item_count', 1);
        $cartRes->assertJsonPath('cart.subtotal', 520);
    }

    public function test_landing_page_priority_remains_intact_over_storefront_product()
    {
        LandingPage::create([
            'slug'         => 'chicken-booster',
            'name'         => 'Chicken Booster Landing',
            'theme'        => 'chicken-booster',
            'status'       => 'published',
            'product_id'   => 'chicken-booster',
            'product_name' => 'Chicken Booster (চিকেন বুস্টার)',
            'content'      => LandingPage::getDefaultMasterContent(),
        ]);

        Product::create([
            'title'         => 'Chicken Booster Catalog Item',
            'slug'          => 'chicken-booster',
            'sku'           => 'CB-STORE-01',
            'regular_price' => 990,
            'sale_price'    => 990,
            'status'        => true,
        ]);

        $res = $this->get('/product/chicken-booster');
        $res->assertStatus(200);
        $res->assertViewIs('pages.landing-page');
    }
}
