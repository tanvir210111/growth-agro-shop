<?php


use Spatie\Sitemap\SitemapGenerator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App as FacadeApp; // App-এর বদলে FacadeApp ব্যবহার করুন
use App\Http\Controllers\Front\FrontendController;
use App\Http\Controllers\WhyChooseUsController;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AdminTicketController; // এটি আপনার web.php এর উপরে যোগ করুন
use App\Http\Controllers\User\UserResetController;
use App\Http\Controllers\User\ClientController;
use App\Http\Controllers\User\HostingController;
use App\Http\Controllers\Admin\HostingManageController;














Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
    Route::post('/generate-api-key', [ClientController::class, 'generateKey'])->name('generate.api_key');
});

Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function() {

});







// এসএমএস রিচার্জ পেমেন্ট কলব্যাক (auth বাইরে – গেটওয়ে রিডাইরেক্টে সেশন থাকে না, সাকসেসে আবার লগইন করানো হয়)







Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function() {
    
});

// ডোমেইন ও হোস্টিং পেমেন্ট কলব্যাক রাউট (নতুন কেনার জন্য)
Route::match(['get', 'post'], '/domain-payment/success', [FrontendController::class, 'domainPaymentSuccess'])->name('front.domain.payment.success');
Route::get('/domain-payment/cancel', [FrontendController::class, 'domainPaymentCancel'])->name('front.domain.payment.cancel');
Route::post('/domain-payment/webhook', [FrontendController::class, 'domainPaymentWebhook'])->name('front.domain.payment.webhook');

/*
|--------------------------------------------------------------------------
| Static Pages Routes
|--------------------------------------------------------------------------
*/
Route::get('/privacy-policy', [App\Http\Controllers\PageController::class, 'privacyPolicy'])->name('privacy.policy');
Route::get('/terms-of-service', [App\Http\Controllers\PageController::class, 'termsOfService'])->name('terms.service');
Route::get('/data-deletion', [App\Http\Controllers\PageController::class, 'dataDeletion'])->name('data.deletion');
Route::get('/about-us', [App\Http\Controllers\PageController::class, 'aboutUs'])->name('about.us');
Route::get('/contact-us', [App\Http\Controllers\PageController::class, 'contactUs'])->name('contact.us');






Route::group(['prefix' => 'user', 'middleware' => ['auth', 'user']], function () {
    
    // হোস্টিং প্যাকেজ দেখা
    Route::get('/hosting', [HostingController::class, 'index'])->name('user.hosting.index');
	
Route::post('/hosting/password-update/{id}', [ClientController::class, 'updateHostingPassword'])->name('user.hosting.password.update');
Route::get('/hosting/details/{id}', [ClientController::class, 'hostingDetails'])->name('user.hosting.details');
    // হোস্টিং কেনার রিকোয়েস্ট
    Route::post('/hosting/buy', [HostingController::class, 'purchase'])->name('user.hosting.buy');

});




Route::group(['prefix' => 'admin/manage', 'middleware' => ['auth:admin']], function () {
    
    // হোস্টিং ম্যানেজমেন্ট
    Route::get('/user-hostings', [HostingManageController::class, 'userHostings'])->name('admin.user.hostings');
    Route::post('/user-hosting/status-update/{id}', [HostingManageController::class, 'updateHostingStatus'])->name('admin.user.hosting.status');
    Route::get('/user-hosting/delete/{id}', [HostingManageController::class, 'deleteUserHosting'])->name('admin.user.hosting.delete');

    // ডোমেইন ম্যানেজমেন্ট
    Route::get('/user-domains', [HostingManageController::class, 'userDomains'])->name('admin.user.domains');
    Route::get('/user-domains/delete/{id}', [HostingManageController::class, 'deleteUserDomain'])->name('admin.user.domains.delete');
    Route::post('/user-domains/update/{id}', [HostingManageController::class, 'updateUserDomainNS'])->name('admin.user.domains.update');

});

// ১. ইউজারদের জন্য রাউট (এটি ইউজার গ্রুপের ভেতরে থাকবে)
Route::prefix('user')->middleware('auth')->group(function() {
    Route::get('/hosting/login-session/{id}', [\App\Http\Controllers\User\ClientController::class, 'userCpanelLogin'])->name('user.hosting.cp_login');
});


// ২. অ্যাডমিনদের জন্য রাউট (এটি আপনার আগের কোডেই আছে)
Route::group(['prefix' => 'admin/manage', 'middleware' => ['auth:admin']], function () {
    Route::get('/user-hosting/cpanel-login/{id}', [HostingManageController::class, 'cpanelLogin'])->name('admin.user.hosting.cp_login');
});

Route::group(['prefix' => 'admin/manage', 'middleware' => ['auth:admin']], function () {
    
    // ১. সার্ভার ম্যানেজমেন্ট
    Route::get('/servers', [HostingManageController::class, 'serverIndex'])->name('admin.hosting.servers');
    Route::post('/servers/store', [HostingManageController::class, 'serverStore'])->name('admin.hosting.servers.store');
    Route::get('/servers/delete/{id}', [HostingManageController::class, 'serverDelete'])->name('admin.hosting.servers.delete');

    // ২. হোস্টিং প্যাকেজ ম্যানেজমেন্ট
    Route::get('/plans', [HostingManageController::class, 'planIndex'])->name('admin.hosting.plans');
    Route::post('/plans/store', [HostingManageController::class, 'planStore'])->name('admin.hosting.plans.store');
    Route::get('/plans/delete/{id}', [HostingManageController::class, 'planDelete'])->name('admin.hosting.plans.delete');
Route::post('/plans/update/{id}', [HostingManageController::class, 'planUpdate'])->name('admin.hosting.plans.update');
    // ৩. ডোমেইন প্রাইসিং ম্যানেজমেন্ট
    Route::get('/domains', [HostingManageController::class, 'domainIndex'])->name('admin.hosting.domains');
    Route::post('/domains/store', [HostingManageController::class, 'domainStore'])->name('admin.hosting.domains.store');
    Route::post('/domains/update', [HostingManageController::class, 'domainUpdate'])->name('admin.hosting.domains.update');
    Route::get('/domains/delete/{id}', [HostingManageController::class, 'domainDelete'])->name('admin.hosting.domains.delete');
Route::get('/servers/login/{id}', [HostingManageController::class, 'serverLogin'])->name('admin.hosting.servers.login');
// সার্ভার আপডেট করার রাউট (এটিই আপনার মিসিং রাউট)
    Route::post('/servers/update/{id}', [HostingManageController::class, 'serverUpdate'])->name('admin.hosting.servers.update');
	// ডোমেইন প্রাইস আপডেট রাউট
Route::post('/domains/update/{id}', [HostingManageController::class, 'domainUpdate'])->name('admin.hosting.domains.update');
});

Route::get('/admin/pos/download/{order_number}', [PosController::class, 'downloadInvoice'])->name('admin.pos.download');
// --- Frontend / Public Routes ---
// ইনভয়েস লাইভ দেখার রাউট (এটি সবার জন্য উন্মুক্ত থাকবে)
// সংশোধিত রাউট
Route::get('/invoice/live/{order_number}', [\App\Http\Controllers\Front\FrontendController::class, 'liveInvoice'])->name('frontend.invoice.live');
// --- Admin Private Routes (Authenticated) ---
Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function() {

    // POS & Invoice Management
    Route::get('/pos/create', [PosController::class, 'index'])->name('admin.pos.index'); // পস সেল পেজ
    Route::post('/pos/store', [PosController::class, 'store'])->name('admin.pos.store'); // সেল সেভ করা
    Route::get('/pos/invoices', [PosController::class, 'allInvoices'])->name('admin.pos.invoices'); // সকল ইনভয়েস তালিকা
    Route::post('/pos/due-collect/{id}', [PosController::class, 'collectDue'])->name('admin.pos.due_collect'); // বকেয়া সংগ্রহ

    // Customer Management
    Route::resource('customer', CustomerController::class)->names([
        'index' => 'admin.customer.index',
        'edit' => 'admin.customer.edit',
        'update' => 'admin.customer.update',
        'destroy' => 'admin.customer.destroy',
    ]);

    // Expense Management
    Route::resource('expense', ExpenseController::class)->names([
        'index' => 'admin.expense.index',
        'store' => 'admin.expense.store',
    ]);

    // Fund & Report Management
    Route::get('/report/fund-statement', [ReportController::class, 'fundStatement'])->name('admin.report.fund'); // ফান্ড স্টেটমেন্ট
    Route::post('/report/fund-add', [ReportController::class, 'addMoney'])->name('admin.report.fund_add'); // ফান্ডে টাকা জমা
    Route::post('/report/fund-withdraw', [ReportController::class, 'withdrawMoney'])->name('admin.report.fund_withdraw'); // ফান্ড থেকে উত্তোলন

});



Route::get('/hosting', [App\Http\Controllers\Front\FrontendController::class, 'hosting'])->name('frontend.hosting');








Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function() {
    
    // টিকেট তালিকা
    Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
    
    // টিকেট ভিউ এবং চ্যাট
    Route::get('/tickets/view/{id}', [AdminTicketController::class, 'view'])->name('tickets.view');
    
    // অ্যাডমিন রিপ্লাই (মেসেজ সেন্ড)
    Route::post('/tickets/reply/{id}', [AdminTicketController::class, 'reply'])->name('tickets.reply');
    
    // টিকেট ডিলিট
    Route::get('/tickets/delete/{id}', [AdminTicketController::class, 'delete'])->name('tickets.delete');

    // [NEW] স্ট্যাটাস আপডেট (Pending/Replied/Closed)
    Route::post('/tickets/status/{id}', [AdminTicketController::class, 'updateStatus'])->name('tickets.status');
    
    // [NEW] অটোমেটিক নতুন মেসেজ চেক (AJAX Polling)
    Route::get('/tickets/check-new/{id}', [AdminTicketController::class, 'checkNewMessages'])->name('tickets.check_new');
    // [এখানে বসান] ফ্রড প্যাকেজ রাউট (সঠিক জায়গা)

});




Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function() {
    Route::resource('team', 'App\Http\Controllers\Admin\TeamController')->names('admin.team');
});
Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function() {
    Route::resource('counter', 'App\Http\Controllers\Admin\CounterController')->names('admin.counter');
});
Route::resource('testimonial', 'App\Http\Controllers\Admin\TestimonialController')->names('admin.testimonial');

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function() {
    Route::resource('portfolio-category', 'App\Http\Controllers\Admin\PortfolioCategoryController');
    Route::resource('portfolio', 'App\Http\Controllers\Admin\PortfolioController');
	Route::resource('pricing', 'App\Http\Controllers\Admin\PricingPlanController');
});





// Brand Logo Routes
Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function() {

    Route::resource('brand', 'App\Http\Controllers\Admin\BrandController')->names([
        'index'   => 'admin.brand.index',
        'create'  => 'admin.brand.create',
        'store'   => 'admin.brand.store',
        'edit'    => 'admin.brand.edit',
        'update'  => 'admin.brand.update',
        'destroy' => 'admin.brand.destroy',
    ]);

});



Route::get('admin/why-choose-us', [WhyChooseUsController::class, 'index'])->name('admin.why-choose-us.index');
Route::get('admin/why-choose-us/create', [WhyChooseUsController::class, 'create'])->name('admin.why-choose-us.create');
Route::post('admin/why-choose-us/store', [WhyChooseUsController::class, 'store'])->name('admin.why-choose-us.store');
Route::delete('admin/why-choose-us/{id}/destroy', [WhyChooseUsController::class, 'destroy'])->name('admin.why-choose-us.destroy');
Route::get('admin/why-choose-us/edit/{id}', [WhyChooseUsController::class, 'edit'])->name('admin.why-choose-us.edit');
Route::post('admin/why-choose-us/update/{id}', [WhyChooseUsController::class, 'update'])->name('admin.why-choose-us.update');


// Service Section Routes
Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function() {
    
    // এখানে except(['show']) যোগ করা হয়েছে যাতে লারাভেল show মেথড না খোঁজে
    Route::resource('service', \App\Http\Controllers\Admin\ServiceController::class, ['except' => ['show']])->names([
        'index'   => 'admin.service.index',
        'create'  => 'admin.service.create',
        'store'   => 'admin.service.store',
        'edit'    => 'admin.service.edit',
        'update'  => 'admin.service.update',
        'destroy' => 'admin.service.destroy',
    ]);

    Route::get('service/edit-text', [\App\Http\Controllers\Admin\ServiceController::class, 'editContent'])->name('admin.service.edit_text');
    Route::post('service/update-text', [\App\Http\Controllers\Admin\ServiceController::class, 'updateContent'])->name('admin.service.update_text');
// ৩. About সেকশন (অনলাইন ব্যবসার সহযোগী) এডিট ও আপডেট রাউট
    Route::get('about-section/edit', [\App\Http\Controllers\Admin\ServiceController::class, 'editAboutSection'])->name('admin.about.edit');
    Route::post('about-section/update', [\App\Http\Controllers\Admin\ServiceController::class, 'updateAboutSection'])->name('admin.about.update');
    

});

// অ্যাডমিন এরিয়া (Consultancy & Contact Management)
Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function() {
    
    // Consultancy Routes
    Route::get('/consultancy', [\App\Http\Controllers\Admin\ConsultancyController::class, 'index'])->name('admin.consultancy.index');
    Route::get('/consultancy/status/{id}/{status}', [\App\Http\Controllers\Admin\ConsultancyController::class, 'status'])->name('admin.consultancy.status');
    Route::get('/consultancy/delete/{id}', [\App\Http\Controllers\Admin\ConsultancyController::class, 'destroy'])->name('admin.consultancy.delete');
    
    // Contact Message Routes
    Route::get('/contact-messages', [\App\Http\Controllers\Admin\ContactController::class, 'index'])->name('admin.contact.index');
    Route::get('/contact/delete/{id}', [\App\Http\Controllers\Admin\ContactController::class, 'destroy'])->name('admin.contact.delete');

    // Fraud Checker (BD Courier)
    Route::get('/fraud-checker', [\App\Http\Controllers\Admin\FraudCheckController::class, 'index'])->name('admin.fraud.index');
    Route::post('/fraud-checker/check', [\App\Http\Controllers\Admin\FraudCheckController::class, 'check'])->name('admin.fraud.check');
    Route::get('/fraud-checker/logs', [\App\Http\Controllers\Admin\FraudCheckController::class, 'logs'])->name('admin.fraud.logs');
    Route::get('/fraud-checker/logs/delete/{id}', [\App\Http\Controllers\Admin\FraudCheckController::class, 'destroy'])->name('admin.fraud.delete');
    Route::get('/fraud-checker/courier-accounts', [\App\Http\Controllers\Admin\CourierAccountController::class, 'index'])->name('admin.fraud.couriers');
    Route::post('/fraud-checker/courier-accounts', [\App\Http\Controllers\Admin\CourierAccountController::class, 'update'])->name('admin.fraud.couriers.update');

});

// ফ্রন্টএন্ড সাবমিট রাউটস (ফাইলের শেষে যোগ করুন)
Route::post('/free-consultant/store', [\App\Http\Controllers\Admin\ConsultancyController::class, 'storeConsultancy'])->name('frontend.consultancy.store');
Route::post('/contact-submit', [\App\Http\Controllers\Admin\ContactController::class, 'store'])->name('frontend.contact.store');

// User Fraud Checker
Route::middleware(['auth'])->prefix('user')->group(function () {
    Route::get('/fraud-checker', [\App\Http\Controllers\User\FraudCheckController::class, 'index'])->name('user.fraud.index');
    Route::post('/fraud-checker/check', [\App\Http\Controllers\User\FraudCheckController::class, 'check'])->name('user.fraud.check');
    Route::get('/fraud-checker/logs', [\App\Http\Controllers\User\FraudCheckController::class, 'logs'])->name('user.fraud.logs');
});

Route::redirect('admin', 'admin/login');

Route::get('/service', function () {
    return view('frontend.service');
});

Route::get('/about', function () {
    return view('frontend.about');
});

Route::get('/portfolio', function () {
    return view('frontend.portfolio');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function() {
    
    // ১. প্রথমে ডাটাসমূহ আনার জন্য এই AJAX রাউটটি বসান
    Route::get('products/datatables', [\App\Http\Controllers\Admin\ProductController::class, 'datatables'])->name('products.datatables');
    // প্রোডাক্ট বা থিম ম্যানেজমেন্টের জন্য রিসোর্স রাউট (index, create, store, edit, update, destroy সব আছে)
    Route::resource('products', 'App\Http\Controllers\Admin\ProductController');
    
});

// ফ্রন্টএন্ড ডিটেইলস পেজ রাউট (স্লাগ সহ)
Route::get('/theme/{slug}', [App\Http\Controllers\Front\FrontendController::class, 'productDetails'])->name('frontend.product.details');

// Team Route (সংশোধিত)
Route::get('/team', [\App\Http\Controllers\Front\FrontendController::class, 'team'])->name('frontend.team');
Route::get('/freeconsultant', function () {
    return view('frontend.freeconsultant');
});
Route::get('/contact', function () {
    return view('frontend.contact');
});

// নাম 'front.pricing' থেকে পরিবর্তন করে 'frontend.pricing' করা হলো
Route::get('/priceing', [\App\Http\Controllers\Front\FrontendController::class, 'pricing'])->name('frontend.pricing');





 



Route::get('/language/{id}', function ($id) {
    $language = DB::table('languages')->find($id);

    if ($language) {
        Session::put('language', $language->id);
        Session::put('locale', $language->code ?? 'en'); // উদাহরণ: 'en' বা 'bn'
        App::setLocale($language->code ?? 'en');
    }

    return redirect()->back();
})->name('front.language.change');



Route::prefix('admin')->group(function(){
    Route::get('/login','Admin\LoginController@loginForm')->name('admin.loginForm');
    Route::post('/login','Admin\LoginController@login')->name('admin.login');

    Route::get('/forgot', 'Admin\LoginController@showForgotForm')->name('admin.forgot');
    Route::post('/forgot', 'Admin\LoginController@forgot')->name('admin.forgot.submit');


    Route::group(['middleware' => 'permissions:menu_builder'], function () {
        //--------------Menu Builder Area---------------
        Route::get('menu-builder','Admin\MenuBuilderController@index')->name('admin.menu.builder');
        Route::post('menu-builder/store','Admin\MenuBuilderController@store')->name('admin.menu.builder.store');
        //--------------Menu Builder Area---------------
    });

  

 

    Route::group(['middleware' => 'permissions:languages'], function () {

        //------------Language area--------------
        Route::get('/language/datatables','Admin\LanguageController@datatables')->name('language.datatables');
        Route::get('/add-language','Admin\LanguageController@index')->name('admin.language.index');
        Route::get('/add-language/create','Admin\LanguageController@create')->name('admin.language.create');
        Route::post('/add-language','Admin\LanguageController@store')->name('admin.language.store');
        Route::get('/add-language/edit/{id}','Admin\LanguageController@edit')->name('admin.language.edit');
        Route::post('/add-language/update/{id}','Admin\LanguageController@update')->name('admin.language.update');
        Route::get('/add-language/delete/{id}','Admin\LanguageController@delete')->name('admin.language.delete');
        Route::get('/languages/status/{id}', 'Admin\LanguageController@status')->name('admin.language.status');
        //------------Language area end--------------


        //-------------Admin Language Area--------------
        Route::get('/admin-language/datatables','Admin\AdminLanguageController@datatables')->name('admin_language.datatables');
        Route::get('/admin-add-language','Admin\AdminLanguageController@index')->name('admin.admin_language.index');
        Route::get('/admin-add-language/create','Admin\AdminLanguageController@create')->name('admin.admin_language.create');
        Route::post('/admin-add-language','Admin\AdminLanguageController@store')->name('admin.admin_language.store');
        Route::get('/admin-add-language/edit/{id}','Admin\AdminLanguageController@edit')->name('admin.admin_language.edit');
        Route::post('/admin-add-language/update/{id}','Admin\AdminLanguageController@update')->name('admin.admin_language.update');
        Route::get('/admin-add-language/delete/{id}','Admin\AdminLanguageController@delete')->name('admin.admin_language.delete');
        Route::get('/admin-languages/status/{id}', 'Admin\AdminLanguageController@status')->name('admin.admin_language.status');
        //-------------Admin Language Area--------------
    });





    Route::group(['middleware' => 'permissions:general_settings'], function () {

        //------------General Settings-----------
        Route::post('/generalsettings/update','Admin\GeneralSettingsController@update')->name('admin.generalsettings.update');
        Route::get('/generalsettings/logo','Admin\GeneralSettingsController@logo')->name('admin.generalsettings.logo');
        Route::get('/generalsettings/favicon','Admin\GeneralSettingsController@favicon')->name('admin.generalsettings.favicon');
        Route::get('/generalsettings/loader','Admin\GeneralSettingsController@loader')->name('admin.generalsettings.loader');
        Route::get('/generalsettings/website/content','Admin\GeneralSettingsController@websiteContent')->name('admin.generalsettings.websiteContent');
        Route::get('/generalsettings/footer','Admin\GeneralSettingsController@footer')->name('admin.generalsettings.footer');
        Route::get('/generalsettings/error/page','Admin\GeneralSettingsController@errorPage')->name('admin.generalsettings.errorPage');
        Route::get('/generalsettings/popular/tags','Admin\GeneralSettingsController@popularTags')->name('admin.generalsettings.popularTags');

        Route::get('/generalsettings/tawakto/{x}','Admin\GeneralSettingsController@tawkto')->name('admin.generalsettings.tawkto');
        Route::get('/generalsettings/isLoader/{x}','Admin\GeneralSettingsController@isLoader')->name('admin.generalsettings.isLoader');
        Route::get('/generalsettings/isAdminLoader/{x}','Admin\GeneralSettingsController@isAdminLoader')->name('admin.generalsettings.isAdminLoader');
        Route::get('/generalsettings/disqus/{x}','Admin\GeneralSettingsController@disqus')->name('admin.generalsettings.disqus');
        Route::get('/generalsettings/smtp/{x}','Admin\GeneralSettingsController@smtp')->name('admin.generalsettings.smtp');
        Route::Get('/generalsettings/capcha/{x}','Admin\GeneralSettingsController@capcha')->name('admin.generalsettings.capcha');
        Route::Get('/generalsettings/emailverfication/{x}','Admin\GeneralSettingsController@emailVerfication')->name('admin.generalsettings.emailverfication');


        //-------------Language Base Logo Area----------------
        Route::get('/language/logo/datatables','Admin\LogoController@datatables')->name('admin.languagelogo.datatables');
        Route::get('/language/logo','Admin\LogoController@index')->name('admin.languagelogo.index');
        Route::get('/language/logo/create','Admin\LogoController@create')->name('admin.languagelogo.create');
        Route::post('/language/logo','Admin\LogoController@store')->name('admin.languagelogo.store');
        Route::get('/language/logo/edit/{id}','Admin\LogoController@edit')->name('admin.languagelogo.edit');
        Route::post('/language/logo/update/{id}','Admin\LogoController@update')->name('admin.languagelogo.update');
        Route::get('/language/logo/delete/{id}','Admin\LogoController@delete')->name('admin.languagelogo.delete');
        //-------------Language Base Logo Area----------------

        //------------General Settings-----------
    });



    Route::group(['middleware' => 'permissions:seo_tools'], function () {

        //------------Seo Management-------------
        Route::post('seo/update','Admin\SeoController@update')->name('seo.update');
        Route::get('seo/google-analytics','Admin\SeoController@googleAnalytics')->name('seo.google.analytics');
        Route::get('seo/meta-keywords','Admin\SeoController@metaKeywords')->name('seo.meta.keywords');
        //------------Seo Management-------------
    });


    Route::group(['middleware' => 'permissions:social_settings'], function () {

        //-------------SocialSettings Manage-----------
        Route::post('social-settings/update','Admin\SocialSettingsController@update')->name('social.settings.update');
        Route::get('social-settings/google','Admin\SocialSettingsController@google')->name('social.settings.google');
        Route::get('social-settings/facebook','Admin\SocialSettingsController@facebook')->name('social.settings.facebook');
        //-------------SocialSettings Manage-----------

        //-------------SocialLink Manage-------------
        Route::get('social-link/datatables','Admin\SocialLinkController@datatables')->name('social.link.datatables');
        Route::get('social-link','Admin\SocialLinkController@index')->name('social.link.index');
        Route::get('social-link/create','Admin\SocialLinkController@create')->name('social.link.create');
        Route::post('social-link','Admin\SocialLinkController@store')->name('social.link.store');
        Route::get('social-link/edit/{id}','Admin\SocialLinkController@edit')->name('social.link.edit');
        Route::post('social-link/update/{id}','Admin\SocialLinkController@update')->name('social.link.update');
        Route::get('social-link/delete/{id}','Admin\SocialLinkController@delete')->name('social.link.delete');
        //-------------SocialLink Manage-------------
    });


    Route::group(['middleware' => 'permissions:pages'], function () {

        //-------------Page Create Area----------------
        Route::get('page/datatables','Admin\PageController@datatables')->name('admin.page.datatables');
        Route::get('/page','Admin\PageController@index')->name('admin.page.index');
        Route::get('/page/create','Admin\PageController@create')->name('admin.page.create');
        Route::post('/page','Admin\PageController@store')->name('admin.page.store');
        Route::get('/page/edit/{id}','Admin\PageController@edit')->name('admin.page.edit');
        Route::post('/page/update/{id}','Admin\PageController@update')->name('admin.page.update');
        Route::get('/page/delete/{id}','Admin\PageController@delete')->name('admin.page.delete');
        Route::get('/page/slugCreate','Admin\PageController@slugCreate')->name('admin.page.slugCreate');
        //-------------Page Create Area----------------
    });


    Route::group(['middleware' => 'permissions:emails_settings'], function () {

        //---------------Email Manage-------------------
        Route::get('email/config','Admin\EmailController@config')->name('admin.email.config');
        Route::get('email/group','Admin\EmailController@group')->name('admin.email.group');
        Route::post('email/group','Admin\EmailController@groupmailsend')->name('admin.email.groupmailsend');
        //---------------Email Manage-------------------
    });


    Route::group(['middleware' => 'permissions:newsLetter'], function () {

        //---------------Subscriber----------------------
        Route::get('/subscriber/datatables','Admin\SubscriberController@datatables')->name('admin.subscriber.datatables');
        Route::get('/subscriber','Admin\SubscriberController@index')->name('admin.subscriber.index');
        Route::get('/subscriber/download','Admin\SubscriberController@download')->name('admin.subscriber.download');
        Route::get('/send-mail','Admin\SubscriberController@email')->name('admin.subscriber.email');
        Route::post('/send-mail','Admin\SubscriberController@sendemail')->name('admin.subscriber.sendemail');
        //---------------Subscriber----------------------
    });


    Route::group(['middleware' => 'permissions:role_management'], function () {

        //----------------Role Management-----------------
        Route::get('/role/datatables','Admin\RoleController@datatables')->name('admin.role.datatables');
        Route::get('/role','Admin\RoleController@index')->name('admin.role.index');
        Route::get('/role/create','Admin\RoleController@create')->name('admin.role.create');
        Route::post('/role','Admin\RoleController@store')->name('admin.role.store');
        Route::get('/role/edit/{id}','Admin\RoleController@edit')->name('admin.role.edit');
        Route::post('/role/update/{id}','Admin\RoleController@update')->name('admin.role.update');
        Route::get('/role/update/{id}','Admin\RoleController@delete')->name('admin.role.delete');
        //----------------Role Management-----------------
    });


    Route::group(['middleware' => 'permissions:user_management'], function () {
        //----------------Staff Management----------------
        Route::get('/user/datatables','Admin\StaffController@datatables')->name('admin.staff.datatables');
        Route::get('/user','Admin\StaffController@index')->name('admin.staff.index');
        Route::get('/user/create','Admin\StaffController@create')->name('admin.staff.create');
        Route::post('/user','Admin\StaffController@store')->name('admin.staff.store');
        Route::get('/user/edit/{id}','Admin\StaffController@edit')->name('admin.staff.edit');
        Route::post('/user/update/{id}','Admin\StaffController@update')->name('admin.staff.update');
        Route::get('/user/delete/{id}','Admin\StaffController@delete')->name('admin.staff.delete');
        //----------------Staff Management----------------
    });

    Route::group(['middleware' => 'permissions:administration_management'], function () {
        //----------------Staff Management----------------
        Route::get('/administator/datatables','Admin\AdministerController@datatables')->name('admin.administator.datatables');
        Route::get('/administator','Admin\AdministerController@index')->name('admin.administator.index');
        Route::get('/administator/create','Admin\AdministerController@create')->name('admin.administator.create');
        Route::post('/administator/store','Admin\AdministerController@store')->name('admin.administator.store');
        Route::get('/administator/edit/{id}','Admin\AdministerController@edit')->name('admin.administator.edit');
        Route::post('/administator/update/{id}','Admin\AdministerController@update')->name('admin.administator.update');
        Route::get('/administator/delete/{id}','Admin\AdministerController@delete')->name('admin.administator.delete');
        //----------------Staff Management----------------
    });





    Route::group(['middleware' => 'permissions:site_map'], function () {
        Route::get('/sitemaps', 'Admin\SiteMapController@all')->name('admin.sitemap.all');
        Route::get('/sitemap.xml', 'Admin\SiteMapController@index')->name('sitemap.index');
        Route::get('/sitemap/categories.xml', 'Admin\SiteMapController@categories')->name('sitemap.categories');
        Route::get('/sitemap/subcategories.xml', 'Admin\SiteMapController@subcategories')->name('sitemap.subcategories');
        Route::get('/sitemap/posts.xml', 'Admin\SiteMapController@posts')->name('sitemap.posts');
    });

    Route::group(['middleware' => 'permissions:font_option'], function () {
        Route::get('/fonts/datatables','Admin\FontController@datatables')->name('admin.fonts.datatables');
        Route::get('/fonts','Admin\FontController@index')->name('fonts.index');
        Route::get('/fonts/status/{id}', 'Admin\FontController@status')->name('admin.fonts.status');
    });

    Route::group(['middleware' => 'permissions:cache_management'], function () {
        //----------------Cache Management-----------------
        Route::get('/cache','Admin\CacheController@clear')->name('admin.cache.clear');
        //----------------Cache Management-----------------
    });

    Route::get('/logout','Admin\DashboardController@logout')->name('admin.logout');
    Route::get('/dashboard','Admin\DashboardController@index')->name('admin.dashboard');

    Route::get('/profile', 'Admin\DashboardController@profile')->name('admin.profile');
    Route::post('/profile', 'Admin\DashboardController@profileUpdate')->name('admin.profile.update');
    Route::get('/password', 'Admin\DashboardController@passwordreset')->name('admin.password');
    Route::post('/password', 'Admin\DashboardController@changepass')->name('admin.password.update');

    Route::get('/check/movescript', 'Admin\DashboardController@movescript')->name('admin-move-script');
    Route::get('/generate/backup', 'Admin\DashboardController@generate_bkup')->name('admin-generate-backup');
    Route::get('/clear/backup', 'Admin\DashboardController@clear_bkup')->name('admin-clear-backup');


});


Route::prefix('user')->middleware('auth')->group(function() {

    // ... আপনার আগের রাউটগুলো ...
    Route::get('/dashboard', [\App\Http\Controllers\User\ClientController::class, 'index'])->name('user.dashboard');
    Route::get('/support-tickets', [\App\Http\Controllers\User\ClientController::class, 'support'])->name('user.support');
    Route::post('/support-tickets', [\App\Http\Controllers\User\ClientController::class, 'createTicket'])->name('user.support.store');
    
	
	
    // বিস্তারিত ও রিপ্লাই রাউট
    Route::get('/support-tickets/view/{id}', [\App\Http\Controllers\User\ClientController::class, 'viewTicket'])->name('user.support.view');
    Route::post('/support-tickets/reply/{id}', [\App\Http\Controllers\User\ClientController::class, 'replyTicket'])->name('user.support.reply');

    // =================================================================
    // [MISSING] এই নতুন রাউটটি যোগ করুন (এটি ছাড়া অটো চেক কাজ করবে না)
    // =================================================================
    Route::get('/support-tickets/check-new/{id}', [\App\Http\Controllers\User\ClientController::class, 'checkNewMessages'])->name('user.support.check_new');


    // প্রোফাইল ও লগআউট
    Route::get('/profile', [\App\Http\Controllers\User\ClientController::class, 'profile'])->name('user.profile');
    Route::post('/profile/update', [\App\Http\Controllers\User\ClientController::class, 'updateProfile'])->name('user.profile.update');
    Route::get('/logout', [\App\Http\Controllers\User\ClientController::class, 'logout'])->name('user.logout');
});

Route::get('/','Front\FrontendController@index')->name('frontend.index');
// ২. আপনার থিম সিস্টেমের রাউট (এখানে বসান)
Route::get('/themes', function () {
    return view('frontend.themes');
})->name('frontend.themes');

Route::get('/theme/{slug}', [\App\Http\Controllers\Front\FrontendController::class, 'productDetails'])->name('frontend.product.details');
Route::get('/tag/{search}','Front\FrontendController@searchByTag')->name('tag.search');
Route::post('the/genius/ocean/2441139', 'Front\FrontendController@subscription');
Route::get('finalize', 'Front\FrontendController@finalize');


Route::post('/poll-vote','Front\PollVoteController@vote')->name('front.poll.vote');
Route::get('/poll-result/{id}','Front\PollVoteController@result')->name('front.poll.result');
Route::post('/subscribers','Front\SubscriberController@store')->name('front.subscribers.store');
Route::get('/load-more','Front\FrontendController@loadMore')->name('frontend.loadMore');
Route::get('/gallery-view/{id}','Front\GalleryController@view')->name('gallery.view');
Route::get('/all-poll','Front\FrontendController@allPoll')->name('front.allPoll');
Route::get('/all-poll-result','Front\FrontendController@allPollResult')->name('front.allPollResult');
Route::get('/news-search','Front\FrontendController@newsSearch')->name('front.news_search');

Route::get('/profile/{admin}','Front\FrontendController@authorProfile')->name('front.authorProfile');
Route::get('/follower','Front\FrontendController@follower')->name('front.follower');


Route::get('/follower/create/{id}','Front\FollowController@followerCreate')->name('front.followerCreate');
Route::get('/follower/{admin}','Front\FollowController@following')->name('front.following');

Route::get('/contact/refresh_code','Front\FrontendController@refresh_code');
// লগইন পেজ রাউট (GET)
Route::get('/login', [\App\Http\Controllers\Front\RegisterController::class, 'LogReg'])->name('login');

// ইউজার পাসওয়ার্ড রিসেট (OTP সিস্টেম)
Route::group(['prefix' => 'user-reset'], function() {
    Route::get('/request', [UserResetController::class, 'showPhoneForm'])->name('user.forgot.password');
    Route::post('/send-otp', [UserResetController::class, 'sendOtp'])->name('user.forgot.send');
    Route::get('/otp', [UserResetController::class, 'showOtpForm'])->name('user.otp.verify');
    Route::post('/otp-check', [UserResetController::class, 'verifyOtp'])->name('user.otp.check');
    Route::get('/confirm', [UserResetController::class, 'showResetForm'])->name('user.password.reset');
    Route::post('/update', [UserResetController::class, 'updatePassword'])->name('user.password.update');
});





Route::post('/register','Front\RegisterController@register')->name('front.register');
Route::get('/register/verify/{token}', 'Front\RegisterController@token')->name('user.register.token');
// লগইন সাবমিট রাউট (POST)
Route::post('/login', [\App\Http\Controllers\Front\LoginController::class, 'login'])->name('front.login');
Route::get('/logout','Front\LoginController@logout')->name('front.logout');


Route::get('auth/{provider}', 'Front\SocialRegisterController@redirectToProvider')->name('social.provider');
Route::get('auth/{provider}/callback', 'Front\SocialRegisterController@handleProviderCallback');



Route::get('/click/count/{id}','Front\FrontendController@clickCount')->name('frontend.click.count');
Route::get('/dynamic/page/{slug}','Front\FrontendController@page')->name('dynamic.page');
Route::get('/change/language/{id}','Front\FrontendController@language')->name('front.language');
Route::get('/news/date/post-by-date','Front\FrontendController@postByDate')->name('frontend.postByDate');