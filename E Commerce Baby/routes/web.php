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

/*
|--------------------------------------------------------------------------
| Admin Panel Routes (Captain Crown Style Backend)
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ModulePageController;

// Admin Auth Routes
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Protected Admin Panel Routes
Route::prefix('admin')->middleware('admin.auth')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Orders Management
    Route::resource('orders', OrderController::class);
    Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('/orders/{id}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');

    // Products Catalog
    Route::resource('products', ProductController::class);
    Route::post('/products/{id}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');

    // Categories & Banners
    Route::resource('categories', CategoryController::class);

    // Hero Sliders
    Route::resource('sliders', SliderController::class);

    // Store & Delivery Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Admin Profile & Password
    Route::get('/profile', [AdminAuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AdminAuthController::class, 'updateProfile'])->name('profile.update');

    // Dynamic Module & Sub-page Handlers for all 19 Captain Crown Modules
    Route::get('/module/{module}/{page?}', [ModulePageController::class, 'renderPage'])->name('module.page');
});
