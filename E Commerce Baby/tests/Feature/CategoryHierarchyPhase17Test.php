<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryHierarchyPhase17Test extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $productService;

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

        $this->productService = app(ProductService::class);
    }

    /**
     * 1. Top-level category creation via admin API
     */
    public function test_01_top_level_category_creation()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson('/api/admin/categories', [
            'title'       => 'Electronics',
            'handle'      => 'electronics',
            'description' => 'Electronic devices & gadgets',
            'sort_order'  => 1,
            'status'      => true,
            'parent_id'   => null,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success'  => true,
            'category' => [
                'title'     => 'Electronics',
                'handle'    => 'electronics',
                'parent_id' => null,
            ],
        ]);

        $this->assertDatabaseHas('categories', [
            'title'     => 'Electronics',
            'handle'    => 'electronics',
            'parent_id' => null,
        ]);
    }

    /**
     * 2. Subcategory creation linked to parent
     */
    public function test_02_subcategory_creation()
    {
        $this->actingAs($this->admin, 'admin');

        $parent = Category::create([
            'title'      => 'Electronics',
            'handle'     => 'electronics',
            'sort_order' => 1,
            'status'     => true,
        ]);

        $response = $this->postJson('/api/admin/categories', [
            'title'       => 'Mobile',
            'handle'      => 'mobile',
            'description' => 'Mobile phones',
            'sort_order'  => 1,
            'status'      => true,
            'parent_id'   => $parent->id,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success'  => true,
            'category' => [
                'title'     => 'Mobile',
                'handle'    => 'mobile',
                'parent_id' => $parent->id,
            ],
        ]);

        $this->assertDatabaseHas('categories', [
            'title'     => 'Mobile',
            'handle'    => 'mobile',
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * 3. Parent-child relationship retrieval
     */
    public function test_03_parent_child_relationship_retrieval()
    {
        $parent = Category::create([
            'title'      => 'Fashion',
            'handle'     => 'fashion',
            'sort_order' => 1,
            'status'     => true,
        ]);

        $child1 = Category::create([
            'parent_id'  => $parent->id,
            'title'      => "Men's Clothing",
            'handle'     => 'mens-clothing',
            'sort_order' => 1,
            'status'     => true,
        ]);

        $child2 = Category::create([
            'parent_id'  => $parent->id,
            'title'      => "Women's Clothing",
            'handle'     => 'womens-clothing',
            'sort_order' => 2,
            'status'     => true,
        ]);

        // Retrieve children from parent
        $children = $parent->children;
        $this->assertCount(2, $children);
        $this->assertTrue($children->contains('id', $child1->id));
        $this->assertTrue($children->contains('id', $child2->id));

        // Retrieve parent from child
        $this->assertEquals($parent->id, $child1->parent->id);
        $this->assertEquals('Fashion', $child1->parent->title);
    }

    /**
     * 4. Category tree structure
     */
    public function test_04_category_tree_structure()
    {
        $parent = Category::create(['title' => 'Fashion', 'handle' => 'fashion', 'sort_order' => 1, 'status' => true]);
        $sub = Category::create(['parent_id' => $parent->id, 'title' => "Men's Clothing", 'handle' => 'mens-clothing', 'sort_order' => 1, 'status' => true]);

        $descendantIds = $parent->getAllDescendantIds();
        $this->assertContains($sub->id, $descendantIds);
        $this->assertCount(1, $descendantIds);

        // Subcategory has no descendants
        $this->assertEmpty($sub->getAllDescendantIds());
    }

    /**
     * 5. Category slug validation (unique, auto-generation)
     */
    public function test_05_category_slug_validation()
    {
        $this->actingAs($this->admin, 'admin');

        // Auto-generation from title
        $res1 = $this->postJson('/api/admin/categories', [
            'title'  => 'Home & Living Decor',
            'handle' => '',
        ]);
        $res1->assertStatus(201);
        $this->assertDatabaseHas('categories', ['handle' => 'home-living-decor']);

        // Duplicate handle validation
        $res2 = $this->postJson('/api/admin/categories', [
            'title'  => 'Another Category',
            'handle' => 'home-living-decor',
        ]);
        $res2->assertStatus(422);
    }

    /**
     * 6. Prevention of self-parenting
     */
    public function test_06_prevention_of_self_parenting()
    {
        $this->actingAs($this->admin, 'admin');

        $cat = Category::create(['title' => 'Gadgets', 'handle' => 'gadgets', 'sort_order' => 1, 'status' => true]);

        $response = $this->putJson("/api/admin/categories/{$cat->id}", [
            'parent_id' => $cat->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['success' => false]);
    }

    /**
     * 7. Prevention of circular hierarchy
     */
    public function test_07_prevention_of_circular_hierarchy()
    {
        $this->actingAs($this->admin, 'admin');

        $root = Category::create(['title' => 'Electronics', 'handle' => 'electronics', 'sort_order' => 1, 'status' => true]);
        $child = Category::create(['parent_id' => $root->id, 'title' => 'Mobile', 'handle' => 'mobile', 'sort_order' => 1, 'status' => true]);
        $grandchild = Category::create(['parent_id' => $child->id, 'title' => 'Smartphones', 'handle' => 'smartphones', 'sort_order' => 1, 'status' => true]);

        // Attempting to make root's parent grandchild (circular!)
        $response = $this->putJson("/api/admin/categories/{$root->id}", [
            'parent_id' => $grandchild->id,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Circular category hierarchy detected: A category cannot have one of its descendants as its parent.',
        ]);
    }

    /**
     * 8. Safe deletion prevention when child categories exist
     */
    public function test_08_safe_deletion_prevention_when_child_categories_exist()
    {
        $this->actingAs($this->admin, 'admin');

        $parent = Category::create(['title' => 'Vehicles', 'handle' => 'vehicles', 'status' => true]);
        Category::create(['parent_id' => $parent->id, 'title' => 'Cars', 'handle' => 'cars', 'status' => true]);

        $response = $this->deleteJson("/api/admin/categories/{$parent->id}");

        $response->assertStatus(422);
        $response->assertJsonFragment(['success' => false]);
        $this->assertDatabaseHas('categories', ['id' => $parent->id]);
    }

    /**
     * 9. Safe deletion prevention when products are linked
     */
    public function test_09_safe_deletion_prevention_when_products_are_linked()
    {
        $this->actingAs($this->admin, 'admin');

        $cat = Category::create(['title' => 'Sports', 'handle' => 'sports', 'status' => true]);
        Product::create([
            'category_id'     => $cat->id,
            'category_handle' => $cat->handle,
            'title'           => 'Football',
            'slug'            => 'football',
            'regular_price'   => 500,
            'sale_price'      => 450,
            'stock'           => 10,
            'status'          => true,
        ]);

        $response = $this->deleteJson("/api/admin/categories/{$cat->id}");

        $response->assertStatus(422);
        $response->assertJsonFragment(['success' => false]);
        $this->assertDatabaseHas('categories', ['id' => $cat->id]);
    }

    /**
     * 10. Successful deletion of empty leaf category
     */
    public function test_10_successful_deletion_of_empty_leaf_category()
    {
        $this->actingAs($this->admin, 'admin');

        $cat = Category::create(['title' => 'Temporary', 'handle' => 'temporary', 'status' => true]);

        $response = $this->deleteJson("/api/admin/categories/{$cat->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('categories', ['id' => $cat->id]);
    }

    /**
     * 11. Product assignment to subcategory
     */
    public function test_11_product_assignment_to_subcategory()
    {
        $parent = Category::create(['title' => 'Fashion', 'handle' => 'fashion', 'status' => true]);
        $sub = Category::create(['parent_id' => $parent->id, 'title' => "Men's Clothing", 'handle' => 'mens-clothing', 'status' => true]);

        $product = Product::create([
            'category_id'     => $sub->id,
            'category_handle' => $sub->handle,
            'title'           => "Men's Polo Shirt",
            'slug'            => 'mens-polo-shirt',
            'regular_price'   => 800,
            'sale_price'      => 650,
            'stock'           => 20,
            'status'          => true,
        ]);

        $this->assertEquals($sub->id, $product->category->id);
        $this->assertEquals($parent->id, $product->category->parent_id);
    }

    /**
     * 12. Storefront /categories displays subcategories grouped under parents
     */
    public function test_12_storefront_categories_displays_subcategories_under_parents()
    {
        $parent = Category::create(['title' => 'Electronics', 'handle' => 'electronics', 'sort_order' => 1, 'status' => true]);
        Category::create(['parent_id' => $parent->id, 'title' => 'Earphones', 'handle' => 'earphones', 'sort_order' => 1, 'status' => true]);

        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertSee('Electronics');
        $response->assertSee('Earphones');
    }

    /**
     * 13. Storefront /category/{slug} for parent returns parent + all descendant products
     */
    public function test_13_storefront_parent_category_returns_parent_and_descendant_products()
    {
        $parent = Category::create(['title' => 'Electronics', 'handle' => 'electronics', 'sort_order' => 1, 'status' => true]);
        $sub1 = Category::create(['parent_id' => $parent->id, 'title' => 'Audio', 'handle' => 'audio', 'sort_order' => 1, 'status' => true]);
        $sub2 = Category::create(['parent_id' => $parent->id, 'title' => 'Mobile', 'handle' => 'mobile', 'sort_order' => 2, 'status' => true]);

        Product::create([
            'category_id'     => $parent->id,
            'category_handle' => 'electronics',
            'title'           => 'Universal Adaptor',
            'slug'            => 'universal-adaptor',
            'regular_price'   => 500,
            'sale_price'      => 400,
            'stock'           => 10,
            'status'          => true,
        ]);

        Product::create([
            'category_id'     => $sub1->id,
            'category_handle' => 'audio',
            'title'           => 'Wireless Earbuds',
            'slug'            => 'wireless-earbuds',
            'regular_price'   => 1500,
            'sale_price'      => 1200,
            'stock'           => 15,
            'status'          => true,
        ]);

        Product::create([
            'category_id'     => $sub2->id,
            'category_handle' => 'mobile',
            'title'           => 'Smartphone Holder',
            'slug'            => 'smartphone-holder',
            'regular_price'   => 300,
            'sale_price'      => 250,
            'stock'           => 25,
            'status'          => true,
        ]);

        $response = $this->get('/category/electronics');
        $response->assertStatus(200);
        $response->assertSee('Universal Adaptor');
        $response->assertSee('Wireless Earbuds');
        $response->assertSee('Smartphone Holder');
    }

    /**
     * 14. Storefront /category/{slug} for subcategory returns only subcategory products
     */
    public function test_14_storefront_subcategory_returns_only_subcategory_products()
    {
        $parent = Category::create(['title' => 'Electronics', 'handle' => 'electronics', 'sort_order' => 1, 'status' => true]);
        $sub = Category::create(['parent_id' => $parent->id, 'title' => 'Audio', 'handle' => 'audio', 'sort_order' => 1, 'status' => true]);

        Product::create([
            'category_id'     => $parent->id,
            'category_handle' => 'electronics',
            'title'           => 'Electronics Big TV',
            'slug'            => 'electronics-big-tv',
            'regular_price'   => 20000,
            'sale_price'      => 18000,
            'stock'           => 5,
            'status'          => true,
        ]);

        Product::create([
            'category_id'     => $sub->id,
            'category_handle' => 'audio',
            'title'           => 'Pro Audio Headphones',
            'slug'            => 'pro-audio-headphones',
            'regular_price'   => 2500,
            'sale_price'      => 2100,
            'stock'           => 10,
            'status'          => true,
        ]);

        $response = $this->get('/category/audio');
        $response->assertStatus(200);
        $response->assertSee('Pro Audio Headphones');
        $response->assertDontSee('Electronics Big TV');
    }

    /**
     * 15. Homepage top categories contains ONLY top-level categories
     */
    public function test_15_homepage_top_categories_contains_only_top_level_categories()
    {
        // 8 top-level categories
        for ($i = 1; $i <= 8; $i++) {
            Category::create([
                'title'      => "Top Category {$i}",
                'handle'     => "top-category-{$i}",
                'sort_order' => $i,
                'status'     => true,
                'parent_id'  => null,
            ]);
        }

        // Subcategory with sort_order 0 (should NOT be picked for top categories)
        Category::create([
            'title'      => 'Hidden Subcategory',
            'handle'     => 'hidden-subcategory',
            'sort_order' => 0,
            'status'     => true,
            'parent_id'  => 1,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // First 7 top-level categories must appear
        for ($i = 1; $i <= 7; $i++) {
            $response->assertSee("Top Category {$i}");
        }

        // Subcategory must not take any top-category slots
        $collections = $this->productService->getCollections();
        $top7 = array_slice($collections, 0, 7);
        $top7Handles = array_column($top7, 'handle');

        $this->assertNotContains('hidden-subcategory', $top7Handles);
    }

    /**
     * 16. Subcategories do NOT take slots from homepage top categories
     */
    public function test_16_subcategories_do_not_take_slots_from_homepage_top_categories()
    {
        $parent = Category::create(['title' => 'Alpha Top', 'handle' => 'alpha-top', 'sort_order' => 1, 'status' => true]);
        Category::create(['parent_id' => $parent->id, 'title' => 'Beta Sub', 'handle' => 'beta-sub', 'sort_order' => 1, 'status' => true]);
        Category::create(['parent_id' => $parent->id, 'title' => 'Gamma Sub', 'handle' => 'gamma-sub', 'sort_order' => 2, 'status' => true]);

        $cols = $this->productService->getCollections();
        $this->assertCount(1, $cols);
        $this->assertEquals('alpha-top', $cols[0]['handle']);
        $this->assertCount(2, $cols[0]['children']);
    }

    /**
     * 17. ProductService collection queries hierarchy-aware
     */
    public function test_17_product_service_collection_queries_hierarchy_aware()
    {
        $parent = Category::create(['title' => 'Electronics', 'handle' => 'electronics', 'sort_order' => 1, 'status' => true]);
        $sub = Category::create(['parent_id' => $parent->id, 'title' => 'Smart Watches', 'handle' => 'smart-watches', 'sort_order' => 1, 'status' => true]);

        Product::create([
            'category_id'     => $sub->id,
            'category_handle' => $sub->handle,
            'title'           => 'Apple Watch',
            'slug'            => 'apple-watch',
            'regular_price'   => 3000,
            'sale_price'      => 2500,
            'stock'           => 5,
            'status'          => true,
        ]);

        // Querying parent returns subcategory products
        $parentProducts = $this->productService->getProductsByCollection('electronics');
        $this->assertCount(1, $parentProducts);
        $this->assertEquals('Apple Watch', $parentProducts[0]['title']);

        // Querying subcategory returns its products
        $subProducts = $this->productService->getProductsByCollection('smart-watches');
        $this->assertCount(1, $subProducts);
        $this->assertEquals('Apple Watch', $subProducts[0]['title']);
    }

    /**
     * 18. ProductService search returns products matching category or parent category
     */
    public function test_18_product_service_search_hierarchy_aware()
    {
        $parent = Category::create(['title' => 'Fashion & Wear', 'handle' => 'fashion-wear', 'status' => true]);
        $sub = Category::create(['parent_id' => $parent->id, 'title' => 'Hoodies', 'handle' => 'hoodies', 'status' => true]);

        Product::create([
            'category_id'     => $sub->id,
            'category_handle' => $sub->handle,
            'title'           => 'Winter Warm Fleece',
            'slug'            => 'winter-warm-fleece',
            'regular_price'   => 1200,
            'sale_price'      => 999,
            'stock'           => 10,
            'status'          => true,
        ]);

        // Search by parent category title
        $resultsParent = $this->productService->search('Fashion');
        $this->assertCount(1, $resultsParent);
        $this->assertEquals('Winter Warm Fleece', $resultsParent[0]['title']);

        // Search by subcategory title
        $resultsSub = $this->productService->search('Hoodies');
        $this->assertCount(1, $resultsSub);
        $this->assertEquals('Winter Warm Fleece', $resultsSub[0]['title']);
    }

    /**
     * 19. Admin category API returns parent info & children count
     */
    public function test_19_admin_category_api_returns_parent_info_and_children_count()
    {
        $this->actingAs($this->admin, 'admin');

        $parent = Category::create(['title' => 'Electronics', 'handle' => 'electronics', 'status' => true]);
        Category::create(['parent_id' => $parent->id, 'title' => 'Mobile', 'handle' => 'mobile', 'status' => true]);

        $response = $this->getJson('/api/admin/categories');
        $response->assertStatus(200);

        $categories = $response->json('categories');
        $this->assertNotEmpty($categories);

        $parentData = collect($categories)->firstWhere('id', $parent->id);
        $this->assertNotNull($parentData);
        $this->assertEquals(1, $parentData['children_count']);
    }

    /**
     * 20. Admin category API returns nested tree structure
     */
    public function test_20_admin_category_api_returns_nested_tree_structure()
    {
        $this->actingAs($this->admin, 'admin');

        $parent = Category::create(['title' => 'Electronics', 'handle' => 'electronics', 'status' => true]);
        Category::create(['parent_id' => $parent->id, 'title' => 'Mobile', 'handle' => 'mobile', 'status' => true]);

        $response = $this->getJson('/api/admin/categories');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'categories',
            'tree' => [
                '*' => [
                    'id',
                    'title',
                    'handle',
                    'parent_id',
                    'children_count',
                    'children' => [
                        '*' => [
                            'id',
                            'title',
                            'handle',
                            'parent_id',
                        ],
                    ],
                ],
            ],
        ]);

        $tree = $response->json('tree');
        $parentInTree = collect($tree)->firstWhere('id', $parent->id);
        $this->assertNotNull($parentInTree);
        $this->assertCount(1, $parentInTree['children']);
        $this->assertEquals('Mobile', $parentInTree['children'][0]['title']);
    }
}
