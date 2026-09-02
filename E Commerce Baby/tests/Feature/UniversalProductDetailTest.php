<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LandingPage;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversalProductDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Product page loads from database with dynamic attributes
     */
    public function test_product_detail_page_loads_with_database_attributes()
    {
        $category = Category::create([
            'title'  => 'Veterinary Care',
            'handle' => 'veterinary-care',
            'status' => true,
        ]);

        $product = Product::create([
            'title'             => 'Calf Vita Mineral Premix 1KG',
            'slug'              => 'calf-vita-mineral-premix-1kg',
            'sku'               => 'VET-CVM-01',
            'category_id'       => $category->id,
            'category_handle'   => 'veterinary-care',
            'regular_price'     => 1200,
            'sale_price'        => 960,
            'cost_price'        => 600,
            'stock'             => 75,
            'featured_image'    => '/images/products/calf-premix.jpg',
            'gallery_images'    => [
                '/images/products/calf-premix.jpg',
                '/images/products/calf-premix-back.jpg'
            ],
            'sizes'             => ['500g Pack', '1 KG Pack', '5 KG Bag'],
            'short_description' => 'Essential minerals and vitamins for young dairy calves.',
            'description'       => '<p>Complete micronutrient balance for high bioavailability.</p>',
            'status'            => true,
        ]);

        $response = $this->get('/product/calf-vita-mineral-premix-1kg');
        $response->assertStatus(200);
        $response->assertViewIs('pages.product-detail');
        $response->assertSee('Calf Vita Mineral Premix 1KG');
        $response->assertSee('VET-CVM-01');
        $response->assertSee('960');
        $response->assertSee('1,200');
        $response->assertSee('20% OFF');
        $response->assertSee('500g Pack');
        $response->assertSee('1 KG Pack');
        $response->assertSee('5 KG Bag');
        $response->assertSee('Veterinary Care');
    }

    /**
     * 2. Standard / no-option product renders cleanly
     */
    public function test_standard_no_option_product_renders_cleanly()
    {
        Product::create([
            'title'             => 'Single Universal Gadget',
            'slug'              => 'single-universal-gadget',
            'sku'               => 'GADGET-01',
            'regular_price'     => 500,
            'sale_price'        => 500,
            'sizes'             => null,
            'short_description' => 'Compact universal electronic accessory.',
            'description'       => 'High quality accessory.',
            'status'            => true,
        ]);

        $response = $this->get('/product/single-universal-gadget');
        $response->assertStatus(200);
        $response->assertSee('Single Universal Gadget');
        $response->assertSee('500');
    }

    /**
     * 3. Related products are database-driven from same category
     */
    public function test_related_products_render_from_database()
    {
        $cat = Category::create(['title' => 'Gardening Tools', 'handle' => 'gardening-tools', 'status' => true]);

        $p1 = Product::create([
            'title'           => 'Garden Shovel Pro',
            'slug'            => 'garden-shovel-pro',
            'category_id'     => $cat->id,
            'category_handle' => 'gardening-tools',
            'regular_price'   => 400,
            'sale_price'      => 350,
            'status'          => true,
        ]);

        $p2 = Product::create([
            'title'           => 'Garden Rake Heavy Duty',
            'slug'            => 'garden-rake-heavy-duty',
            'category_id'     => $cat->id,
            'category_handle' => 'gardening-tools',
            'regular_price'   => 550,
            'sale_price'      => 450,
            'status'          => true,
        ]);

        $response = $this->get('/product/garden-shovel-pro');
        $response->assertStatus(200);
        $response->assertSee('Garden Rake Heavy Duty');
    }

    /**
     * 4. Invalid product returns 404
     */
    public function test_invalid_product_returns_404()
    {
        $response = $this->get('/product/non-existent-product-slug');
        $response->assertStatus(404);
    }

    /**
     * 5. LandingPage takes precedence over storefront Product
     */
    public function test_landing_page_precedence_is_preserved()
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
            'title'         => 'Chicken Booster Storefront Product',
            'slug'          => 'chicken-booster',
            'regular_price' => 990,
            'sale_price'    => 990,
            'status'        => true,
        ]);

        $response = $this->get('/product/chicken-booster');
        $response->assertStatus(200);
        $response->assertViewIs('pages.landing-page');
    }

    /**
     * 6. Add to Bag cart action works via API
     */
    public function test_add_to_bag_action_works()
    {
        $product = Product::create([
            'title'         => 'Organic Compost 10KG',
            'slug'          => 'organic-compost-10kg',
            'sku'           => 'ORG-COMP-10',
            'regular_price' => 300,
            'sale_price'    => 250,
            'sizes'         => ['10KG Bag'],
            'status'        => true,
        ]);

        $response = $this->postJson('/cart/add', [
            'product_id' => $product->id,
            'size'       => '10KG Bag',
            'quantity'   => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('cart.item_count', 2);
        $response->assertJsonPath('cart.subtotal', 500);
    }
}
