<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrderManagementPhase11Test extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected string $adminToken = 'adm_session_1234567890abcdef';

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::firstOrCreate(
            ['email' => 'admin@growthagro.shop'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'status' => 'active',
            ]
        );
    }

    public function test_storefront_checkout_creates_order_with_is_new_true_and_main_website_source()
    {
        $category = Category::create([
            'title' => 'Agro Care',
            'handle' => 'agro-care',
            'is_active' => true,
        ]);

        $product = Product::create([
            'title' => 'Test Organic Fertilizer',
            'slug' => 'test-organic-fertilizer',
            'sale_price' => 500,
            'price' => 500,
            'regular_price' => 600,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $checkoutData = [
            'customer_name' => 'Tanvir Ahmed',
            'customer_phone' => '01711223344',
            'customer_address' => 'House 12, Road 4, Dhanmondi, Dhaka',
            'delivery_area' => 'inside_dhaka',
            'payment_method' => 'cod',
            'direct_product_id' => $product->id,
            'direct_quantity' => 2,
        ];

        $response = $this->post('/checkout', $checkoutData);
        $response->assertRedirect();

        $createdOrder = Order::latest()->first();
        $this->assertNotNull($createdOrder);

        $this->assertEquals('pending', $createdOrder->status);
        $this->assertTrue((bool)$createdOrder->is_new);
        $this->assertEquals('MAIN_WEBSITE', $createdOrder->source_type);
        $this->assertEquals(1000 + (float)$createdOrder->delivery_charge, (float)$createdOrder->total_amount);
    }

    public function test_landing_order_sync_creates_order_with_is_new_true_and_landing_source()
    {
        $internalSecret = env('INTERNAL_API_SECRET', 'baby-fashion-internal-2024-secret');

        $syncPayload = [
            'order_number'     => 'LAND-TEST-999',
            'customer_name'    => 'Karim Rahman',
            'customer_phone'   => '01899887766',
            'customer_address' => 'Sector 7, Uttara, Dhaka',
            'delivery_zone'    => 'inside',
            'delivery_charge'  => 60,
            'subtotal'         => 850,
            'total'            => 910,
            'payment_method'   => 'Cash on Delivery',
            'product_name'     => 'Chicken Booster Pro',
            'variant_name'     => '500g',
            'quantity'         => 1,
            'unit_price'       => 850,
            'landing_page'     => '/product/chicken-booster',
        ];

        $res = $this->postJson('/api/internal/sync-landing-order', $syncPayload, [
            'X-Internal-Secret' => $internalSecret
        ]);

        $res->assertSuccessful()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('orders', [
            'invoice_no'   => 'LAND-TEST-999',
            'status'       => 'pending',
            'is_new'       => true,
            'source_type'  => 'LANDING',
            'landing_page' => '/product/chicken-booster',
            'total_amount' => 910,
        ]);
    }

    public function test_viewing_order_marks_is_new_false_without_changing_status_or_data()
    {
        $order = Order::create([
            'invoice_no' => 'VIEW-TEST-100',
            'customer_name' => 'Rahim Ali',
            'customer_phone' => '01511223344',
            'customer_address' => 'Mirpur 10, Dhaka',
            'city_type' => 'inside_dhaka',
            'delivery_charge' => 60,
            'subtotal' => 1200,
            'discount' => 0,
            'total_amount' => 1260,
            'payment_method' => 'Cash on Delivery',
            'status' => 'pending',
            'is_new' => true,
            'source_type' => 'MAIN_WEBSITE',
        ]);

        $this->assertTrue((bool)$order->is_new);
        $this->assertEquals('pending', $order->status);

        // Call the mark viewed endpoint with admin authorization
        $res = $this->patchJson("/api/orders/{$order->invoice_no}/viewed", [], [
            'x-admin-token' => $this->adminToken
        ]);
        $res->assertSuccessful()
            ->assertJson([
                'success' => true,
                'is_new' => false,
                'status' => 'pending'
            ]);

        $order->refresh();
        $this->assertFalse((bool)$order->is_new);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(1260, $order->total_amount);
        $this->assertEquals('MAIN_WEBSITE', $order->source_type);
    }

    public function test_admin_orders_index_returns_canonical_source_and_is_new()
    {
        Order::create([
            'invoice_no' => 'CANON-WEB-01',
            'customer_name' => 'Web Customer',
            'customer_phone' => '01700000001',
            'customer_address' => 'Dhaka',
            'total_amount' => 500,
            'status' => 'pending',
            'is_new' => true,
            'source_type' => 'MAIN_WEBSITE',
        ]);

        Order::create([
            'invoice_no' => 'CANON-LP-01',
            'customer_name' => 'LP Customer',
            'customer_phone' => '01700000002',
            'customer_address' => 'Chittagong',
            'total_amount' => 850,
            'status' => 'approved',
            'is_new' => false,
            'source_type' => 'LANDING',
        ]);

        $res = $this->getJson('/api/admin/orders', [
            'x-admin-token' => $this->adminToken
        ]);
        $res->assertSuccessful()
            ->assertJson(['success' => true]);

        $orders = collect($res->json('orders'));
        $webOrder = $orders->firstWhere('order_number', 'CANON-WEB-01');
        $lpOrder = $orders->firstWhere('order_number', 'CANON-LP-01');

        $this->assertNotNull($webOrder);
        $this->assertEquals('MAIN_WEBSITE', $webOrder['source']);
        $this->assertTrue($webOrder['is_new']);

        $this->assertNotNull($lpOrder);
        $this->assertEquals('LANDING', $lpOrder['source']);
        $this->assertFalse($lpOrder['is_new']);
    }

    public function test_order_processing_report_aggregates_live_db_counts()
    {
        // Create orders in different statuses
        Order::create([
            'invoice_no' => 'REP-01',
            'customer_name' => 'User 1',
            'customer_phone' => '01700000011',
            'customer_address' => 'Dhaka',
            'total_amount' => 500,
            'status' => 'pending',
            'is_new' => true,
            'source_type' => 'MAIN_WEBSITE',
        ]);

        Order::create([
            'invoice_no' => 'REP-02',
            'customer_name' => 'User 2',
            'customer_phone' => '01700000012',
            'customer_address' => 'Dhaka',
            'total_amount' => 700,
            'status' => 'approved',
            'is_new' => false,
            'source_type' => 'LANDING',
        ]);

        Order::create([
            'invoice_no' => 'REP-03',
            'customer_name' => 'User 3',
            'customer_phone' => '01700000013',
            'customer_address' => 'Dhaka',
            'total_amount' => 1200,
            'status' => 'delivered',
            'is_new' => false,
            'source_type' => 'MAIN_WEBSITE',
        ]);

        $res = $this->getJson('/api/admin/reports/order-processing?period=today', [
            'x-admin-token' => $this->adminToken
        ]);
        $res->assertSuccessful()
            ->assertJson(['success' => true]);

        $metrics = $res->json('metrics');
        $this->assertEquals(3, $metrics['created']);
        $this->assertEquals(1, $metrics['pending']);
        $this->assertEquals(1, $metrics['approved']);
        $this->assertEquals(1, $metrics['delivered']);
        $this->assertEquals(0, $metrics['returned']);
        $this->assertEquals(0, $metrics['canceled']);
    }

    public function test_order_success_page_contains_receipt_print_button()
    {
        $order = Order::create([
            'invoice_no' => 'PRINT-TEST-777',
            'customer_name' => 'Shahidul Islam',
            'customer_phone' => '01722334455',
            'customer_address' => 'Banani, Dhaka',
            'city_type' => 'inside_dhaka',
            'delivery_charge' => 60,
            'subtotal' => 1500,
            'discount' => 0,
            'total_amount' => 1560,
            'payment_method' => 'Cash on Delivery',
            'status' => 'pending',
            'is_new' => true,
            'source_type' => 'MAIN_WEBSITE',
        ]);

        $response = $this->get("/order/success/{$order->invoice_no}");
        $response->assertSuccessful();
        $response->assertSee('রসিদ ডাউনলোড / প্রিন্ট করুন');
        $response->assertSee('window.print()', false);
        $response->assertSee('@media print', false);
    }
}
