<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LandingPage;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversalStorefrontEcosystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Homepage loads with dynamic branding and no hardcoded baby hero
     */
    public function test_homepage_loads_with_universal_branding()
    {
        Setting::set('site_name', 'Growth Agro Store');
        Setting::set('site_title', 'Growth Agro | Premium Agro & Livestock Solutions');
        Setting::set('support_phone', '01800-112233');

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Growth Agro Store');
        $response->assertSee('01800-112233');
        $response->assertSee('Shop Our Top Categories');
        $response->assertSee('New Arrivals');
        $response->assertSee('Proven Bestsellers');
        $response->assertSee('Cash on Delivery');
        $response->assertSee('Fast Delivery');
    }

    /**
     * 2. All Categories page loads dynamically with category search & CTA
     */
    public function test_all_categories_page_loads_with_database_categories()
    {
        Category::create(['title' => 'Poultry Supplements', 'handle' => 'poultry-supplements', 'status' => true]);
        Category::create(['title' => 'Dairy Feed', 'handle' => 'dairy-feed', 'status' => true]);

        $response = $this->get('/categories');
        $response->assertStatus(200);
        $response->assertViewIs('pages.categories');
        $response->assertSee('All Categories');
        $response->assertSee('Poultry Supplements');
        $response->assertSee('Dairy Feed');
        $response->assertSee('Browse our comprehensive department categories');
        $response->assertSee('View All Products');
    }

    /**
     * 3. Category & Catalog page loads with sidebar filter and products
     */
    public function test_category_page_renders_sidebar_and_products()
    {
        $cat = Category::create(['title' => 'Aqua Care', 'handle' => 'aqua-care', 'status' => true]);
        Product::create([
            'title'           => 'Probiotic Fish Booster 1KG',
            'slug'            => 'probiotic-fish-booster-1kg',
            'category_id'     => $cat->id,
            'category_handle' => 'aqua-care',
            'regular_price'   => 800,
            'sale_price'      => 650,
            'status'          => true,
        ]);

        $response = $this->get('/collections/aqua-care');
        $response->assertStatus(200);
        $response->assertViewIs('pages.shop');
        $response->assertSee('Aqua Care');
        $response->assertSee('Probiotic Fish Booster 1KG');
        $response->assertSee('650');
        $response->assertSee('Filter By');
        $response->assertSee('Price Range');
    }

    /**
     * 4. Product details page renders universal structure
     */
    public function test_product_details_page_renders_universal_structure()
    {
        $product = Product::create([
            'title'             => 'Zinc Chelate Feed Grade 500g',
            'slug'              => 'zinc-chelate-feed-grade-500g',
            'sku'               => 'AGRO-ZN-500',
            'regular_price'     => 450,
            'sale_price'        => 380,
            'sizes'             => ['500g', '1kg', '5kg'],
            'short_description' => 'Highly bioavailable zinc chelate for animal health.',
            'description'       => '<p>Complete specifications and dosage details.</p>',
            'status'            => true,
        ]);

        $response = $this->get('/product/zinc-chelate-feed-grade-500g');
        $response->assertStatus(200);
        $response->assertViewIs('pages.product-detail');
        $response->assertSee('Zinc Chelate Feed Grade 500g');
        $response->assertSee('AGRO-ZN-500');
        $response->assertSee('380');
        $response->assertSee('500g');
        $response->assertSee('Add to Shopping Bag');
        $response->assertSee('Direct Order (Cash on Delivery)');
        $response->assertSee('Specifications');
        $response->assertSee('Delivery & Returns', false);
    }

    /**
     * 5. Landing page precedence remains 100% active on /product/{slug}
     */
    public function test_landing_page_priority_is_strictly_preserved()
    {
        LandingPage::create([
            'slug'         => 'chicken-booster',
            'name'         => 'Chicken Booster Master',
            'theme'        => 'chicken-booster',
            'status'       => 'published',
            'product_id'   => 'chicken-booster',
            'product_name' => 'Chicken Booster (চিকেন বুস্টার)',
            'content'      => LandingPage::getDefaultMasterContent(),
        ]);

        Product::create([
            'title'         => 'Chicken Booster Store Item',
            'slug'          => 'chicken-booster',
            'regular_price' => 990,
            'sale_price'    => 990,
            'status'        => true,
        ]);

        $response = $this->get('/product/chicken-booster');
        $response->assertStatus(200);
        $response->assertViewIs('pages.landing-page');
    }
}
