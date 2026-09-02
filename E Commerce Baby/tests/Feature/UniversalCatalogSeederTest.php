<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\LandingPage;
use App\Models\Product;
use App\Models\Slider;
use Database\Seeders\UniversalCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversalCatalogSeederTest extends TestCase
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

    public function test_seeder_creates_all_11_universal_categories()
    {
        $this->seed(UniversalCatalogSeeder::class);

        $expectedHandles = [
            'electronics',
            'fashion',
            'beauty',
            'home-living',
            'grocery',
            'accessories',
            'health',
            'sports',
            'books',
            'automotive',
            'toys-games',
        ];

        $this->assertEquals(11, Category::count());

        foreach ($expectedHandles as $handle) {
            $this->assertDatabaseHas('categories', [
                'handle' => $handle,
                'status' => 1,
            ]);
        }
    }

    public function test_seeder_is_idempotent_and_creates_no_duplicates_on_repeat()
    {
        // Run first time
        $this->seed(UniversalCatalogSeeder::class);
        $initialCatCount = Category::count();
        $initialProdCount = Product::count();

        $this->assertEquals(11, $initialCatCount);
        $this->assertEquals(55, $initialProdCount);

        // Run second time
        $this->seed(UniversalCatalogSeeder::class);

        $this->assertEquals($initialCatCount, Category::count());
        $this->assertEquals($initialProdCount, Product::count());
    }

    public function test_seeder_creates_55_products_correctly_linked_to_categories()
    {
        $this->seed(UniversalCatalogSeeder::class);

        $categories = Category::withCount('products')->get();

        $this->assertCount(11, $categories);
        foreach ($categories as $category) {
            $this->assertEquals(5, $category->products_count, "Category {$category->handle} should have exactly 5 products.");
        }

        // Verify sample product integrity
        $smartWatch = Product::where('sku', 'ELEC-SWP4-01')->first();
        $this->assertNotNull($smartWatch);
        $this->assertEquals('smart-watch-pro-4', $smartWatch->slug);
        $this->assertEquals('electronics', $smartWatch->category_handle);
        $this->assertEquals(3500, $smartWatch->regular_price);
        $this->assertEquals(2850, $smartWatch->sale_price);
        $this->assertEquals(45, $smartWatch->stock);
        $this->assertContains('Midnight Blue', $smartWatch->sizes);
        $this->assertTrue((bool)$smartWatch->is_new_arrival);
        $this->assertTrue((bool)$smartWatch->is_bestseller);
        $this->assertTrue((bool)$smartWatch->is_featured);
    }

    public function test_admin_categories_and_products_apis_expose_seeded_records()
    {
        $this->seed(UniversalCatalogSeeder::class);

        // 1. Admin Categories API
        $catRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->getJson('/api/admin/categories');
        $catRes->assertStatus(200);
        $catRes->assertJsonCount(11, 'categories');
        $catRes->assertJsonFragment(['handle' => 'electronics', 'title' => 'Electronics']);

        // 2. Admin Products API
        $prodRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->getJson('/api/admin/products?per_page=100');
        $prodRes->assertStatus(200);
        $prodRes->assertJsonPath('meta.total', 55);
        $prodRes->assertJsonFragment(['sku' => 'FASH-MCT-01', 'title' => "Men's Cotton T-Shirt"]);
    }

    public function test_newly_manually_created_category_appears_alongside_seeded_categories()
    {
        $this->seed(UniversalCatalogSeeder::class);

        // Manually create an extra category via Admin API
        $createRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->postJson('/api/admin/categories', [
                'title'       => 'Gardening & Seeds',
                'handle'      => 'gardening-seeds',
                'description' => 'Organic seeds and farming equipment',
            ]);
        $createRes->assertStatus(201);

        $listRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->getJson('/api/admin/categories');
        $listRes->assertStatus(200);
        $listRes->assertJsonCount(12, 'categories');
        $listRes->assertJsonFragment(['handle' => 'gardening-seeds', 'title' => 'Gardening & Seeds']);
    }

    public function test_storefront_renders_seeded_catalog_across_all_pages()
    {
        $this->seed(UniversalCatalogSeeder::class);

        // 1. Homepage loads categories, new arrivals, bestsellers
        $homeRes = $this->get('/');
        $homeRes->assertStatus(200);
        $homeRes->assertSee('Electronics');
        $homeRes->assertSee('Fashion');
        $homeRes->assertSee('Smart Watch Pro 4');
        $homeRes->assertSee('Vitamin C Face Serum');

        // 2. All Categories page
        $catsRes = $this->get('/categories');
        $catsRes->assertStatus(200);
        $catsRes->assertSee('Electronics');
        $catsRes->assertSee('Toys & Games');

        // 3. Shop Catalog page
        $shopRes = $this->get('/shop');
        $shopRes->assertStatus(200);
        $shopRes->assertSee("Men's Cotton T-Shirt");

        // 4. Category-filtered Catalog page
        $catShowRes = $this->get('/category/electronics');
        $catShowRes->assertStatus(200);
        $catShowRes->assertSee('Smart Watch Pro 4');
        $catShowRes->assertSee('Wireless Earbuds');

        // 5. Product Details page
        $prodShowRes = $this->get('/product/smart-watch-pro-4');
        $prodShowRes->assertStatus(200);
        $prodShowRes->assertSee('Smart Watch Pro 4');
        $prodShowRes->assertSee('Midnight Blue');
        $prodShowRes->assertSee('2,850');

        // 6. Add to Cart works for seeded product
        $prod = Product::where('slug', 'smart-watch-pro-4')->first();
        $cartRes = $this->postJson('/cart/add', [
            'product_id' => $prod->id,
            'size'       => 'Midnight Blue',
            'quantity'   => 1,
        ]);
        $cartRes->assertStatus(200);
        $cartRes->assertJsonPath('success', true);
        $cartRes->assertJsonPath('cart.item_count', 1);
        $cartRes->assertJsonPath('cart.subtotal', 2850);
    }

    public function test_all_11_categories_have_valid_media_and_files_exist()
    {
        $this->seed(UniversalCatalogSeeder::class);

        $categories = Category::all();
        $this->assertCount(11, $categories);

        foreach ($categories as $cat) {
            $this->assertNotEmpty($cat->image, "Category {$cat->handle} has empty image.");
            $this->assertStringStartsWith('/images/categories/', $cat->image, "Category {$cat->handle} image path must start with /images/categories/");
            $this->assertStringEndsWith('.svg', $cat->image);
            $this->assertStringNotContainsString('landing-pages', $cat->image);
            $this->assertStringNotContainsString('uploads/products', $cat->image);

            $relative = ltrim($cat->image, '/');
            $this->assertFileExists(public_path($relative), "Media file {$cat->image} for category {$cat->handle} does not exist on disk.");
        }
    }

    public function test_all_55_products_have_featured_media_and_files_exist()
    {
        $this->seed(UniversalCatalogSeeder::class);

        $products = Product::all();
        $this->assertCount(55, $products);

        foreach ($products as $prod) {
            $this->assertNotEmpty($prod->featured_image, "Product {$prod->sku} has empty featured_image.");
            $this->assertStringStartsWith('/uploads/products/', $prod->featured_image, "Product {$prod->sku} media must start with /uploads/products/");
            $this->assertStringNotContainsString('landing-pages', $prod->featured_image);
            $this->assertStringNotContainsString('/images/logo.png', $prod->featured_image);

            $relative = ltrim($prod->featured_image, '/');
            $this->assertFileExists(public_path($relative), "Media file {$prod->featured_image} for product {$prod->sku} does not exist on disk.");

            // Gallery check
            $this->assertIsArray($prod->gallery_images);
            $this->assertNotEmpty($prod->gallery_images);
            foreach ($prod->gallery_images as $gImg) {
                $this->assertStringStartsWith('/uploads/products/', $gImg);
                $this->assertStringNotContainsString('landing-pages', $gImg);
            }
        }
    }

    public function test_seeder_does_not_overwrite_admin_replaced_product_media()
    {
        // 1. Initial seed
        $this->seed(UniversalCatalogSeeder::class);

        $prod = Product::where('slug', 'smart-watch-pro-4')->first();
        $this->assertEquals('/uploads/products/smart-watch-pro-4.svg', $prod->featured_image);

        // 2. Admin replaces product image
        $customAdminImage = '/uploads/products/custom-admin-watch-' . time() . '.webp';
        $customGallery = ['/uploads/products/custom-admin-gallery-1.webp'];

        $updateRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->putJson("/api/admin/products/{$prod->id}", [
                'title'          => 'Smart Watch Pro 4 (Custom Edition)',
                'featured_image' => $customAdminImage,
                'gallery_images' => $customGallery,
            ]);
        $updateRes->assertStatus(200);

        $prod->refresh();
        $this->assertEquals($customAdminImage, $prod->featured_image);
        $this->assertEquals($customGallery, $prod->gallery_images);

        // 3. Run seeder again
        $this->seed(UniversalCatalogSeeder::class);

        // 4. Verify custom media is strictly preserved
        $prod->refresh();
        $this->assertEquals($customAdminImage, $prod->featured_image, "Seeder overwrote admin custom featured_image!");
        $this->assertEquals($customGallery, $prod->gallery_images, "Seeder overwrote admin custom gallery_images!");
    }

    public function test_seeder_does_not_overwrite_admin_replaced_category_media()
    {
        // 1. Initial seed
        $this->seed(UniversalCatalogSeeder::class);

        $cat = Category::where('handle', 'electronics')->first();
        $this->assertEquals('/images/categories/electronics.svg', $cat->image);

        // 2. Admin replaces category image
        $customCatImage = '/uploads/products/custom-electronics-icon-' . time() . '.webp';
        $updateRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->putJson("/api/admin/categories/{$cat->id}", [
                'title' => 'Electronics & Smart Tech',
                'image' => $customCatImage,
            ]);
        $updateRes->assertStatus(200);

        $cat->refresh();
        $this->assertEquals($customCatImage, $cat->image);

        // 3. Run seeder again
        $this->seed(UniversalCatalogSeeder::class);

        // 4. Verify custom category media is preserved
        $cat->refresh();
        $this->assertEquals($customCatImage, $cat->image, "Seeder overwrote admin custom category image!");
    }

    public function test_product_service_fallback_uses_universal_placeholder()
    {
        $service = new \App\Services\ProductService();

        // Product without image
        $blankProduct = new Product([
            'id'             => 999,
            'title'          => 'Test Blank Product',
            'slug'           => 'test-blank-product',
            'sku'            => 'TEST-BLANK-999',
            'regular_price'  => 1000,
            'sale_price'     => 800,
            'featured_image' => null,
        ]);

        $formatted = $service->formatProduct($blankProduct);
        $this->assertNotEquals('/images/logo.png', $formatted['primary_image']);
        $this->assertEquals('/images/placeholder.webp', $formatted['primary_image']);

        // Category without image
        $blankCategory = new Category([
            'title'  => 'Blank Category',
            'handle' => 'blank-cat',
            'image'  => null,
        ]);

        $formattedCat = $service->formatCategory($blankCategory);
        $this->assertNotEquals('/images/banners/all-collection.jpg', $formattedCat['image']);
        $this->assertEquals('/images/placeholder.webp', $formattedCat['image']);
    }

    public function test_landing_page_priority_remains_intact_over_seeded_product()
    {
        $this->seed(UniversalCatalogSeeder::class);

        // Create landing page that matches a seeded product slug
        LandingPage::create([
            'slug'         => 'smart-watch-pro-4',
            'name'         => 'Smart Watch Special Campaign',
            'theme'        => 'smart-watch',
            'status'       => 'published',
            'product_id'   => 'smart-watch-pro-4',
            'product_name' => 'Smart Watch Pro 4 Campaign',
            'content'      => LandingPage::getDefaultMasterContent(),
        ]);

        $res = $this->get('/product/smart-watch-pro-4');
        $res->assertStatus(200);
        $res->assertViewIs('pages.landing-page');
    }

    public function test_seeder_creates_3_canonical_hero_sliders_with_valid_media_and_paths()
    {
        $this->seed(UniversalCatalogSeeder::class);

        $sliders = Slider::orderBy('sort_order', 'asc')->get();
        $this->assertCount(3, $sliders);

        $expectedImages = [
            '/uploads/sliders/hero_banner_1.webp',
            '/uploads/sliders/hero_banner_2.webp',
            '/uploads/sliders/hero_banner_3.webp',
        ];

        foreach ($sliders as $idx => $slider) {
            $this->assertEquals($expectedImages[$idx], $slider->image);
            $this->assertTrue((bool)$slider->status);
            $this->assertStringStartsWith('/uploads/sliders/', $slider->image);
            $this->assertStringNotContainsString('landing-pages', $slider->image);
            $this->assertStringNotContainsString('images/logo.png', $slider->image);

            $relative = ltrim($slider->image, '/');
            $this->assertFileExists(public_path($relative), "Slider image {$slider->image} does not exist on disk.");
        }
    }

    public function test_seeder_does_not_duplicate_sliders_on_multiple_runs()
    {
        $this->seed(UniversalCatalogSeeder::class);
        $this->assertEquals(3, Slider::count());

        $this->seed(UniversalCatalogSeeder::class);
        $this->assertEquals(3, Slider::count());
    }

    public function test_seeder_does_not_overwrite_admin_customized_slider_media_or_content()
    {
        $this->seed(UniversalCatalogSeeder::class);

        $slider = Slider::where('sort_order', 1)->first();
        $this->assertNotNull($slider);

        // Admin edits slider 1
        $customImage = '/uploads/sliders/admin-custom-banner-' . time() . '.webp';
        $updateRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->putJson("/api/admin/sliders/{$slider->id}", [
                'title'       => 'Custom Admin Flash Deal',
                'subtitle'    => 'FLASH DEAL 50% OFF',
                'image'       => $customImage,
                'link'        => '/shop?deal=flash',
                'button_text' => 'GRAB DEAL NOW',
                'sort_order'  => 1,
                'status'      => true,
            ]);
        $updateRes->assertStatus(200);

        $slider->refresh();
        $this->assertEquals($customImage, $slider->image);
        $this->assertEquals('Custom Admin Flash Deal', $slider->title);

        // Re-run seeder
        $this->seed(UniversalCatalogSeeder::class);

        $slider->refresh();
        $this->assertEquals($customImage, $slider->image, "Seeder overwrote admin custom slider image!");
        $this->assertEquals('Custom Admin Flash Deal', $slider->title, "Seeder overwrote admin custom slider title!");
    }

    public function test_seeder_preserves_newly_created_admin_slider()
    {
        $this->seed(UniversalCatalogSeeder::class);
        $this->assertEquals(3, Slider::count());

        // Admin creates a 4th slider
        $createRes = $this->withHeaders(['x-admin-token' => 'adm_session'])
            ->postJson('/api/admin/sliders', [
                'title'       => 'Fourth Exclusive Slider',
                'subtitle'    => 'SPECIAL PROMO',
                'image'       => '/uploads/sliders/custom-slide-4.webp',
                'link'        => '/shop?promo=4',
                'button_text' => 'VIEW MORE',
                'sort_order'  => 4,
                'status'      => true,
            ]);
        $createRes->assertStatus(201);

        $this->assertEquals(4, Slider::count());

        // Re-run seeder
        $this->seed(UniversalCatalogSeeder::class);

        $this->assertEquals(4, Slider::count(), "Seeder deleted admin-created slider!");
        $this->assertDatabaseHas('sliders', [
            'title' => 'Fourth Exclusive Slider',
        ]);
    }
}
