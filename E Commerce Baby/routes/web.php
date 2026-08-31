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

// Internal Node.js to Laravel Landing Order Sync Bridge
use App\Http\Controllers\Api\InternalSyncController;
Route::post('/api/internal/sync-landing-order', [InternalSyncController::class, 'syncLandingOrder'])->name('api.internal.sync-landing-order');
