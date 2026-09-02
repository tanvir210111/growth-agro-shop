<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LandingPage;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversalProductServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = app(ProductService::class);
    }

    /**
     * 1. Test database-driven product creation & retrieval across universal categories
     */
    public function test_universal_product_creation_and_database_retrieval()
    {
        $category = Category::create([
            'title'        => 'Agro & Livestock Supplements',
            'handle'       => 'agro-livestock',
            'description'  => 'High quality natural supplements for poultry, cattle and aqua farming.',
            'sort_order'   => 1,
            'status'       => true,
        ]);

        $product = Product::create([
            'title'             => 'Layer Booster Premium Formula 1KG',
            'slug'              => 'layer-booster-premium-formula-1kg',
            'sku'               => 'AGRO-LB-01',
            'category_id'       => $category->id,
            'category_handle'   => 'agro-livestock',
            'regular_price'     => 1500,
            'sale_price'        => 1250,
            'cost_price'        => 800,
            'stock'             => 100,
            'featured_image'    => '/images/products/layer-booster.jpg',
            'sizes'             => ['500g Pack', '1 KG Pack', '5 KG Bag'],
            'short_description' => 'Premium egg production and calcium boosting supplement.',
            'description'       => '<p>Formulated for commercial and home poultry farms.</p>',
            'is_featured'       => true,
            'is_new_arrival'    => true,
            'is_bestseller'     => true,
            'status'            => true,
        ]);

        // findBySlug
        $found = $this->productService->findBySlug('layer-booster-premium-formula-1kg');
        $this->assertNotNull($found);
        $this->assertEquals('Layer Booster Premium Formula 1KG', $found['title']);
        $this->assertEquals('AGRO-LB-01', $found['sku']);
        $this->assertEquals(1250, $found['price']);
        $this->assertEquals(1500, $found['original_price']);
        $this->assertEquals(17, $found['discount_percent']);
        $this->assertEquals(['500g Pack', '1 KG Pack', '5 KG Bag'], $found['sizes']);
        $this->assertEquals('Agro & Livestock Supplements', $found['category_name']);

        // findById
        $foundById = $this->productService->findById($product->id);
        $this->assertNotNull($foundById);
        $this->assertEquals($product->id, $foundById['id']);
    }

    /**
     * 2. Test universal database search across title, SKU, description and category
     */
    public function test_universal_database_search_queries_all_fields()
    {
        $category = Category::create([
            'title'       => 'Home & Living',
            'handle'      => 'home-living',
            'status'      => true,
        ]);

        Product::create([
            'title'             => 'Nordic Minimalist Ceramic Coffee Cup',
            'slug'              => 'nordic-minimalist-ceramic-coffee-cup',
            'sku'               => 'HOME-CUP-99',
            'category_id'       => $category->id,
            'category_handle'   => 'home-living',
            'regular_price'     => 600,
            'sale_price'        => 480,
            'stock'             => 40,
            'short_description' => 'Matte ceramic finish with wooden coaster.',
            'description'       => 'Dishwasher safe and heat resistant.',
            'status'            => true,
        ]);

        // Search by title substring
        $resultsTitle = $this->productService->search('Ceramic');
        $this->assertNotEmpty($resultsTitle);
        $this->assertEquals('Nordic Minimalist Ceramic Coffee Cup', $resultsTitle[0]['title']);

        // Search by SKU
        $resultsSku = $this->productService->search('HOME-CUP');
        $this->assertNotEmpty($resultsSku);
        $this->assertEquals('Nordic Minimalist Ceramic Coffee Cup', $resultsSku[0]['title']);

        // Search by category handle
        $resultsCat = $this->productService->search('home-living');
        $this->assertNotEmpty($resultsCat);

        // Search via HTTP API /api/search?q=
        $response = $this->getJson('/api/search?q=Ceramic');
        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Nordic Minimalist Ceramic Coffee Cup']);
    }

    /**
     * 3. Test New Arrivals, Best Sellers, and Featured query from DB
     */
    public function test_new_arrivals_and_bestsellers_are_database_driven()
    {
        Product::create([
            'title'             => 'New Arrival Special Widget',
            'slug'              => 'new-arrival-special-widget',
            'regular_price'     => 500,
            'sale_price'        => 400,
            'is_new_arrival'    => true,
            'is_bestseller'     => false,
            'is_featured'       => false,
            'status'            => true,
        ]);

        Product::create([
            'title'             => 'All Time Top Selling Gadget',
            'slug'              => 'all-time-top-selling-gadget',
            'regular_price'     => 900,
            'sale_price'        => 750,
            'is_new_arrival'    => false,
            'is_bestseller'     => true,
            'is_featured'       => true,
            'status'            => true,
        ]);

        $newArrivals = $this->productService->getNewArrivals();
        $this->assertNotEmpty($newArrivals);
        $this->assertTrue(collect($newArrivals)->contains('slug', 'new-arrival-special-widget'));

        $bestsellers = $this->productService->getBestsellers();
        $this->assertNotEmpty($bestsellers);
        $this->assertTrue(collect($bestsellers)->contains('slug', 'all-time-top-selling-gadget'));

        $featured = $this->productService->getFeaturedProducts();
        $this->assertNotEmpty($featured);
        $this->assertTrue(collect($featured)->contains('slug', 'all-time-top-selling-gadget'));
    }

    /**
     * 4. Test Category Collections filtering and price sorting from DB
     */
    public function test_category_collection_filtering_and_sorting()
    {
        $catA = Category::create(['title' => 'Electronics', 'handle' => 'electronics', 'status' => true]);
        $catB = Category::create(['title' => 'Gardening', 'handle' => 'gardening', 'status' => true]);

        Product::create([
            'title'             => 'Budget Garden Hose',
            'slug'              => 'budget-garden-hose',
            'category_id'       => $catB->id,
            'category_handle'   => 'gardening',
            'regular_price'     => 300,
            'sale_price'        => 250,
            'status'            => true,
        ]);

        Product::create([
            'title'             => 'Pro Garden Hose Premium',
            'slug'              => 'pro-garden-hose-premium',
            'category_id'       => $catB->id,
            'category_handle'   => 'gardening',
            'regular_price'     => 900,
            'sale_price'        => 800,
            'status'            => true,
        ]);

        // Filter by category handle
        $gardeningProducts = $this->productService->getProductsByCollection('gardening');
        $this->assertCount(2, $gardeningProducts);

        // Sort price asc
        $sortedAsc = $this->productService->getProductsByCollection('gardening', 'price_asc');
        $this->assertEquals('Budget Garden Hose', $sortedAsc[0]['title']);

        // Sort price desc
        $sortedDesc = $this->productService->getProductsByCollection('gardening', 'price_desc');
        $this->assertEquals('Pro Garden Hose Premium', $sortedDesc[0]['title']);
    }

    /**
     * 5. Landing Page Safety: Landing pages take absolute precedence over storefront products
     */
    public function test_landing_page_priority_over_storefront_product()
    {
        // Create Landing Page for chicken-booster
        LandingPage::create([
            'slug'         => 'chicken-booster',
            'name'         => 'Chicken Booster Landing',
            'theme'        => 'chicken-booster',
            'status'       => 'published',
            'product_id'   => 'chicken-booster',
            'product_name' => 'Chicken Booster (চিকেন বুস্টার)',
            'content'      => LandingPage::getDefaultMasterContent(),
        ]);

        // Even if a storefront product exists with slug chicken-booster
        Product::create([
            'title'             => 'Chicken Booster Storefront Product',
            'slug'              => 'chicken-booster',
            'regular_price'     => 990,
            'sale_price'        => 990,
            'status'            => true,
        ]);

        // GET /product/chicken-booster must serve landing-page blade view
        $response = $this->get('/product/chicken-booster');
        $response->assertStatus(200);
        $response->assertViewIs('pages.landing-page');
    }
}
