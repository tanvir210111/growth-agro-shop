<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileStorefrontResponsiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('site_name', 'Growth Shop');
        Setting::set('support_phone', '01560-016740');
    }

    /**
     * 1. Mobile Header has Row 1 & Row 2 elements, navigation bar, and off-canvas drawer
     */
    public function test_mobile_header_renders_row1_row2_and_drawer()
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Mobile Menu Button (Row 1 Left)
        $response->assertSee('id="mobileMenuToggle"', false);

        // Brand Logo (Row 1 Center/Left)
        $response->assertSee('Growth Shop');

        // Phone call link (Row 1 Right)
        $response->assertSee('phone-action-btn', false);
        $response->assertSee('01560-016740');

        // Cart toggle with badge (Row 1 Right)
        $response->assertSee('cart-toggle-btn', false);
        $response->assertSee('cart-badge-count', false);
        $response->assertSee('Cart');

        // Search Bar (Row 2 on mobile)
        $response->assertSee('class="header-search"', false);
        $response->assertSee('class="search-input live-search-input"', false);
        $response->assertSee('id="searchResultsDropdown"', false);

        // Horizontal Category Navigation Bar
        $response->assertSee('class="nav-bar"', false);
        $response->assertSee('class="nav-links"', false);

        // Off-Canvas Mobile Drawer
        $response->assertSee('id="mobileNavDrawer"', false);
        $response->assertSee('id="mobileNavDrawerOverlay"', false);
        $response->assertSee('id="mobileDrawerClose"', false);
        $response->assertSee('Shop by Category');
    }

    /**
     * 2. Homepage Mobile Hero and 2-Column Categories Grid
     */
    public function test_homepage_hero_and_categories_grid()
    {
        // Create 8 categories
        for ($i = 1; $i <= 8; $i++) {
            Category::create([
                'title' => 'Category ' . $i,
                'handle' => 'category-' . $i,
                'sort_order' => $i,
                'status' => true,
            ]);
        }

        $response = $this->get('/');
        $response->assertStatus(200);

        // Hero Slider structure
        $response->assertSee('hero-slider-container', false);
        $response->assertSee('hero-slide', false);

        // Homepage Categories Showcase Grid (7 priority + 1 More Categories)
        $response->assertSee('homepage-categories-grid', false);
        $response->assertSee('Shop Our Top Categories');
        $response->assertSee('More Categories');
    }

    /**
     * 3. Shop Page Mobile Filter Drawer and 2-Column Product Grid
     */
    public function test_shop_page_mobile_filter_drawer_and_toolbar()
    {
        $cat = Category::create([
            'title' => 'Beauty Care',
            'handle' => 'beauty-care',
            'status' => true,
        ]);

        Product::create([
            'title' => 'Organic Glow Serum',
            'slug' => 'organic-glow-serum',
            'category_id' => $cat->id,
            'category_handle' => 'beauty-care',
            'regular_price' => 1200,
            'sale_price' => 950,
            'status' => true,
        ]);

        $response = $this->get('/shop');
        $response->assertStatus(200);

        // Mobile Filter trigger button
        $response->assertSee('id="openFilterDrawerBtn"', false);
        $response->assertSee('Filters');

        // Sidebar off-canvas drawer structure
        $response->assertSee('id="catalogSidebar"', false);
        $response->assertSee('id="filterDrawerOverlay"', false);
        $response->assertSee('id="filterDrawerClose"', false);
        $response->assertSee('Filter By');
        $response->assertSee('Price Range');

        // Toolbar elements: sort and count
        $response->assertSee('catalog-toolbar', false);
        $response->assertSee('Showing');
        $response->assertSee('Sort:');
        $response->assertSee('Organic Glow Serum');
    }

    /**
     * 4. Multi-level recursive category hierarchy renders inside mobile filter
     */
    public function test_shop_page_recursive_hierarchy_in_mobile_filter()
    {
        $level1 = Category::create(['title' => 'Cosmetics', 'handle' => 'cosmetics', 'status' => true]);
        $level2 = Category::create(['title' => 'Skincare', 'handle' => 'skincare', 'parent_id' => $level1->id, 'status' => true]);
        $level3 = Category::create(['title' => 'Face Creams', 'handle' => 'face-creams', 'parent_id' => $level2->id, 'status' => true]);

        $response = $this->get('/shop');
        $response->assertStatus(200);
        $response->assertSee('Cosmetics');
        $response->assertSee('Skincare');
        $response->assertSee('Face Creams');
    }

    /**
     * 5. Categories page renders 2-column layout and recursive subcategories cleanly
     */
    public function test_categories_page_mobile_rendering()
    {
        $parent = Category::create(['title' => 'Electronics', 'handle' => 'electronics', 'status' => true]);
        Category::create(['title' => 'Smart Gadgets', 'handle' => 'smart-gadgets', 'parent_id' => $parent->id, 'status' => true]);

        $response = $this->get('/categories');
        $response->assertStatus(200);
        $response->assertSee('categories-grid', false);
        $response->assertSee('Electronics');
        $response->assertSee('Smart Gadgets');
        $response->assertSee('subcat-link-item', false);
    }

    /**
     * 6. Product Detail, Checkout, and WhatsApp floating widget
     */
    public function test_product_detail_checkout_and_whatsapp_widget()
    {
        $cat = Category::create(['title' => 'Gadgets', 'handle' => 'gadgets', 'status' => true]);
        $prod = Product::create([
            'title' => 'Wireless Bluetooth Earbuds',
            'slug' => 'wireless-bluetooth-earbuds',
            'category_id' => $cat->id,
            'regular_price' => 1500,
            'sale_price' => 1150,
            'sizes' => ['White', 'Black'],
            'status' => true,
        ]);

        // Product Detail
        $detailRes = $this->get('/product/wireless-bluetooth-earbuds');
        $detailRes->assertStatus(200);
        $detailRes->assertSee('Wireless Bluetooth Earbuds');
        $detailRes->assertSee('Direct Order (Cash on Delivery)');
        $detailRes->assertSee('Add to Shopping Bag');
        $detailRes->assertSee('id="productTabs"', false);

        // Checkout
        $checkoutRes = $this->get('/checkout');
        $checkoutRes->assertStatus(200);
        $checkoutRes->assertSee('Cash on Delivery Checkout');
        $checkoutRes->assertSee('Inside Dhaka');
        $checkoutRes->assertSee('Outside Dhaka');
        $checkoutRes->assertSee('delivery-area-options', false);

        // WhatsApp Widget
        $detailRes->assertSee('id="whatsappContainer"', false);
        $detailRes->assertSee('id="whatsappToggleBtn"', false);
        $detailRes->assertSee('id="whatsappPopup"', false);
    }

    /**
     * 7. All 3 hero slides render complete artwork structure and non-overlapping controls
     */
    public function test_all_3_hero_slides_render_complete_artwork_and_controls()
    {
        // Seed the 3 canonical sliders
        \App\Models\Slider::create([
            'title'       => "SUMMER SALE\nUP TO 40% OFF",
            'subtitle'    => 'TRENDING NOW',
            'image'       => '/uploads/sliders/hero_banner_1.webp',
            'link'        => '/shop',
            'button_text' => 'SHOP NOW →',
            'sort_order'  => 1,
            'status'      => true,
        ]);
        \App\Models\Slider::create([
            'title'       => "QUALITY PRODUCTS\nFOR EVERYDAY LIFE",
            'subtitle'    => 'EVERYDAY ESSENTIALS',
            'image'       => '/uploads/sliders/hero_banner_2.webp',
            'link'        => '/shop',
            'button_text' => 'SHOP NOW →',
            'sort_order'  => 2,
            'status'      => true,
        ]);
        \App\Models\Slider::create([
            'title'       => "SMARTER PRODUCTS\nBETTER EVERYDAY",
            'subtitle'    => 'TECH & LIFESTYLE',
            'image'       => '/uploads/sliders/hero_banner_3.webp',
            'link'        => '/shop',
            'button_text' => 'EXPLORE NOW →',
            'sort_order'  => 3,
            'status'      => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // All 3 slides present in HTML
        $response->assertSee('/uploads/sliders/hero_banner_1.webp');
        $response->assertSee('/uploads/sliders/hero_banner_2.webp');
        $response->assertSee('/uploads/sliders/hero_banner_3.webp');

        // All 3 titles present
        $response->assertSee('SUMMER SALE');
        $response->assertSee('QUALITY PRODUCTS');
        $response->assertSee('SMARTER PRODUCTS');

        // Navigation controls present
        $response->assertSee('id="heroPrevBtn"', false);
        $response->assertSee('id="heroNextBtn"', false);
        $response->assertSee('id="heroSliderDots"', false);
    }

    /**
     * 8. baby-fashion.css contains mobile hero zero-crop and zero-overlap rules
     */
    public function test_stylesheet_contains_mobile_first_rules()
    {
        $cssPath = public_path('css/baby-fashion.css');
        $this->assertFileExists($cssPath);
        $css = file_get_contents($cssPath);

        // Viewport breakpoints
        $this->assertStringContainsString('@media (max-width: 768px)', $css);
        $this->assertStringContainsString('@media (max-width: 640px)', $css);
        $this->assertStringContainsString('@media (max-width: 480px)', $css);

        // Header mobile rules
        $this->assertStringContainsString('.mobile-nav-drawer', $css);
        $this->assertStringContainsString('.mobile-menu-toggle', $css);
        $this->assertStringContainsString('.nav-links', $css);

        // Hero mobile constraints (220-280px range)
        $this->assertStringContainsString('.hero-slider-container', $css);
        $this->assertStringContainsString('250px', $css);

        // Hero mobile zero-crop rules (object-fit: contain, right center alignment)
        $this->assertStringContainsString('object-fit: contain', $css);
        $this->assertStringContainsString('object-position: right center', $css);

        // Hero arrow clearance and extreme edge positioning
        $this->assertStringContainsString('.hero-nav-arrow.hero-prev', $css);
        $this->assertStringContainsString('left: 3px', $css);
        $this->assertStringContainsString('right: 3px', $css);

        // Category grid 2 columns
        $this->assertStringContainsString('.homepage-categories-grid', $css);

        // Global zero-overflow safeguard
        $this->assertStringContainsString('overflow-x: hidden !important', $css);
        $this->assertStringContainsString('max-width: 100vw !important', $css);
    }
}
