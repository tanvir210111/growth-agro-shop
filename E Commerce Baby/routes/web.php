<?php

use App\Http\Controllers\FrontendController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Baby Fashion BD - Web Routes
|--------------------------------------------------------------------------
*/

// Homepage
Route::get('/', [FrontendController::class, 'home'])->name('home');

// Shop & Collections
Route::get('/shop', [FrontendController::class, 'shop'])->name('shop');
Route::get('/collections/{handle}', [FrontendController::class, 'collection'])->name('collection.show');
Route::get('/product/{slug}', [FrontendController::class, 'product'])->name('product.show');

// Search API
Route::get('/api/search', [FrontendController::class, 'search'])->name('api.search');
Route::get('/api/quick-view/{slug}', [FrontendController::class, 'quickView'])->name('api.quick-view');

// Cart & Cart Drawer API
Route::get('/cart', [FrontendController::class, 'cart'])->name('cart.index');
Route::post('/cart/add', [FrontendController::class, 'cartAdd'])->name('cart.add');
Route::post('/cart/update', [FrontendController::class, 'cartUpdate'])->name('cart.update');
Route::post('/cart/remove', [FrontendController::class, 'cartRemove'])->name('cart.remove');
Route::get('/cart/json', [FrontendController::class, 'cartJson'])->name('cart.json');

// Checkout & Cash on Delivery Flow
Route::get('/checkout', [FrontendController::class, 'checkout'])->name('checkout.index');
Route::post('/checkout', [FrontendController::class, 'processCheckout'])->name('checkout.process');
Route::get('/order/success/{orderNumber}', [FrontendController::class, 'orderSuccess'])->name('order.success');

// Informational Pages
Route::get('/about-us', [FrontendController::class, 'about'])->name('about');
Route::get('/contact-us', [FrontendController::class, 'contact'])->name('contact');
Route::get('/policy/{type?}', [FrontendController::class, 'policy'])->name('policy');
// Central Growth Agro Admin Panel
Route::get('/admin', function () {
    return response()->file(public_path('admin/index.html'));
});

Route::get('/admin/login', function () {
    return response()->file(public_path('admin/index.html'));
});

// Database-Backed Admin Authentication Endpoints
use App\Http\Controllers\Api\AdminAuthController;

Route::post('/api/admin/login', [AdminAuthController::class, 'login'])->name('api.admin.login');
Route::post('/api/auth/login', [AdminAuthController::class, 'login'])->name('api.auth.login');
Route::get('/api/admin/me', [AdminAuthController::class, 'me'])->name('api.admin.me');
Route::post('/api/admin/logout', [AdminAuthController::class, 'logout'])->name('api.admin.logout');

// Growth Agro Chicken Booster Campaign Landing Page
Route::get('/products/chicken-booster', function () {
    return response()->file(public_path('products/chicken-booster/index.html'));
})->name('products.chicken-booster');

Route::get('/products/chicken-booster/{any}', function () {
    return response()->file(public_path('products/chicken-booster/index.html'));
})->where('any', '.*');

// Unified Tracking Event Ingestion Endpoint
use App\Http\Controllers\Api\TrackingController;
Route::post('/api/tracking/event', [TrackingController::class, 'recordEvent'])->name('api.tracking.event');

// Central Admin Analytics & Attribution Endpoints
use App\Http\Controllers\Api\AdminAnalyticsController;
Route::get('/api/admin/analytics/overview', [AdminAnalyticsController::class, 'overview'])->name('api.admin.analytics.overview');
Route::get('/api/admin/analytics/funnel', [AdminAnalyticsController::class, 'funnel'])->name('api.admin.analytics.funnel');
Route::get('/api/admin/analytics/attribution', [AdminAnalyticsController::class, 'attribution'])->name('api.admin.analytics.attribution');
Route::get('/api/admin/analytics/campaigns', [AdminAnalyticsController::class, 'campaigns'])->name('api.admin.analytics.campaigns');
Route::get('/api/admin/analytics/landing-pages', [AdminAnalyticsController::class, 'landingPages'])->name('api.admin.analytics.landing-pages');
Route::get('/api/admin/analytics/timeline', [AdminAnalyticsController::class, 'timeline'])->name('api.admin.analytics.timeline');
Route::get('/api/admin/analytics/devices', [AdminAnalyticsController::class, 'devices'])->name('api.admin.analytics.devices');
Route::get('/api/admin/analytics/journey/{order_id}', [AdminAnalyticsController::class, 'journey'])->name('api.admin.analytics.journey');

// Phase 5B: Fraud Detection API endpoints (admin-authenticated, source-agnostic)
Route::get('/api/admin/fraud/overview', [AdminAnalyticsController::class, 'fraudOverview'])->name('api.admin.fraud.overview');
Route::get('/api/admin/fraud/orders/{order_id}', [AdminAnalyticsController::class, 'fraudOrderDetail'])->name('api.admin.fraud.orders.detail');
Route::match(['get', 'post'], '/api/admin/fraud/courier-check', [AdminAnalyticsController::class, 'courierCheck'])->name('api.admin.fraud.courier-check');

// Admin Orders list (used by admin panel JS — includes fraud fields)
Route::get('/api/orders', [AdminAnalyticsController::class, 'ordersIndex'])->name('api.orders.index');
Route::get('/api/orders/{order_number}/status', [AdminAnalyticsController::class, 'ordersIndex'])->name('api.orders.status');
Route::patch('/api/orders/{order_number}/status', function (\Illuminate\Http\Request $request, $order_number) {
    // Simple inline status updater used by admin panel
    $admin = app(\App\Http\Controllers\Api\AdminAnalyticsController::class);
    if (!method_exists($admin, 'authenticateAdmin')) {
        return response()->json(['success' => false], 403);
    }
    $newStatus = $request->input('status', '');
    $updated = \App\Models\Order::where('invoice_no', $order_number)->update(['status' => $newStatus]);
    return response()->json(['success' => $updated > 0]);
})->name('api.orders.updateStatus');

// Forward Public Node Order & Courier API endpoints
Route::post('/api/orders', function (\Illuminate\Http\Request $request) {
    try {
        $nodeHost = env('NODE_HOST', '127.0.0.1');
        $nodePort = env('NODE_PORT', 3000);
        $nodeUrl = "http://{$nodeHost}:{$nodePort}/api/orders";

        $response = \Illuminate\Support\Facades\Http::timeout(10)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
            ->withBody($request->getContent(), 'application/json')
            ->post($nodeUrl);

        return response($response->body(), $response->status())
            ->header('Content-Type', 'application/json');
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('[Laravel Proxy /api/orders Error] ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error'   => 'Order processing service unavailable: ' . $e->getMessage()
        ], 503);
    }
})->name('api.orders.post');

Route::post('/api/checkout/courier-check', function (\Illuminate\Http\Request $request) {
    try {
        $nodeHost = env('NODE_HOST', '127.0.0.1');
        $nodePort = env('NODE_PORT', 3000);
        $nodeUrl = "http://{$nodeHost}:{$nodePort}/api/checkout/courier-check";

        $response = \Illuminate\Support\Facades\Http::timeout(10)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])
            ->withBody($request->getContent(), 'application/json')
            ->post($nodeUrl);

        return response($response->body(), $response->status())
            ->header('Content-Type', 'application/json');
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'error'   => 'Courier check unavailable.'
        ], 503);
    }
})->name('api.checkout.courier-check');

// Central Landing Page Builder / CMS Endpoints
use App\Http\Controllers\Api\AdminLandingPageController;

Route::get('/api/admin/landing-pages/master-defaults', [AdminLandingPageController::class, 'defaults'])->name('api.admin.landing-pages.defaults');
Route::get('/api/admin/landing-pages/check-slug', [AdminLandingPageController::class, 'checkSlug'])->name('api.admin.landing-pages.check-slug');
Route::post('/api/admin/landing-pages/upload-media', [AdminLandingPageController::class, 'uploadMedia'])->name('api.admin.landing-pages.upload-media');
Route::get('/api/admin/landing-pages', [AdminLandingPageController::class, 'index'])->name('api.admin.landing-pages.index');
Route::post('/api/admin/landing-pages', [AdminLandingPageController::class, 'store'])->name('api.admin.landing-pages.store');
Route::get('/api/admin/landing-pages/{id}', [AdminLandingPageController::class, 'show'])->name('api.admin.landing-pages.show');
Route::match(['put', 'patch', 'post'], '/api/admin/landing-pages/{id}', [AdminLandingPageController::class, 'update'])->name('api.admin.landing-pages.update');
Route::post('/api/admin/landing-pages/{id}/duplicate', [AdminLandingPageController::class, 'duplicate'])->name('api.admin.landing-pages.duplicate');
Route::patch('/api/admin/landing-pages/{id}/status', [AdminLandingPageController::class, 'setStatus'])->name('api.admin.landing-pages.status');
Route::delete('/api/admin/landing-pages/{id}', [AdminLandingPageController::class, 'destroy'])->name('api.admin.landing-pages.destroy');

// Admin Preview Route
Route::get('/admin/landing-pages/{id}/preview', function ($id, \Illuminate\Http\Request $request) {
    $landingPage = \App\Models\LandingPage::findOrFail($id);
    $content = $landingPage->content ?: \App\Models\LandingPage::getDefaultMasterContent();
    $deliveryConfig = $landingPage->delivery_config ?: \App\Models\LandingPage::getDefaultDeliveryConfig();
    $themeConfig = $landingPage->theme_config ?: \App\Models\LandingPage::getDefaultThemeConfig();
    $sectionOrder = $landingPage->section_order ?: \App\Models\LandingPage::getDefaultSectionOrder();
    return view('pages.landing-page', compact('landingPage', 'content', 'deliveryConfig', 'themeConfig', 'sectionOrder'));
})->name('admin.landing-pages.preview');

// Internal Node.js to Laravel Landing Order Sync Bridge & Config Lookup
use App\Http\Controllers\Api\InternalSyncController;
Route::post('/api/internal/sync-landing-order', [InternalSyncController::class, 'syncLandingOrder'])->name('api.internal.sync-landing-order');
Route::get('/api/internal/landing-page-config/{slug}', [InternalSyncController::class, 'getLandingPageConfig'])->name('api.internal.landing-page-config');
