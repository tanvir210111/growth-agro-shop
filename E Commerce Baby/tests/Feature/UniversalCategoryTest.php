<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LandingPage;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversalCategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Complete catalog page loads via /shop and alias /categories
     */
    public function test_complete_catalog_loads_via_shop_and_categories()
    {
        $cat = Category::create([
            'title'      => 'Dairy Supplements',
            'handle'     => 'dairy-supplements',
            'sort_order' => 1,
            'status'     => true,
        ]);

        Product::create([
            'title'           => 'Calf Growth Milk Replacer 1KG',
            'slug'            => 'calf-growth-milk-replacer-1kg',
            'category_id'     => $cat->id,
            'category_handle' => 'dairy-supplements',
            'regular_price'   => 1200,
            'sale_price'      => 1050,
            'status'          => true,
        ]);

        // /shop
        $resShop = $this->get('/shop');
        $resShop->assertStatus(200);
        $resShop->assertViewIs('pages.shop');
        $resShop->assertSee('Calf Growth Milk Replacer 1KG');
        $resShop->assertSee('Dairy Supplements');

        // /categories (All Categories View)
        $resCat = $this->get('/categories');
        $resCat->assertStatus(200);
        $resCat->assertViewIs('pages.categories');
        $resCat->assertSee('Dairy Supplements');
    }

    /**
     * 2. Category page resolves by handle via /collections/{handle} and /category/{slug}
     */
    public function test_category_page_resolves_and_filters_products_strictly()
    {
        $catA = Category::create([
            'title'  => 'Fisheries Aqua Care',
            'handle' => 'fisheries-aqua-care',
            'status' => true,
        ]);

        $catB = Category::create([
            'title'  => 'Soil Nutrition',
            'handle' => 'soil-nutrition',
            'status' => true,
        ]);

        $prodA = Product::create([
            'title'           => 'Aqua Oxygen Pro 500g',
            'slug'            => 'aqua-oxygen-pro-500g',
            'category_id'     => $catA->id,
            'category_handle' => 'fisheries-aqua-care',
            'regular_price'   => 450,
            'sale_price'      => 380,
            'status'          => true,
        ]);

        $prodB = Product::create([
            'title'           => 'Humic Acid Soil Tonic 1L',
            'slug'            => 'humic-acid-soil-tonic-1l',
            'category_id'     => $catB->id,
            'category_handle' => 'soil-nutrition',
            'regular_price'   => 600,
            'sale_price'      => 520,
            'status'          => true,
        ]);

        // Via /collections/{handle}
        $resCollection = $this->get('/collections/fisheries-aqua-care');
        $resCollection->assertStatus(200);
        $resCollection->assertSee('Aqua Oxygen Pro 500g');
        $resCollection->assertDontSee('Humic Acid Soil Tonic 1L');

        // Via /category/{slug}
        $resCategory = $this->get('/category/soil-nutrition');
        $resCategory->assertStatus(200);
        $resCategory->assertSee('Humic Acid Soil Tonic 1L');
        $resCategory->assertDontSee('Aqua Oxygen Pro 500g');
    }

    /**
     * 3. Invalid category returns 404
     */
    public function test_invalid_category_returns_404()
    {
        $response = $this->get('/collections/non-existent-category');
        $response->assertStatus(404);

        $responseAlias = $this->get('/category/non-existent-category');
        $responseAlias->assertStatus(404);
    }

    /**
     * 4. Inactive category cannot be browsed publicly
     */
    public function test_inactive_category_returns_404()
    {
        Category::create([
            'title'  => 'Archived Line',
            'handle' => 'archived-line',
            'status' => false,
        ]);

        $response = $this->get('/collections/archived-line');
        $response->assertStatus(404);

        $responseAlias = $this->get('/category/archived-line');
        $responseAlias->assertStatus(404);
    }

    /**
     * 5. Empty category handles gracefully without crashing
     */
    public function test_empty_category_renders_clean_empty_state()
    {
        Category::create([
            'title'  => 'Upcoming Crop Seeds',
            'handle' => 'upcoming-crop-seeds',
            'status' => true,
        ]);

        $response = $this->get('/collections/upcoming-crop-seeds');
        $response->assertStatus(200);
        $response->assertSee('Upcoming Crop Seeds');
        $response->assertSee('No products found');
    }

    /**
     * 6. Sorting works cleanly across database queries
     */
    public function test_sorting_by_price_and_newest()
    {
        $cat = Category::create([
            'title'  => 'Equipment',
            'handle' => 'equipment',
            'status' => true,
        ]);

        Product::create([
            'title'           => 'Budget Pruner',
            'slug'            => 'budget-pruner',
            'category_id'     => $cat->id,
            'category_handle' => 'equipment',
            'regular_price'   => 200,
            'sale_price'      => 150,
            'status'          => true,
        ]);

        Product::create([
            'title'           => 'Pro Heavy-Duty Sprayer',
            'slug'            => 'pro-heavy-duty-sprayer',
            'category_id'     => $cat->id,
            'category_handle' => 'equipment',
            'regular_price'   => 2500,
            'sale_price'      => 2100,
            'status'          => true,
        ]);

        // Price asc
        $resAsc = $this->get('/collections/equipment?sort=price_asc');
        $resAsc->assertStatus(200);
        $resAsc->assertSeeInOrder(['Budget Pruner', 'Pro Heavy-Duty Sprayer']);

        // Price desc
        $resDesc = $this->get('/collections/equipment?sort=price_desc');
        $resDesc->assertStatus(200);
        $resDesc->assertSeeInOrder(['Pro Heavy-Duty Sprayer', 'Budget Pruner']);
    }

    /**
     * 7. Shared /product/{slug} priority: LandingPage takes precedence over Product
     */
    public function test_landing_page_priority_over_product_is_preserved()
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
     * 8. Storefront 3-Level Category Hierarchy rendering in shop sidebar (Beauty -> Makeup -> Korean Makeup)
     */
    public function test_08_storefront_3_level_category_hierarchy_rendering_in_sidebar()
    {
        $beauty = Category::create([
            'title'      => 'Beauty Storefront',
            'handle'     => 'beauty-storefront',
            'sort_order' => 1,
            'status'     => true,
        ]);

        $makeup = Category::create([
            'parent_id'  => $beauty->id,
            'title'      => 'Makeup Collection',
            'handle'     => 'makeup-collection',
            'sort_order' => 1,
            'status'     => true,
        ]);

        $koreanMakeup = Category::create([
            'parent_id'  => $makeup->id,
            'title'      => 'Korean Makeup Line',
            'handle'     => 'korean-makeup-line',
            'sort_order' => 1,
            'status'     => true,
        ]);

        $product = Product::create([
            'title'           => 'Korean Cushion BB Cream 15g',
            'slug'            => 'korean-cushion-bb-cream-15g',
            'category_id'     => $koreanMakeup->id,
            'category_handle' => 'korean-makeup-line',
            'regular_price'   => 1250,
            'sale_price'      => 990,
            'status'          => true,
        ]);

        // 1. Visit /collections/beauty-storefront
        $resBeauty = $this->get('/collections/beauty-storefront');
        $resBeauty->assertStatus(200);
        $resBeauty->assertSee('Beauty Storefront');
        $resBeauty->assertSee('Makeup Collection');
        $resBeauty->assertSee('Korean Makeup Line');
        $resBeauty->assertSee('Korean Cushion BB Cream 15g');

        // 2. Visit /collections/makeup-collection
        $resMakeup = $this->get('/collections/makeup-collection');
        $resMakeup->assertStatus(200);
        $resMakeup->assertSee('Makeup Collection');
        $resMakeup->assertSee('Korean Makeup Line');
        $resMakeup->assertSee('Korean Cushion BB Cream 15g');

        // 3. Visit /category/korean-makeup-line
        $resKorean = $this->get('/category/korean-makeup-line');
        $resKorean->assertStatus(200);
        $resKorean->assertSee('Korean Makeup Line');
        $resKorean->assertSee('Korean Cushion BB Cream 15g');
    }

    /**
     * 9. Deep Breadcrumbs on arbitrary depth category pages
     */
    public function test_09_deep_breadcrumbs_on_multi_level_category_pages()
    {
        $beauty = Category::create([
            'title'      => 'Beauty Breadcrumb',
            'handle'     => 'beauty-crumb',
            'status'     => true,
        ]);

        $makeup = Category::create([
            'parent_id'  => $beauty->id,
            'title'      => 'Makeup Breadcrumb',
            'handle'     => 'makeup-crumb',
            'status'     => true,
        ]);

        $koreanMakeup = Category::create([
            'parent_id'  => $makeup->id,
            'title'      => 'Korean Makeup Breadcrumb',
            'handle'     => 'korean-makeup-crumb',
            'status'     => true,
        ]);

        // Test breadcrumb order on Level 3 category: Home > Categories > Beauty > Makeup > Korean Makeup
        $res = $this->get('/category/korean-makeup-crumb');
        $res->assertStatus(200);
        $res->assertSeeInOrder([
            'Home',
            'Categories',
            'Beauty Breadcrumb',
            'Makeup Breadcrumb',
            'Korean Makeup Breadcrumb',
        ]);
    }

    /**
     * 10. /categories page renders recursive multi-level subcategories
     */
    public function test_10_categories_page_renders_multi_level_subcategories_recursively()
    {
        $beauty = Category::create([
            'title'      => 'Beauty AllCat',
            'handle'     => 'beauty-allcat',
            'status'     => true,
        ]);

        $makeup = Category::create([
            'parent_id'  => $beauty->id,
            'title'      => 'Makeup SubCat',
            'handle'     => 'makeup-subcat',
            'status'     => true,
        ]);

        $korean = Category::create([
            'parent_id'  => $makeup->id,
            'title'      => 'Korean SubSubCat',
            'handle'     => 'korean-subsubcat',
            'status'     => true,
        ]);

        $res = $this->get('/categories');
        $res->assertStatus(200);
        $res->assertSee('Beauty AllCat');
        $res->assertSee('Makeup SubCat');
        $res->assertSee('Korean SubSubCat');
    }
}
