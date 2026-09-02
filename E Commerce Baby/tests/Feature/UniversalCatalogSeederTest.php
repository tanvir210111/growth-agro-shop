<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\LandingPage;
use App\Models\Product;
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
}
