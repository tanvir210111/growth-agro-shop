<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LandingPage;
use App\Models\Product;
use App\Models\Slider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversalHomepageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Homepage loads cleanly even with an empty database (no crash, clean fallback)
     */
    public function test_homepage_loads_cleanly_with_empty_database()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('pages.home');
        $response->assertSee('Shop Our Top Categories');
        $response->assertSee('New Arrivals');
        $response->assertSee('Proven Bestsellers');
        $response->assertSee('Cash on Delivery');
    }

    /**
     * 2. Homepage categories and product counts are completely database-driven
     */
    public function test_homepage_categories_and_product_counts_are_database_driven()
    {
        $cat1 = Category::create([
            'title'       => 'Poultry Supplements',
            'handle'      => 'poultry-supplements',
            'sort_order'  => 1,
            'status'      => true,
        ]);

        $cat2 = Category::create([
            'title'       => 'Organic Gardening',
            'handle'      => 'organic-gardening',
            'sort_order'  => 2,
            'status'      => true,
        ]);

        Product::create([
            'title'           => 'Broiler Fast Growth 1KG',
            'slug'            => 'broiler-fast-growth-1kg',
            'category_id'     => $cat1->id,
            'category_handle' => 'poultry-supplements',
            'regular_price'   => 800,
            'sale_price'      => 650,
            'status'          => true,
        ]);

        Product::create([
            'title'           => 'Layer Egg Booster 1KG',
            'slug'            => 'layer-egg-booster-1kg',
            'category_id'     => $cat1->id,
            'category_handle' => 'poultry-supplements',
            'regular_price'   => 950,
            'sale_price'      => 800,
            'status'          => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Poultry Supplements');
        $response->assertSee('Organic Gardening');
        $response->assertSee('2 Items'); // Poultry has 2 items
        $response->assertSee('0 Items'); // Gardening has 0 items
    }

    /**
     * 3. New Arrivals & Best Sellers are populated from database
     */
    public function test_homepage_new_arrivals_and_bestsellers_render_from_database()
    {
        Product::create([
            'title'          => 'Eco Bio Fertilizer 5KG',
            'slug'           => 'eco-bio-fertilizer-5kg',
            'regular_price'  => 600,
            'sale_price'     => 480,
            'is_new_arrival' => true,
            'is_bestseller'  => false,
            'status'         => true,
        ]);

        Product::create([
            'title'          => 'Aqua Pro Fish Growth 2KG',
            'slug'           => 'aqua-pro-fish-growth-2kg',
            'regular_price'  => 1200,
            'sale_price'     => 990,
            'is_new_arrival' => false,
            'is_bestseller'  => true,
            'status'         => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Eco Bio Fertilizer 5KG');
        $response->assertSee('Aqua Pro Fish Growth 2KG');
    }

    /**
     * 4. Hero slider renders database-backed slides
     */
    public function test_homepage_hero_slider_renders_from_database()
    {
        Slider::create([
            'title'       => 'Monsoon Agriculture Mega Offer',
            'subtitle'    => 'Save up to 40% on all organic products',
            'image'       => '/images/banners/monsoon-offer.jpg',
            'link'        => '/shop?category=organic-gardening',
            'button_text' => 'ORDER NOW',
            'sort_order'  => 1,
            'status'      => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('/images/banners/monsoon-offer.jpg');
        $response->assertSee('/shop?category=organic-gardening');
    }

    /**
     * 5. Landing page precedence remains protected
     */
    public function test_landing_page_priority_remains_active()
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

        $response = $this->get('/product/chicken-booster');
        $response->assertStatus(200);
        $response->assertViewIs('pages.landing-page');
    }
}
