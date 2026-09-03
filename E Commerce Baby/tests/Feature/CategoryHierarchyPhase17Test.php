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

    /**
     * 21. User Exact Requirement: Create Agro (top-level) and Chicken Medicine (subcategory under Agro)
     */
    public function test_21_create_agro_as_top_level_and_chicken_medicine_under_agro()
    {
        $this->actingAs($this->admin, 'admin');

        // 1. Create Top-level category 'Agro' with parent_id = null
        $resAgro = $this->postJson('/api/admin/categories', [
            'parent_id'   => null,
            'title'       => 'Agro',
            'handle'      => 'agro',
            'description' => 'Agriculture and animal care',
            'sort_order'  => 1,
            'status'      => true,
        ]);
        $resAgro->assertStatus(201);
        $agroId = $resAgro->json('category.id');
        $this->assertNotNull($agroId);
        $this->assertNull($resAgro->json('category.parent_id'));

        // 2. Create Subcategory 'Chicken Medicine' with parent_id = Agro ID
        $resCM = $this->postJson('/api/admin/categories', [
            'parent_id'   => $agroId,
            'title'       => 'Chicken Medicine',
            'handle'      => 'chicken-medicine',
            'description' => 'Poultry healthcare and medicine',
            'sort_order'  => 1,
            'status'      => true,
        ]);
        $resCM->assertStatus(201);
        $cmId = $resCM->json('category.id');
        $this->assertNotNull($cmId);
        $this->assertEquals($agroId, $resCM->json('category.parent_id'));
        $this->assertEquals('Agro', $resCM->json('category.parent.title'));

        // 3. Verify Database contains Agro -> Chicken Medicine hierarchy
        $this->assertDatabaseHas('categories', [
            'id'        => $agroId,
            'title'     => 'Agro',
            'handle'    => 'agro',
            'parent_id' => null,
        ]);

        $this->assertDatabaseHas('categories', [
            'id'        => $cmId,
            'title'     => 'Chicken Medicine',
            'handle'    => 'chicken-medicine',
            'parent_id' => $agroId,
        ]);

        // 4. Verify category list returns Chicken Medicine right under Agro
        $resIndex = $this->getJson('/api/admin/categories');
        $resIndex->assertStatus(200);
        $cats = $resIndex->json('categories');

        $foundCM = collect($cats)->firstWhere('id', $cmId);
        $this->assertNotNull($foundCM);
        $this->assertEquals($agroId, $foundCM['parent_id']);
        $this->assertEquals('Agro', $foundCM['parent']['title']);

        // 5. Verify tree contains Agro with Chicken Medicine child
        $tree = $resIndex->json('tree');
        $agroInTree = collect($tree)->firstWhere('id', $agroId);
        $this->assertNotNull($agroInTree);
        $this->assertEquals(1, $agroInTree['children_count']);
        $this->assertEquals('Chicken Medicine', $agroInTree['children'][0]['title']);
    }

    /**
     * 22. Create subcategory under another parent and verify parent_id persists across fresh reloads
     */
    public function test_22_create_subcategory_under_another_parent_and_persistence()
    {
        $this->actingAs($this->admin, 'admin');

        $parent = Category::create(['title' => 'Livestock Care', 'handle' => 'livestock-care', 'status' => true]);

        $res = $this->postJson('/api/admin/categories', [
            'parent_id'   => $parent->id,
            'title'       => 'Cattle Feed',
            'handle'      => 'cattle-feed',
            'description' => 'Nutritional cattle feed',
            'sort_order'  => 2,
            'status'      => true,
        ]);
        $res->assertStatus(201);
        $subId = $res->json('category.id');

        // Fresh DB query (simulating page reload)
        $subCategory = Category::with('parent')->find($subId);
        $this->assertNotNull($subCategory);
        $this->assertEquals($parent->id, $subCategory->parent_id);
        $this->assertEquals('Livestock Care', $subCategory->parent->title);
    }

    /**
     * 23. Edit subcategory parent to another parent or to top-level
     */
    public function test_23_edit_subcategory_parent()
    {
        $this->actingAs($this->admin, 'admin');

        $parent1 = Category::create(['title' => 'Parent One', 'handle' => 'parent-one', 'status' => true]);
        $parent2 = Category::create(['title' => 'Parent Two', 'handle' => 'parent-two', 'status' => true]);

        $sub = Category::create(['parent_id' => $parent1->id, 'title' => 'Child Category', 'handle' => 'child-category', 'status' => true]);

        // Change parent from parent1 to parent2
        $resUpdate = $this->putJson("/api/admin/categories/{$sub->id}", [
            'parent_id' => $parent2->id,
        ]);
        $resUpdate->assertStatus(200);
        $this->assertEquals($parent2->id, $resUpdate->json('category.parent_id'));
        $this->assertDatabaseHas('categories', ['id' => $sub->id, 'parent_id' => $parent2->id]);

        // Change parent to null (convert to top-level)
        $resTop = $this->putJson("/api/admin/categories/{$sub->id}", [
            'parent_id' => null,
        ]);
        $resTop->assertStatus(200);
        $this->assertNull($resTop->json('category.parent_id'));
        $this->assertDatabaseHas('categories', ['id' => $sub->id, 'parent_id' => null]);
    }

    /**
     * 24. Invalid parent_id rejected with HTTP 422
     */
    public function test_24_invalid_parent_id_rejected()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->postJson('/api/admin/categories', [
            'parent_id' => 999999, // non-existent category
            'title'     => 'Bad Child',
            'handle'    => 'bad-child',
            'status'    => true,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['parent_id']);
    }

    /**
     * 25. Self-parenting and circular hierarchy rejected on update with HTTP 422
     */
    public function test_25_self_parenting_and_circular_hierarchy_rejected()
    {
        $this->actingAs($this->admin, 'admin');

        $parent = Category::create(['title' => 'Parent', 'handle' => 'parent-circ', 'status' => true]);
        $child = Category::create(['parent_id' => $parent->id, 'title' => 'Child', 'handle' => 'child-circ', 'status' => true]);

        // Self-parenting
        $resSelf = $this->putJson("/api/admin/categories/{$parent->id}", [
            'parent_id' => $parent->id,
        ]);
        $resSelf->assertStatus(422);
        $resSelf->assertJsonValidationErrors(['parent_id']);

        // Circular hierarchy: parent choosing its child as parent
        $resCirc = $this->putJson("/api/admin/categories/{$parent->id}", [
            'parent_id' => $child->id,
        ]);
        $resCirc->assertStatus(422);
        $resCirc->assertJsonValidationErrors(['parent_id']);
    }

    /**
     * 26. Products continue using correct category_id
     */
    public function test_26_products_continue_using_correct_category_id()
    {
        $this->actingAs($this->admin, 'admin');

        $parent = Category::create(['title' => 'Agro Main', 'handle' => 'agro-main', 'status' => true]);
        $sub = Category::create(['parent_id' => $parent->id, 'title' => 'Chicken Booster', 'handle' => 'chicken-booster-sub', 'status' => true]);

        $product = Product::create([
            'category_id'     => $sub->id,
            'category_handle' => $sub->handle,
            'title'           => 'Booster Formula 500g',
            'slug'            => 'booster-formula-500g',
            'regular_price'   => 850,
            'sale_price'      => 750,
            'stock'           => 50,
            'status'          => true,
        ]);

        $this->assertDatabaseHas('products', [
            'id'          => $product->id,
            'category_id' => $sub->id,
        ]);

        $resIndex = $this->getJson('/api/admin/categories');
        $resIndex->assertStatus(200);
        $subInApi = collect($resIndex->json('categories'))->firstWhere('id', $sub->id);
        $this->assertEquals(1, $subInApi['products_count']);
    }

    /**
     * 27. Phase 18: Full 5-Level Category Chain Creation (Agro -> Poultry -> Chicken Medicine -> Antibiotics -> Penicillin)
     */
    public function test_27_full_5_level_category_chain_creation_and_persistence()
    {
        $this->actingAs($this->admin, 'admin');

        // Level 1: Agro (Top-Level)
        $res1 = $this->postJson('/api/admin/categories', [
            'parent_id'   => null,
            'title'       => 'Agro',
            'handle'      => 'agro-p18',
            'description' => 'Agro top-level',
            'status'      => true,
        ]);
        $res1->assertStatus(201);
        $lvl1Id = $res1->json('category.id');
        $this->assertNull($res1->json('category.parent_id'));

        // Level 2: Poultry (under Agro)
        $res2 = $this->postJson('/api/admin/categories', [
            'parent_id'   => $lvl1Id,
            'title'       => 'Poultry',
            'handle'      => 'poultry-p18',
            'description' => 'Poultry section',
            'status'      => true,
        ]);
        $res2->assertStatus(201);
        $lvl2Id = $res2->json('category.id');
        $this->assertEquals($lvl1Id, $res2->json('category.parent_id'));

        // Level 3: Chicken Medicine (under Poultry)
        $res3 = $this->postJson('/api/admin/categories', [
            'parent_id'   => $lvl2Id,
            'title'       => 'Chicken Medicine',
            'handle'      => 'chicken-medicine-p18',
            'description' => 'Chicken medicine section',
            'status'      => true,
        ]);
        $res3->assertStatus(201);
        $lvl3Id = $res3->json('category.id');
        $this->assertEquals($lvl2Id, $res3->json('category.parent_id'));

        // Level 4: Antibiotics (under Chicken Medicine)
        $res4 = $this->postJson('/api/admin/categories', [
            'parent_id'   => $lvl3Id,
            'title'       => 'Antibiotics',
            'handle'      => 'antibiotics-p18',
            'description' => 'Antibiotics section',
            'status'      => true,
        ]);
        $res4->assertStatus(201);
        $lvl4Id = $res4->json('category.id');
        $this->assertEquals($lvl3Id, $res4->json('category.parent_id'));

        // Level 5: Penicillin (under Antibiotics)
        $res5 = $this->postJson('/api/admin/categories', [
            'parent_id'   => $lvl4Id,
            'title'       => 'Penicillin',
            'handle'      => 'penicillin-p18',
            'description' => 'Penicillin sub-sub-sub-subcategory',
            'status'      => true,
        ]);
        $res5->assertStatus(201);
        $lvl5Id = $res5->json('category.id');
        $this->assertEquals($lvl4Id, $res5->json('category.parent_id'));

        // Verify all 5 levels exist in database with accurate parent_ids
        $this->assertDatabaseHas('categories', ['id' => $lvl1Id, 'parent_id' => null, 'title' => 'Agro']);
        $this->assertDatabaseHas('categories', ['id' => $lvl2Id, 'parent_id' => $lvl1Id, 'title' => 'Poultry']);
        $this->assertDatabaseHas('categories', ['id' => $lvl3Id, 'parent_id' => $lvl2Id, 'title' => 'Chicken Medicine']);
        $this->assertDatabaseHas('categories', ['id' => $lvl4Id, 'parent_id' => $lvl3Id, 'title' => 'Antibiotics']);
        $this->assertDatabaseHas('categories', ['id' => $lvl5Id, 'parent_id' => $lvl4Id, 'title' => 'Penicillin']);
    }

    /**
     * 28. Phase 18: Recursive Tree Structure and accurate children_count at every nesting level
     */
    public function test_28_recursive_api_tree_at_5_levels_with_correct_children_count()
    {
        $this->actingAs($this->admin, 'admin');

        $c1 = Category::create(['title' => 'Agro Tree', 'handle' => 'agro-tree', 'status' => true]);
        $c2 = Category::create(['parent_id' => $c1->id, 'title' => 'Poultry Tree', 'handle' => 'poultry-tree', 'status' => true]);
        $c3 = Category::create(['parent_id' => $c2->id, 'title' => 'Chicken Med Tree', 'handle' => 'chicken-med-tree', 'status' => true]);
        $c4a = Category::create(['parent_id' => $c3->id, 'title' => 'Antibiotics Tree', 'handle' => 'antibiotics-tree', 'status' => true]);
        $c4b = Category::create(['parent_id' => $c3->id, 'title' => 'Vitamins Tree', 'handle' => 'vitamins-tree', 'status' => true]);
        $c5 = Category::create(['parent_id' => $c4a->id, 'title' => 'Penicillin Tree', 'handle' => 'penicillin-tree', 'status' => true]);

        $res = $this->getJson('/api/admin/categories');
        $res->assertStatus(200);

        $tree = $res->json('tree');
        $node1 = collect($tree)->firstWhere('id', $c1->id);
        $this->assertNotNull($node1);
        $this->assertEquals(0, $node1['depth']);
        $this->assertEquals(1, $node1['children_count']);

        // Level 2
        $node2 = $node1['children'][0];
        $this->assertEquals($c2->id, $node2['id']);
        $this->assertEquals(1, $node2['depth']);
        $this->assertEquals(1, $node2['children_count']);

        // Level 3
        $node3 = $node2['children'][0];
        $this->assertEquals($c3->id, $node3['id']);
        $this->assertEquals(2, $node3['depth']);
        $this->assertEquals(2, $node3['children_count']); // Antibiotics and Vitamins

        // Level 4
        $node4a = collect($node3['children'])->firstWhere('id', $c4a->id);
        $this->assertNotNull($node4a);
        $this->assertEquals(3, $node4a['depth']);
        $this->assertEquals(1, $node4a['children_count']);

        $node4b = collect($node3['children'])->firstWhere('id', $c4b->id);
        $this->assertNotNull($node4b);
        $this->assertEquals(3, $node4b['depth']);
        $this->assertEquals(0, $node4b['children_count']);

        // Level 5
        $node5 = $node4a['children'][0];
        $this->assertEquals($c5->id, $node5['id']);
        $this->assertEquals(4, $node5['depth']);
        $this->assertEquals(0, $node5['children_count']);
        $this->assertEmpty($node5['children']);
    }

    /**
     * 29. Phase 18: Edit Category Parent across unrelated branches
     */
    public function test_29_edit_category_parent_across_unrelated_branches()
    {
        $this->actingAs($this->admin, 'admin');

        $agro = Category::create(['title' => 'Agro Branch', 'handle' => 'agro-branch', 'status' => true]);
        $poultry = Category::create(['parent_id' => $agro->id, 'title' => 'Poultry Branch', 'handle' => 'poultry-branch', 'status' => true]);
        $med = Category::create(['parent_id' => $poultry->id, 'title' => 'Med Branch', 'handle' => 'med-branch', 'status' => true]);

        $cattle = Category::create(['title' => 'Cattle Branch', 'handle' => 'cattle-branch', 'status' => true]);

        // Move Med from Poultry (under Agro) to Cattle
        $res = $this->putJson("/api/admin/categories/{$med->id}", [
            'parent_id' => $cattle->id,
        ]);
        $res->assertStatus(200);
        $this->assertEquals($cattle->id, $res->json('category.parent_id'));
        $this->assertDatabaseHas('categories', [
            'id'        => $med->id,
            'parent_id' => $cattle->id,
        ]);
    }

    /**
     * 30. Phase 18: Prevent selecting descendant at any depth (Deep Circular Hierarchy Prevention)
     */
    public function test_30_edit_category_prevents_selecting_descendant_at_any_depth()
    {
        $this->actingAs($this->admin, 'admin');

        $agro = Category::create(['title' => 'Agro Circ', 'handle' => 'agro-circ', 'status' => true]);
        $poultry = Category::create(['parent_id' => $agro->id, 'title' => 'Poultry Circ', 'handle' => 'poultry-circ', 'status' => true]);
        $med = Category::create(['parent_id' => $poultry->id, 'title' => 'Med Circ', 'handle' => 'med-circ', 'status' => true]);
        $anti = Category::create(['parent_id' => $med->id, 'title' => 'Anti Circ', 'handle' => 'anti-circ', 'status' => true]);
        $pen = Category::create(['parent_id' => $anti->id, 'title' => 'Pen Circ', 'handle' => 'pen-circ', 'status' => true]);

        // 1. Agro (Level 1) cannot select Penicillin (Level 5) as parent
        $res1 = $this->putJson("/api/admin/categories/{$agro->id}", [
            'parent_id' => $pen->id,
        ]);
        $res1->assertStatus(422);
        $res1->assertJsonValidationErrors(['parent_id']);

        // 2. Poultry (Level 2) cannot select Penicillin (Level 5) as parent
        $res2 = $this->putJson("/api/admin/categories/{$poultry->id}", [
            'parent_id' => $pen->id,
        ]);
        $res2->assertStatus(422);
        $res2->assertJsonValidationErrors(['parent_id']);

        // 3. Med (Level 3) cannot select Anti (Level 4) as parent
        $res3 = $this->putJson("/api/admin/categories/{$med->id}", [
            'parent_id' => $anti->id,
        ]);
        $res3->assertStatus(422);
        $res3->assertJsonValidationErrors(['parent_id']);
    }

    /**
     * 31. Phase 18: Products assigned to deep subcategory and hierarchical storefront filtering
     */
    public function test_31_products_at_deep_subcategories_and_hierarchical_filtering()
    {
        $this->actingAs($this->admin, 'admin');

        $c1 = Category::create(['title' => 'Store Agro', 'handle' => 'store-agro', 'status' => true]);
        $c2 = Category::create(['parent_id' => $c1->id, 'title' => 'Store Poultry', 'handle' => 'store-poultry', 'status' => true]);
        $c3 = Category::create(['parent_id' => $c2->id, 'title' => 'Store Med', 'handle' => 'store-med', 'status' => true]);
        $c4 = Category::create(['parent_id' => $c3->id, 'title' => 'Store Anti', 'handle' => 'store-anti', 'status' => true]);
        $c5 = Category::create(['parent_id' => $c4->id, 'title' => 'Store Penicillin', 'handle' => 'store-penicillin', 'status' => true]);

        $unrelated = Category::create(['title' => 'Store Cattle', 'handle' => 'store-cattle', 'status' => true]);

        // Product in Level 5: Penicillin
        $product = Product::create([
            'category_id'     => $c5->id,
            'category_handle' => $c5->handle,
            'title'           => 'Penicillin Injection 10ml',
            'slug'            => 'penicillin-injection-10ml',
            'regular_price'   => 500,
            'sale_price'      => 450,
            'stock'           => 100,
            'status'          => true,
        ]);

        // 1. Filter by Level 1 (Store Agro) -> Should contain product
        $prodsLvl1 = $this->productService->getProductsByCollection('store-agro');
        $this->assertCount(1, $prodsLvl1);
        $this->assertEquals('Penicillin Injection 10ml', $prodsLvl1[0]['title']);

        // 2. Filter by Level 2 (Store Poultry) -> Should contain product
        $prodsLvl2 = $this->productService->getProductsByCollection('store-poultry');
        $this->assertCount(1, $prodsLvl2);

        // 3. Filter by Level 3 (Store Med) -> Should contain product
        $prodsLvl3 = $this->productService->getProductsByCollection('store-med');
        $this->assertCount(1, $prodsLvl3);

        // 4. Filter by Level 4 (Store Anti) -> Should contain product
        $prodsLvl4 = $this->productService->getProductsByCollection('store-anti');
        $this->assertCount(1, $prodsLvl4);

        // 5. Filter by Level 5 (Store Penicillin) -> Should contain product
        $prodsLvl5 = $this->productService->getProductsByCollection('store-penicillin');
        $this->assertCount(1, $prodsLvl5);

        // 6. Filter by Unrelated branch -> Should NOT contain product
        $prodsUnrelated = $this->productService->getProductsByCollection('store-cattle');
        $this->assertCount(0, $prodsUnrelated);
    }

    /**
     * 32. Phase 18: Hierarchical list preserves depth-first ordering and depth metadata
     */
    public function test_32_hierarchical_list_preserves_depth_first_ordering()
    {
        $this->actingAs($this->admin, 'admin');

        $c1 = Category::create(['title' => 'Main Agro', 'handle' => 'main-agro', 'status' => true]);
        $c2 = Category::create(['parent_id' => $c1->id, 'title' => 'Sub Poultry', 'handle' => 'sub-poultry', 'status' => true]);
        $c3 = Category::create(['parent_id' => $c2->id, 'title' => 'Sub Med', 'handle' => 'sub-med', 'status' => true]);

        $res = $this->getJson('/api/admin/categories');
        $res->assertStatus(200);

        $categories = $res->json('categories');
        $idx1 = collect($categories)->search(fn($c) => $c['id'] === $c1->id);
        $idx2 = collect($categories)->search(fn($c) => $c['id'] === $c2->id);
        $idx3 = collect($categories)->search(fn($c) => $c['id'] === $c3->id);

        $this->assertTrue($idx1 !== false && $idx2 !== false && $idx3 !== false);
        $this->assertTrue($idx1 < $idx2 && $idx2 < $idx3);
        $this->assertEquals(0, $categories[$idx1]['depth']);
        $this->assertEquals(1, $categories[$idx2]['depth']);
        $this->assertEquals(2, $categories[$idx3]['depth']);
    }
}
