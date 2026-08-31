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
