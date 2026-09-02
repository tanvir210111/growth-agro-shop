<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\LandingPage;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MetaPixelIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Artisan::call('migrate');

        Admin::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('admin123'),
                'role'     => 'super_admin',
            ]
        );
    }

    public function test_guest_cannot_access_marketing_settings_api()
    {
        $getRes = $this->getJson('/api/admin/settings/marketing');
        $getRes->assertStatus(401);

        $postRes = $this->postJson('/api/admin/settings/marketing', [
            'facebook_pixel' => '1793041018387711'
        ]);
        $postRes->assertStatus(401);
    }

    public function test_admin_can_get_and_update_marketing_settings()
    {
        // 1. Update Pixel ID with raw numeric ID
        $postRes = $this->withHeaders([
            'x-admin-token' => 'adm_session'
        ])->postJson('/api/admin/settings/marketing', [
            'facebook_pixel' => '1793041018387711'
        ]);

        $postRes->assertStatus(200);
        $postRes->assertJson([
            'success'  => true,
            'settings' => [
                'facebook_pixel' => '1793041018387711'
            ]
        ]);

        $this->assertEquals('1793041018387711', Setting::get('facebook_pixel'));

        // 2. GET confirms the value
        $getRes = $this->withHeaders([
            'x-admin-token' => 'adm_session'
        ])->getJson('/api/admin/settings/marketing');

        $getRes->assertStatus(200);
        $getRes->assertJson([
            'success'  => true,
            'settings' => [
                'facebook_pixel' => '1793041018387711'
            ]
        ]);
    }

    public function test_normalization_extracts_numeric_pixel_id_from_snippet()
    {
        $snippet = "<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init', '1793041018387711');fbq('track', 'PageView');</script>";

        $postRes = $this->withHeaders([
            'x-admin-token' => 'adm_session'
        ])->postJson('/api/admin/settings/marketing', [
            'facebook_pixel' => $snippet
        ]);

        $postRes->assertStatus(200);
        $this->assertEquals('1793041018387711', Setting::get('facebook_pixel'));
    }

    public function test_invalid_pixel_returns_validation_error()
    {
        $postRes = $this->withHeaders([
            'x-admin-token' => 'adm_session'
        ])->postJson('/api/admin/settings/marketing', [
            'facebook_pixel' => 'invalid-pixel-string-xyz'
        ]);

        $postRes->assertStatus(422);
        $postRes->assertJson([
            'success' => false,
        ]);
    }

    public function test_main_ecommerce_website_renders_meta_pixel()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('connect.facebook.net/en_US/fbevents.js', false);
        $response->assertSee("fbq('init', '1793041018387711');", false);
        $response->assertSee("fbq('track', 'PageView');", false);
    }

    public function test_landing_pages_render_meta_pixel()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        $response = $this->get('/product/chicken-booster');
        $response->assertStatus(200);
        $response->assertSee('connect.facebook.net/en_US/fbevents.js', false);
        $response->assertSee("fbq('init', '1793041018387711');", false);
        $response->assertSee("fbq('track', 'PageView');", false);
    }

    public function test_empty_pixel_does_not_render_script()
    {
        Setting::set('facebook_pixel', '');

        $homeRes = $this->get('/');
        $homeRes->assertStatus(200);
        $homeRes->assertDontSee('connect.facebook.net/en_US/fbevents.js');
        $homeRes->assertDontSee("fbq('init'");

        $lpRes = $this->get('/product/chicken-booster');
        $lpRes->assertStatus(200);
        $lpRes->assertDontSee('connect.facebook.net/en_US/fbevents.js');
        $lpRes->assertDontSee("fbq('init'");

        // Restore for subsequent runs
        Setting::set('facebook_pixel', '1793041018387711');
    }

    public function test_main_ecommerce_product_page_fires_viewcontent()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        $response = $this->get('/product/girls-red-butterfly-printed-t-shirt-floral-shorts-set');
        $response->assertStatus(200);
        // PageView in head
        $response->assertSee("fbq('track', 'PageView');", false);
        // ViewContent in body script with dynamic parameters
        $response->assertSee("fbq('track', 'ViewContent', {", false);
        $response->assertSee("content_ids: ['girls-red-butterfly-printed-t-shirt-floral-shorts-set']", false);
        $response->assertSee("content_type: 'product'", false);
        $response->assertSee("value: 790", false);
        $response->assertSee("currency: 'BDT'", false);
    }

    public function test_landing_page_fires_pageview_only_without_viewcontent_on_load()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        $response = $this->get('/product/chicken-booster');
        $response->assertStatus(200);
        // PageView in head
        $response->assertSee("fbq('track', 'PageView');", false);
        // ViewContent must NOT be present on landing page
        $response->assertDontSee("ViewContent");
    }

    public function test_viewcontent_is_not_fired_on_homepage()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        $response = $this->get('/');
        $response->assertStatus(200);
        // PageView should fire on home
        $response->assertSee("fbq('track', 'PageView');", false);
        // ViewContent should NOT fire on home
        $response->assertDontSee("ViewContent");
    }

    public function test_main_ecommerce_checkout_page_fires_initiatecheckout()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        $response = $this->get('/checkout');
        $response->assertStatus(200);
        $response->assertSee("fbq('track', 'PageView');", false);
        $response->assertSee("fbq('track', 'InitiateCheckout', {", false);
        $response->assertSee("currency: 'BDT'", false);
    }

    public function test_main_ecommerce_product_detail_page_has_initiatecheckout_hook()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        $response = $this->get('/product/girls-red-butterfly-printed-t-shirt-floral-shorts-set');
        $response->assertStatus(200);
        $response->assertSee("triggerDetailInitiateCheckout()", false);
        $response->assertSee("window.fbq('track', 'InitiateCheckout'", false);
    }

    public function test_landing_page_has_initiatecheckout_hook()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        $response = $this->get('/product/chicken-booster');
        $response->assertStatus(200);
        $response->assertSee("fireCheckoutStarted()", false);
        $response->assertSee("window.fbq('track', 'InitiateCheckout'", false);
        $response->assertSee("fireAddToCart()", false);
        $response->assertSee("window.fbq('track', 'AddToCart'", false);
        $response->assertSee("IntersectionObserver", false);
    }

    public function test_main_ecommerce_order_success_page_fires_purchase_event()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        $order = \App\Models\Order::create([
            'invoice_no'       => 'TEST-ORD-999',
            'customer_name'    => 'Test Customer',
            'customer_phone'   => '01711000000',
            'customer_address' => 'Dhaka Test Address',
            'city_type'        => 'inside_dhaka',
            'delivery_charge'  => 70,
            'subtotal'         => 1580,
            'discount'         => 0,
            'total_amount'     => 1650,
            'status'           => 'pending',
            'payment_method'   => 'COD',
            'source_type'      => 'main_website'
        ]);

        \App\Models\OrderItem::create([
            'order_id'      => $order->id,
            'product_name'  => 'Girls Red Butterfly Set',
            'product_image' => '',
            'size'          => 'Standard',
            'price'         => 790,
            'quantity'      => 2,
            'total'         => 1580,
        ]);

        $response = $this->get('/order/success/TEST-ORD-999');
        $response->assertStatus(200);
        $response->assertSee("fbq('track', 'PageView');", false);
        $response->assertSee("window.fbq('track', 'Purchase', {", false);
        $response->assertSee("const totalVal = 1650;", false);
        $response->assertSee("value: totalVal,", false);
        $response->assertSee("currency: 'BDT'", false);
        $response->assertSee("num_items: 2", false);
        $response->assertSee("meta_tracked_purchase_", false);
    }

    public function test_landing_page_redirects_to_source_matched_success_url_on_success()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        $response = $this->get('/product/chicken-booster');
        $response->assertStatus(200);
        $response->assertSee("window.location.href = '/product/' + encodeURIComponent(LANDING_PAGE_SLUG) + '/success/' + encodeURIComponent(orderNo);", false);
    }

    public function test_invalid_order_number_returns_404_on_success_page()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        $response = $this->get('/order/success/NON-EXISTENT-ORDER-999');
        $response->assertStatus(404);
        $response->assertDontSee("window.fbq('track', 'Purchase'");

        $response2 = $this->get('/product/chicken-booster/success/NON-EXISTENT-ORDER-999');
        $response2->assertStatus(404);
        $response2->assertDontSee("window.fbq('track', 'Purchase'");
    }

    public function test_invalid_slug_returns_404_on_success_page()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        $response = $this->get('/product/non-existent-slug-xyz/success/CB-20260901-TEST');
        $response->assertStatus(404);
    }

    public function test_landing_page_order_renders_on_source_matched_success_url_with_purchase_event()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        $order = \App\Models\Order::create([
            'invoice_no'       => 'CB-20260901-TEST',
            'customer_name'    => 'Farmer Rahim',
            'customer_phone'   => '01811000000',
            'customer_address' => 'Gazipur Farm',
            'city_type'        => 'outside_dhaka',
            'delivery_charge'  => 0,
            'subtotal'         => 2300,
            'discount'         => 0,
            'total_amount'     => 2300,
            'status'           => 'pending',
            'payment_method'   => 'COD',
            'source_type'      => 'landing_page',
            'landing_page'     => '/product/chicken-booster'
        ]);

        \App\Models\OrderItem::create([
            'order_id'      => $order->id,
            'product_name'  => 'Broiler Booster (১ কেজি)',
            'product_image' => '',
            'size'          => '1 KG',
            'price'         => 2300,
            'quantity'      => 1,
            'total'         => 2300,
        ]);

        // Direct source-matched success URL
        $response = $this->get('/product/chicken-booster/success/CB-20260901-TEST');
        $response->assertStatus(200);
        $response->assertSee("fbq('track', 'PageView');", false);
        // ViewContent must NOT be present on success page
        $response->assertDontSee("ViewContent");
        $response->assertSee("window.fbq('track', 'Purchase', {", false);
        $response->assertSee("const totalVal = 2300;", false);
        $response->assertSee("value: totalVal,", false);
        $response->assertSee("currency: 'BDT'", false);
        $response->assertSee("num_items: 1", false);
        $response->assertSee("meta_tracked_purchase_", false);
        $response->assertSee("meta_tracked_success_pageview_CB-20260901-TEST", false);
        $response->assertSee("Broiler Booster (১ কেজি)", false);
        $response->assertDontSee("site-header", false);
        $response->assertDontSee("cart-drawer", false);
        $response->assertSee('href="/product/chicken-booster"', false);
        // Receipt button and print structure
        $response->assertSee("btnDownloadReceipt", false);
        $response->assertSee("রসিদ ডাউনলোড / প্রিন্ট করুন", false);
    }

    public function test_old_landing_success_url_redirects_to_source_matched_url()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        $order = \App\Models\Order::create([
            'invoice_no'       => 'CB-20260901-CANONICAL',
            'customer_name'    => 'Farmer Rahim',
            'customer_phone'   => '01811000000',
            'customer_address' => 'Gazipur Farm',
            'city_type'        => 'outside_dhaka',
            'delivery_charge'  => 0,
            'subtotal'         => 2300,
            'discount'         => 0,
            'total_amount'     => 2300,
            'status'           => 'pending',
            'payment_method'   => 'COD',
            'source_type'      => 'landing_page',
            'landing_page'     => '/product/chicken-booster'
        ]);

        \App\Models\OrderItem::create([
            'order_id'      => $order->id,
            'product_name'  => 'Broiler Booster (১ কেজি)',
            'product_image' => '',
            'size'          => '1 KG',
            'price'         => 2300,
            'quantity'      => 1,
            'total'         => 2300,
        ]);

        $response = $this->get('/order/success/CB-20260901-CANONICAL');
        $response->assertStatus(302);
        $response->assertRedirect('/product/chicken-booster/success/CB-20260901-CANONICAL');
    }

    public function test_slug_mismatch_redirects_to_canonical_source_matched_url()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        $order = \App\Models\Order::create([
            'invoice_no'       => 'CB-20260901-MISMATCH',
            'customer_name'    => 'Farmer Rahim',
            'customer_phone'   => '01811000000',
            'customer_address' => 'Gazipur Farm',
            'city_type'        => 'outside_dhaka',
            'delivery_charge'  => 0,
            'subtotal'         => 2300,
            'discount'         => 0,
            'total_amount'     => 2300,
            'status'           => 'pending',
            'payment_method'   => 'COD',
            'source_type'      => 'landing_page',
            'landing_page'     => '/product/chicken-booster'
        ]);

        \App\Models\OrderItem::create([
            'order_id'      => $order->id,
            'product_name'  => 'Broiler Booster (১ কেজি)',
            'product_image' => '',
            'size'          => '1 KG',
            'price'         => 2300,
            'quantity'      => 1,
            'total'         => 2300,
        ]);

        \App\Models\LandingPage::firstOrCreate(
            ['slug' => 'mediascope-it'],
            [
                'name'         => 'MediaScope IT Smart Device',
                'theme'        => 'universal',
                'status'       => 'published',
                'product_id'   => 'mediascope-it',
                'product_name' => 'MediaScope IT Smart Device'
            ]
        );

        // Access Chicken Booster order via mediascope-it URL -> must return 404 (prevent cross-landing-page order exposure)
        $response = $this->get('/product/mediascope-it/success/CB-20260901-MISMATCH');
        $response->assertStatus(404);
    }

    public function test_admin_can_toggle_landing_page_meta_add_to_cart_and_initiate_checkout_independently()
    {
        // 1. Initial default state: both active (true)
        $getRes = $this->withHeaders(['x-admin-token' => 'adm_session'])->getJson('/api/admin/settings/marketing');
        $getRes->assertStatus(200);
        $getRes->assertJson([
            'success'  => true,
            'settings' => [
                'landing_meta_add_to_cart_enabled'       => true,
                'landing_meta_initiate_checkout_enabled' => true,
            ]
        ]);

        // 2. Disable AddToCart, keep InitiateCheckout enabled
        $postRes1 = $this->withHeaders(['x-admin-token' => 'adm_session'])->postJson('/api/admin/settings/marketing', [
            'facebook_pixel'                         => '1793041018387711',
            'landing_meta_add_to_cart_enabled'       => false,
            'landing_meta_initiate_checkout_enabled' => true,
        ]);
        $postRes1->assertStatus(200);
        $postRes1->assertJson([
            'success'  => true,
            'settings' => [
                'landing_meta_add_to_cart_enabled'       => false,
                'landing_meta_initiate_checkout_enabled' => true,
            ]
        ]);
        $this->assertEquals('0', Setting::get('landing_meta_add_to_cart_enabled'));
        $this->assertEquals('1', Setting::get('landing_meta_initiate_checkout_enabled'));

        // 3. Disable InitiateCheckout, enable AddToCart
        $postRes2 = $this->withHeaders(['x-admin-token' => 'adm_session'])->postJson('/api/admin/settings/marketing', [
            'facebook_pixel'                         => '1793041018387711',
            'landing_meta_add_to_cart_enabled'       => true,
            'landing_meta_initiate_checkout_enabled' => false,
        ]);
        $postRes2->assertStatus(200);
        $postRes2->assertJson([
            'success'  => true,
            'settings' => [
                'landing_meta_add_to_cart_enabled'       => true,
                'landing_meta_initiate_checkout_enabled' => false,
            ]
        ]);
        $this->assertEquals('1', Setting::get('landing_meta_add_to_cart_enabled'));
        $this->assertEquals('0', Setting::get('landing_meta_initiate_checkout_enabled'));

        // 4. Disable both
        $postRes3 = $this->withHeaders(['x-admin-token' => 'adm_session'])->postJson('/api/admin/settings/marketing', [
            'facebook_pixel'                         => '1793041018387711',
            'landing_meta_add_to_cart_enabled'       => false,
            'landing_meta_initiate_checkout_enabled' => false,
        ]);
        $postRes3->assertStatus(200);
        $postRes3->assertJson([
            'success'  => true,
            'settings' => [
                'landing_meta_add_to_cart_enabled'       => false,
                'landing_meta_initiate_checkout_enabled' => false,
            ]
        ]);
        $this->assertEquals('0', Setting::get('landing_meta_add_to_cart_enabled'));
        $this->assertEquals('0', Setting::get('landing_meta_initiate_checkout_enabled'));

        // Restore defaults for subsequent tests
        Setting::set('landing_meta_add_to_cart_enabled', '1');
        Setting::set('landing_meta_initiate_checkout_enabled', '1');
    }

    public function test_landing_page_renders_toggle_constants_correctly()
    {
        Setting::set('facebook_pixel', '1793041018387711');

        // State A: Both Enabled
        Setting::set('landing_meta_add_to_cart_enabled', '1');
        Setting::set('landing_meta_initiate_checkout_enabled', '1');
        $resA = $this->get('/product/chicken-booster');
        $resA->assertStatus(200);
        $resA->assertSee('const META_ADD_TO_CART_ENABLED = true;', false);
        $resA->assertSee('const META_INITIATE_CHECKOUT_ENABLED = true;', false);

        // State B: AddToCart OFF, InitiateCheckout ON
        Setting::set('landing_meta_add_to_cart_enabled', '0');
        Setting::set('landing_meta_initiate_checkout_enabled', '1');
        $resB = $this->get('/product/chicken-booster');
        $resB->assertStatus(200);
        $resB->assertSee('const META_ADD_TO_CART_ENABLED = false;', false);
        $resB->assertSee('const META_INITIATE_CHECKOUT_ENABLED = true;', false);

        // State C: AddToCart ON, InitiateCheckout OFF
        Setting::set('landing_meta_add_to_cart_enabled', '1');
        Setting::set('landing_meta_initiate_checkout_enabled', '0');
        $resC = $this->get('/product/chicken-booster');
        $resC->assertStatus(200);
        $resC->assertSee('const META_ADD_TO_CART_ENABLED = true;', false);
        $resC->assertSee('const META_INITIATE_CHECKOUT_ENABLED = false;', false);

        // State D: Both OFF
        Setting::set('landing_meta_add_to_cart_enabled', '0');
        Setting::set('landing_meta_initiate_checkout_enabled', '0');
        $resD = $this->get('/product/chicken-booster');
        $resD->assertStatus(200);
        $resD->assertSee('const META_ADD_TO_CART_ENABLED = false;', false);
        $resD->assertSee('const META_INITIATE_CHECKOUT_ENABLED = false;', false);

        // Restore defaults
        Setting::set('landing_meta_add_to_cart_enabled', '1');
        Setting::set('landing_meta_initiate_checkout_enabled', '1');
    }
}
