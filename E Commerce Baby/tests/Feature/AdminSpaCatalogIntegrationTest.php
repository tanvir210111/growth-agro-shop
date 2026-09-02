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

class AdminSpaCatalogIntegrationTest extends TestCase
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

    public function test_admin_spa_html_contains_storefront_catalog_panels_and_modals()
    {
        $response = $this->get('/admin');
        $response->assertStatus(200);

        $htmlPath = public_path('admin/index.html');
        $this->assertFileExists($htmlPath);
        $htmlContent = file_get_contents($htmlPath);

        // Verify Sidebar Navigation elements
        $this->assertStringContainsString('id="subnav-products"', $htmlContent);
        $this->assertStringContainsString('id="subnav-categories"', $htmlContent);
        $this->assertStringContainsString('id="subnav-sliders"', $htmlContent);
        $this->assertStringContainsString('id="subnav-header-setting"', $htmlContent);

        // Verify View Panels
        $this->assertStringContainsString('id="view-products"', $htmlContent);
        $this->assertStringContainsString('id="view-categories"', $htmlContent);
        $this->assertStringContainsString('id="view-sliders"', $htmlContent);
        $this->assertStringContainsString('id="view-header-setting"', $htmlContent);

        // Verify Modals
        $this->assertStringContainsString('id="productModal"', $htmlContent);
        $this->assertStringContainsString('id="categoryModal"', $htmlContent);
        $this->assertStringContainsString('id="sliderModal"', $htmlContent);
    }

    public function test_admin_spa_javascript_bundle_contains_catalog_handlers()
    {
        $jsPath = public_path('admin/app.js');
        $this->assertFileExists($jsPath);
        $jsContent = file_get_contents($jsPath);

        $this->assertStringContainsString('loadProductsCatalog', $jsContent);
        $this->assertStringContainsString('loadCategoriesCatalog', $jsContent);
        $this->assertStringContainsString('loadSlidersCatalog', $jsContent);
        $this->assertStringContainsString('loadStorefrontSettings', $jsContent);
        $this->assertStringContainsString('saveProductData', $jsContent);
        $this->assertStringContainsString('saveCategoryData', $jsContent);
        $this->assertStringContainsString('saveSliderData', $jsContent);
        $this->assertStringContainsString('saveStorefrontSettings', $jsContent);
        $this->assertStringContainsString('uploadProductImage', $jsContent);
        $this->assertStringContainsString('/api/admin/products', $jsContent);
        $this->assertStringContainsString('/api/admin/categories', $jsContent);
        $this->assertStringContainsString('/api/admin/sliders', $jsContent);
        $this->assertStringContainsString('/api/admin/settings/storefront', $jsContent);
    }

    public function test_full_end_to_end_admin_catalog_flow_to_storefront()
    {
        // 1. Admin uploads product image
        $fakeImage = UploadedFile::fake()->image('universal_product.jpg', 600, 600);
        $imgUploadRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->postJson('/api/admin/products/upload-media', [
                'image' => $fakeImage,
            ]);
        $imgUploadRes->assertStatus(200);
        $uploadedImageUrl = $imgUploadRes->json('url');
        $this->assertStringStartsWith('/uploads/products/', $uploadedImageUrl);

        // 2. Admin creates a Category
        $catRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->postJson('/api/admin/categories', [
                'title'       => 'Universal Test Category',
                'handle'      => 'universal-test-category',
                'description' => 'Tested category for agricultural solutions',
                'sort_order'  => 1,
                'status'      => true,
            ]);
        $catRes->assertStatus(201);
        $categoryId = $catRes->json('category.id');

        // 3. Admin creates a Product linked to that Category
        $prodRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->postJson('/api/admin/products', [
                'title'             => 'Universal Test Product',
                'slug'              => 'universal-test-product',
                'sku'               => 'UTP-001',
                'category_id'       => $categoryId,
                'regular_price'     => 1200,
                'sale_price'        => 1000,
                'stock'             => 10,
                'sizes'             => ['1 KG Pack', '5 KG Bag'],
                'featured_image'    => $uploadedImageUrl,
                'short_description' => 'Premium high-potency test product.',
                'description'       => '<p>Complete specifications and instructions.</p>',
                'is_new_arrival'    => true,
                'is_bestseller'     => true,
                'is_featured'       => true,
                'status'            => true,
            ]);
        $prodRes->assertStatus(201);
        $productId = $prodRes->json('product.id');

        // 4. Verify DB records
        $this->assertDatabaseHas('categories', ['id' => $categoryId, 'handle' => 'universal-test-category']);
        $this->assertDatabaseHas('products', [
            'id'             => $productId,
            'slug'           => 'universal-test-product',
            'sku'            => 'UTP-001',
            'sale_price'     => 1000,
            'is_new_arrival' => 1,
            'is_bestseller'  => 1,
        ]);

        // 5. Verify Storefront /categories page
        $catsPage = $this->get('/categories');
        $catsPage->assertStatus(200);
        $catsPage->assertSee('Universal Test Category');

        // 6. Verify Storefront /category/universal-test-category
        $catShowPage = $this->get('/category/universal-test-category');
        $catShowPage->assertStatus(200);
        $catShowPage->assertSee('Universal Test Product');

        // 7. Verify Storefront /shop page
        $shopPage = $this->get('/shop');
        $shopPage->assertStatus(200);
        $shopPage->assertSee('Universal Test Product');

        // 8. Verify Storefront Homepage / (New Arrivals & Bestsellers)
        $homePage = $this->get('/');
        $homePage->assertStatus(200);
        $homePage->assertSee('Universal Test Product');

        // 9. Verify Storefront Product Detail Page /product/universal-test-product
        $prodDetailPage = $this->get('/product/universal-test-product');
        $prodDetailPage->assertStatus(200);
        $prodDetailPage->assertSee('Universal Test Product');
        $prodDetailPage->assertSee('1 KG Pack');
        $prodDetailPage->assertSee('5 KG Bag');

        // 10. Verify Add to Cart
        $cartAddRes = $this->postJson('/cart/add', [
            'product_id' => $productId,
            'size'       => '1 KG Pack',
            'quantity'   => 2,
        ]);
        $cartAddRes->assertStatus(200);
        $cartAddRes->assertJsonPath('success', true);
        $cartAddRes->assertJsonPath('cart.item_count', 2);
        $cartAddRes->assertJsonPath('cart.subtotal', 2000);

        // 11. Verify Checkout Page
        $checkoutPage = $this->get('/checkout');
        $checkoutPage->assertStatus(200);
        $checkoutPage->assertSee('Universal Test Product');

        // 12. Cleanup uploaded file
        $createdFilePath = public_path(ltrim($uploadedImageUrl, '/'));
        if (File::exists($createdFilePath)) {
            File::delete($createdFilePath);
        }
    }

    public function test_landing_page_priority_remains_intact_over_admin_created_products()
    {
        LandingPage::create([
            'slug'         => 'chicken-booster',
            'name'         => 'Chicken Booster Campaign',
            'theme'        => 'chicken-booster',
            'status'       => 'published',
            'product_id'   => 'chicken-booster',
            'product_name' => 'Chicken Booster (চিকেন বুস্টার)',
            'content'      => LandingPage::getDefaultMasterContent(),
        ]);

        Product::create([
            'title'         => 'Chicken Booster Admin Product',
            'slug'          => 'chicken-booster',
            'sku'           => 'CB-ADMIN-PROD',
            'regular_price' => 990,
            'sale_price'    => 990,
            'status'        => true,
        ]);

        $res = $this->get('/product/chicken-booster');
        $res->assertStatus(200);
        $res->assertViewIs('pages.landing-page');
    }
}
