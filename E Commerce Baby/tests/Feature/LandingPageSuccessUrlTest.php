<?php

namespace Tests\Feature;

use App\Models\LandingPage;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageSuccessUrlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A. Valid Chicken Booster landing order:
     * GET /product/chicken-booster/success/{validOrder} -> HTTP 200 -> landing-success view
     */
    public function test_valid_chicken_booster_landing_order_renders_landing_success_view()
    {
        LandingPage::create([
            'slug'         => 'chicken-booster',
            'name'         => 'Chicken Booster (চিকেন বুস্টার)',
            'theme'        => 'chicken-booster',
            'status'       => 'published',
            'product_id'   => 'chicken-booster',
            'product_name' => 'Chicken Booster (চিকেন বুস্টার)',
            'content'      => LandingPage::getDefaultMasterContent(),
        ]);

        $order = Order::create([
            'invoice_no'       => 'CB-20260902-9103A0',
            'customer_name'    => 'Rahim Uddin',
            'customer_phone'   => '01711223344',
            'customer_address' => 'Mirpur 10, Dhaka',
            'city_type'        => 'inside_dhaka',
            'delivery_charge'  => 0,
            'subtotal'         => 1850,
            'discount'         => 0,
            'total_amount'     => 1850,
            'status'           => 'pending',
            'payment_method'   => 'COD',
            'source_type'      => 'landing_page',
            'landing_page'     => '/products/chicken-booster/'
        ]);

        OrderItem::create([
            'order_id'      => $order->id,
            'product_name'  => 'চিকেন বুস্টার (Chicken Booster) - ২ টি প্যাক',
            'product_image' => '',
            'size'          => '1 KG Combo',
            'price'         => 1850,
            'quantity'      => 1,
            'total'         => 1850,
        ]);

        $response = $this->get('/product/chicken-booster/success/CB-20260902-9103A0');
        $response->assertStatus(200);
        $response->assertViewIs('pages.landing-success');
        $response->assertSee('CB-20260902-9103A0');
        $response->assertSee('Rahim Uddin');
        $response->assertSee('1,850');
    }

    /**
     * B. Invalid order -> HTTP 404
     */
    public function test_invalid_order_number_returns_404()
    {
        LandingPage::create([
            'slug'         => 'chicken-booster',
            'name'         => 'Chicken Booster',
            'theme'        => 'chicken-booster',
            'status'       => 'published',
            'product_id'   => 'chicken-booster',
            'product_name' => 'Chicken Booster',
        ]);

        $response = $this->get('/product/chicken-booster/success/CB-NONEXISTENT-999');
        $response->assertStatus(404);
    }

    /**
     * C. Invalid landing-page slug -> HTTP 404
     */
    public function test_invalid_landing_page_slug_returns_404()
    {
        $order = Order::create([
            'invoice_no'       => 'CB-20260902-VALID1',
            'customer_name'    => 'Karim Khan',
            'customer_phone'   => '01811223344',
            'customer_address' => 'Agrabad, Chittagong',
            'city_type'        => 'outside_dhaka',
            'delivery_charge'  => 120,
            'subtotal'         => 990,
            'discount'         => 0,
            'total_amount'     => 1110,
            'status'           => 'pending',
            'payment_method'   => 'COD',
            'source_type'      => 'landing_page',
            'landing_page'     => '/product/nonexistent-slug'
        ]);

        $response = $this->get('/product/nonexistent-slug/success/CB-20260902-VALID1');
        $response->assertStatus(404);
    }

    /**
     * D. Order from another landing page -> HTTP 404 (Security check)
     */
    public function test_order_from_another_landing_page_returns_404()
    {
        LandingPage::create([
            'slug'         => 'chicken-booster',
            'name'         => 'Chicken Booster',
            'theme'        => 'chicken-booster',
            'status'       => 'published',
            'product_id'   => 'chicken-booster',
            'product_name' => 'Chicken Booster',
        ]);

        LandingPage::create([
            'slug'         => 'mediascope-it',
            'name'         => 'MediaScope IT Smart Device',
            'theme'        => 'universal',
            'status'       => 'published',
            'product_id'   => 'mediascope-it',
            'product_name' => 'MediaScope IT Smart Device',
        ]);

        // Order belongs to Chicken Booster
        $cbOrder = Order::create([
            'invoice_no'       => 'CB-20260902-9103A0',
            'customer_name'    => 'Rahim Uddin',
            'customer_phone'   => '01711223344',
            'customer_address' => 'Mirpur 10, Dhaka',
            'city_type'        => 'inside_dhaka',
            'delivery_charge'  => 0,
            'subtotal'         => 1850,
            'discount'         => 0,
            'total_amount'     => 1850,
            'status'           => 'pending',
            'payment_method'   => 'COD',
            'source_type'      => 'landing_page',
            'landing_page'     => '/products/chicken-booster/'
        ]);

        // Accessing Chicken Booster order from mediascope-it slug must be blocked
        $response = $this->get('/product/mediascope-it/success/CB-20260902-9103A0');
        $response->assertStatus(404);
    }

    /**
     * E. Main e-commerce order:
     * GET /order/success/{orderNumber} -> existing behavior unchanged (pages.order-success)
     */
    public function test_main_ecommerce_order_renders_main_success_view()
    {
        $storeOrder = Order::create([
            'invoice_no'       => 'BFB-MAIN-STORE-101',
            'customer_name'    => 'Main Website Shopper',
            'customer_phone'   => '01911223344',
            'customer_address' => 'Gulshan 2, Dhaka',
            'city_type'        => 'inside_dhaka',
            'delivery_charge'  => 60,
            'subtotal'         => 1200,
            'discount'         => 0,
            'total_amount'     => 1260,
            'status'           => 'pending',
            'payment_method'   => 'COD',
            'source_type'      => 'storefront',
            'landing_page'     => '/checkout'
        ]);

        OrderItem::create([
            'order_id'      => $storeOrder->id,
            'product_name'  => 'Baby T-Shirt Set',
            'product_image' => '',
            'size'          => '6-12M',
            'price'         => 1200,
            'quantity'      => 1,
            'total'         => 1200,
        ]);

        $response = $this->get('/order/success/BFB-MAIN-STORE-101');
        $response->assertStatus(200);
        $response->assertViewIs('pages.order-success');
        $response->assertSee('BFB-MAIN-STORE-101');
        $response->assertSee('Main Website Shopper');
    }

    /**
     * F. Dynamic landing page:
     * GET /product/{slug}/success/{orderNumber} -> works for valid matching landing-page orders
     */
    public function test_dynamic_landing_page_success_url_works_for_various_slug_formats()
    {
        LandingPage::create([
            'slug'         => 'baby-butterfly-set',
            'name'         => 'Baby Butterfly Set',
            'theme'        => 'universal',
            'status'       => 'published',
            'product_id'   => 'baby-butterfly-set',
            'product_name' => 'Baby Butterfly Printed Set',
            'content'      => LandingPage::getDefaultMasterContent(),
        ]);

        $formats = [
            '/product/baby-butterfly-set',
            '/products/baby-butterfly-set/',
            'baby-butterfly-set',
            'https://growthagro.shop/product/baby-butterfly-set'
        ];

        foreach ($formats as $idx => $landingFormat) {
            $inv = 'DYN-ORDER-' . ($idx + 1);
            $order = Order::create([
                'invoice_no'       => $inv,
                'customer_name'    => 'Dynamic Buyer ' . ($idx + 1),
                'customer_phone'   => '0170000000' . $idx,
                'customer_address' => 'Banani, Dhaka',
                'city_type'        => 'inside_dhaka',
                'delivery_charge'  => 60,
                'subtotal'         => 899,
                'discount'         => 0,
                'total_amount'     => 959,
                'status'           => 'pending',
                'payment_method'   => 'COD',
                'source_type'      => 'landing_page',
                'landing_page'     => $landingFormat
            ]);

            OrderItem::create([
                'order_id'      => $order->id,
                'product_name'  => 'Baby Butterfly Printed Set',
                'product_image' => '',
                'size'          => '1-2Y',
                'price'         => 899,
                'quantity'      => 1,
                'total'         => 899,
            ]);

            $response = $this->get('/product/baby-butterfly-set/success/' . $inv);
            $response->assertStatus(200);
            $response->assertViewIs('pages.landing-success');
            $response->assertSee($inv);
            $response->assertSee('Dynamic Buyer ' . ($idx + 1));
        }
    }

    /**
     * G. JIT sync fallback regression test:
     *    When the order exists in Laravel DB (simulating post-JIT-sync state),
     *    the success page renders correctly (200 + purchase + no ViewContent).
     *    This directly covers the production 404 scenario where the async sync bridge
     *    failed at order-creation time and the order was later recovered via JIT fallback.
     */
    public function test_success_page_renders_after_jit_sync_fallback()
    {
        LandingPage::create([
            'slug'         => 'chicken-booster',
            'name'         => 'Chicken Booster (চিকেন বুস্টার)',
            'theme'        => 'chicken-booster',
            'status'       => 'published',
            'product_id'   => 'chicken-booster',
            'product_name' => 'Chicken Booster (চিকেন বুস্টার)',
            'content'      => LandingPage::getDefaultMasterContent(),
        ]);

        // Simulate an order that was recovered after JIT sync
        // (same structure as InternalSyncController would create)
        $order = Order::create([
            'invoice_no'       => 'CB-JITSYNC-TEST-001',
            'customer_name'    => 'JIT Test Customer',
            'customer_phone'   => '01712345678',
            'customer_address' => 'Uttara, Dhaka',
            'city_type'        => 'inside_dhaka',
            'delivery_charge'  => 60,
            'subtotal'         => 990,
            'discount'         => 0,
            'total_amount'     => 1050,
            'status'           => 'pending',
            'payment_method'   => 'Cash on Delivery',
            'source_type'      => 'landing_page',
            'landing_page'     => '/product/chicken-booster',
            'note'             => 'Landing Page Order: Chicken Booster',
        ]);

        OrderItem::create([
            'order_id'      => $order->id,
            'product_name'  => 'Chicken Booster (৫০০ গ্রাম)',
            'product_image' => '',
            'size'          => '500g',
            'price'         => 990,
            'quantity'      => 1,
            'total'         => 990,
        ]);

        $response = $this->get('/product/chicken-booster/success/CB-JITSYNC-TEST-001');

        // Must return 200 (not 404) after sync
        $response->assertStatus(200);
        $response->assertViewIs('pages.landing-success');
        $response->assertSee('CB-JITSYNC-TEST-001');
        $response->assertSee('JIT Test Customer');

        // Purchase event must be present; ViewContent must NOT be present
        $response->assertSee("fbq('track', 'Purchase'", false);
        $response->assertDontSee('ViewContent');
    }

    /**
     * H. Security: another landing page's order cannot access success page of a different slug.
     *    Ensures JIT fallback does not weaken cross-order security.
     */
    public function test_jit_sync_does_not_expose_order_across_slugs()
    {
        LandingPage::create([
            'slug'         => 'chicken-booster',
            'name'         => 'Chicken Booster',
            'theme'        => 'chicken-booster',
            'status'       => 'published',
            'product_id'   => 'chicken-booster',
            'product_name' => 'Chicken Booster',
        ]);

        LandingPage::create([
            'slug'         => 'face-serum-bd',
            'name'         => 'Face Serum BD',
            'theme'        => 'universal',
            'status'       => 'published',
            'product_id'   => 'face-serum-bd',
            'product_name' => 'Face Serum BD',
        ]);

        // Order belongs to face-serum-bd
        Order::create([
            'invoice_no'       => 'CB-CROSS-SLUG-001',
            'customer_name'    => 'Cross Slug Buyer',
            'customer_phone'   => '01899887766',
            'customer_address' => 'Farmgate, Dhaka',
            'city_type'        => 'inside_dhaka',
            'delivery_charge'  => 60,
            'subtotal'         => 750,
            'discount'         => 0,
            'total_amount'     => 810,
            'status'           => 'pending',
            'payment_method'   => 'Cash on Delivery',
            'source_type'      => 'landing_page',
            'landing_page'     => '/product/face-serum-bd',
        ]);

        // Accessing face-serum-bd order from chicken-booster slug must return 404
        $response = $this->get('/product/chicken-booster/success/CB-CROSS-SLUG-001');
        $response->assertStatus(404);
    }
}
