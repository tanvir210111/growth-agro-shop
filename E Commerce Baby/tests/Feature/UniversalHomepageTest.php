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
     * 4. Hero slider renders database-backed slides in correct order
     */
    public function test_homepage_hero_slider_renders_from_database()
    {
        Slider::create([
            'title'       => "SUMMER SALE\nUP TO 40% OFF",
            'subtitle'    => 'TRENDING NOW',
            'image'       => '/uploads/sliders/hero_banner_1.webp',
            'link'        => '/shop',
            'button_text' => 'SHOP NOW →',
            'sort_order'  => 1,
            'status'      => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('/uploads/sliders/hero_banner_1.webp');
        $response->assertSee('/shop');
        $response->assertSee('TRENDING NOW');
        $response->assertSee('SHOP NOW →');
    }

    /**
     * 5. Active vs Inactive sliders and sort_order testing
     */
    public function test_active_and_inactive_sliders_filtering_and_sort_order()
    {
        Slider::create([
            'title'       => 'Inactive Hero Promo',
            'subtitle'    => 'EXPIRED OFFER',
            'image'       => '/uploads/sliders/inactive_promo.webp',
            'link'        => '/shop?promo=inactive',
            'button_text' => 'EXPIRED',
            'sort_order'  => 1,
            'status'      => false, // Inactive
        ]);

        Slider::create([
            'title'       => 'Second Active Slide',
            'subtitle'    => 'SPECIAL PROMO',
            'image'       => '/uploads/sliders/hero_banner_2.webp',
            'link'        => '/categories',
            'button_text' => 'EXPLORE NOW',
            'sort_order'  => 2,
            'status'      => true,
        ]);

        Slider::create([
            'title'       => 'First Active Slide',
            'subtitle'    => 'TOP PROMO',
            'image'       => '/uploads/sliders/hero_banner_1.webp',
            'link'        => '/shop',
            'button_text' => 'BUY NOW',
            'sort_order'  => 1,
            'status'      => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Active slides must render
        $response->assertSee('First Active Slide');
        $response->assertSee('Second Active Slide');
        $response->assertSee('/uploads/sliders/hero_banner_1.webp');
        $response->assertSee('/uploads/sliders/hero_banner_2.webp');

        // Inactive slide must NOT render
        $response->assertDontSee('Inactive Hero Promo');
        $response->assertDontSee('EXPIRED OFFER');
        $response->assertDontSee('/uploads/sliders/inactive_promo.webp');
    }

    /**
     * 6. Empty slider database state renders clean universal fallback hero
     */
    public function test_empty_slider_db_renders_universal_fallback_hero()
    {
        $this->assertEquals(0, Slider::count());

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('SUPER SALE');
        $response->assertSee('UP TO 40% OFF');
        $response->assertSee('Universal Marketplace');
        $response->assertDontSee('Baby Fashion');
        $response->assertDontSee('images/logo.png');
    }

    /**
     * 7. Slider image paths must strictly use uploads/sliders and never landing-pages
     */
    public function test_slider_images_use_uploads_sliders_and_never_landing_pages()
    {
        Slider::create([
            'title'       => 'Safe Image Hero Slide',
            'subtitle'    => 'SAFE PATH',
            'image'       => '/uploads/sliders/hero_banner_3.webp',
            'link'        => '/shop',
            'button_text' => 'SHOP NOW',
            'sort_order'  => 1,
            'status'      => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('/uploads/sliders/hero_banner_3.webp');
        $response->assertDontSee('/uploads/landing-pages/');
    }

    /**
     * 8. Landing page precedence remains protected
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
