@php
    $currentRoute = Route::currentRouteName();
    $currentModule = request()->route('module');
    $currentPage = request()->route('page');
@endphp

<aside class="cc-sidebar">
    <ul class="cc-nav-list">
        <!-- 1. Dashboard -->
        <li class="cc-nav-item dashboard-item {{ $currentRoute === 'admin.dashboard' ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}">
                <i class="fa fa-home" style="color:#0284c7;"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- 2. Shop -->
        <li class="cc-menu-group {{ $currentModule === 'shop' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-th-large"></i>
                    <span>Shop</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentModule === 'shop' && $currentPage === 'manage' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'shop', 'page' => 'manage']) }}"><i class="fa fa-list-alt"></i> Manage</a>
                </li>
                <li class="{{ $currentModule === 'shop' && $currentPage === 'expenses' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'shop', 'page' => 'expenses']) }}"><i class="fa fa-money-bill"></i> Expenses</a>
                </li>
                <li class="{{ $currentModule === 'shop' && $currentPage === 'expense-purposes' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'shop', 'page' => 'expense-purposes']) }}"><i class="fa fa-arrow-circle-right"></i> Expense Purposes</a>
                </li>
                <li class="{{ $currentModule === 'shop' && $currentPage === 'ad-daily-cost' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'shop', 'page' => 'ad-daily-cost']) }}"><i class="fa fa-dollar-sign"></i> Ad & Daily Cost</a>
                </li>
            </ul>
        </li>

        <!-- 3. Orders Manage -->
        <li class="cc-menu-group {{ str_contains($currentRoute, 'orders') || $currentModule === 'orders-manage' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-list"></i>
                    <span>Orders Manage</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentRoute === 'admin.orders.index' ? 'active' : '' }}">
                    <a href="{{ route('admin.orders.index') }}"><i class="fa fa-arrow-circle-right"></i> Online Manage</a>
                </li>
                <li class="{{ $currentModule === 'orders-manage' && $currentPage === 'processing-report' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'orders-manage', 'page' => 'processing-report']) }}"><i class="fa fa-user-check"></i> Order processing report</a>
                </li>
            </ul>
        </li>

        <!-- 4. Sale Manage -->
        <li class="cc-menu-group {{ $currentModule === 'sale-manage' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-folder"></i>
                    <span>Sale Manage</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentModule === 'sale-manage' && $currentPage === 'shop-retail' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'sale-manage', 'page' => 'shop-retail']) }}"><i class="fa fa-arrow-circle-right"></i> Shop Retail</a>
                </li>
                <li class="{{ $currentModule === 'sale-manage' && $currentPage === 'company-sale' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'sale-manage', 'page' => 'company-sale']) }}"><i class="fa fa-arrow-circle-right"></i> Company Sale</a>
                </li>
                <li class="{{ $currentModule === 'sale-manage' && $currentPage === 'companies' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'sale-manage', 'page' => 'companies']) }}"><i class="fa fa-arrow-circle-right"></i> Companies</a>
                </li>
                <li class="{{ $currentModule === 'sale-manage' && $currentPage === 'exchange' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'sale-manage', 'page' => 'exchange']) }}"><i class="fa fa-exchange-alt"></i> Exchange</a>
                </li>
            </ul>
        </li>

        <!-- 5. Accounts -->
        <li class="cc-menu-group {{ $currentModule === 'accounts' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-money-bill-wave"></i>
                    <span>Accounts</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentModule === 'accounts' && $currentPage === 'credit' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'accounts', 'page' => 'credit']) }}"><i class="fa fa-arrow-circle-right"></i> Credit</a>
                </li>
                <li class="{{ $currentModule === 'accounts' && $currentPage === 'debit' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'accounts', 'page' => 'debit']) }}"><i class="fa fa-arrow-circle-right"></i> Debit</a>
                </li>
                <li class="{{ $currentModule === 'accounts' && $currentPage === 'due' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'accounts', 'page' => 'due']) }}"><i class="fa fa-arrow-circle-right"></i> Due</a>
                </li>
                <li class="{{ $currentModule === 'accounts' && $currentPage === 'mange-balance' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'accounts', 'page' => 'mange-balance']) }}"><i class="fa fa-arrow-circle-right"></i> Mange Balance</a>
                </li>
                <li class="{{ $currentModule === 'accounts' && $currentPage === 'fund-transfer' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'accounts', 'page' => 'fund-transfer']) }}"><i class="fa fa-arrow-circle-right"></i> Fund Transfer</a>
                </li>
            </ul>
        </li>

        <!-- 6. Products Manage -->
        <li class="cc-menu-group {{ str_contains($currentRoute, 'products') || $currentModule === 'products-manage' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-boxes"></i>
                    <span>Products Manage</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentRoute === 'admin.products.index' ? 'active' : '' }}">
                    <a href="{{ route('admin.products.index') }}"><i class="fa fa-arrow-circle-right"></i> All Products</a>
                </li>
                <li class="{{ $currentRoute === 'admin.products.create' ? 'active' : '' }}">
                    <a href="{{ route('admin.products.create') }}"><i class="fa fa-arrow-circle-right"></i> Add Product</a>
                </li>
                <li class="{{ $currentModule === 'products-manage' && $currentPage === 'attribute' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'products-manage', 'page' => 'attribute']) }}"><i class="fa fa-arrow-circle-right"></i> Attribute</a>
                </li>
                <li class="{{ $currentModule === 'products-manage' && $currentPage === 'variant' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'products-manage', 'page' => 'variant']) }}"><i class="far fa-circle"></i> Variant</a>
                </li>
                <li class="{{ $currentModule === 'products-manage' && $currentPage === 'products-purchase' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'products-manage', 'page' => 'products-purchase']) }}"><i class="fa fa-arrow-circle-right"></i> Products Purchase</a>
                </li>
                <li class="{{ $currentModule === 'products-manage' && $currentPage === 'damage-products' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'products-manage', 'page' => 'damage-products']) }}"><i class="fa fa-arrow-circle-right"></i> Damage Products</a>
                </li>
                <li class="{{ $currentModule === 'products-manage' && $currentPage === 'transfer-products' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'products-manage', 'page' => 'transfer-products']) }}"><i class="fa fa-arrow-circle-right"></i> Transfer Products</a>
                </li>
                <li class="{{ $currentModule === 'products-manage' && $currentPage === 'product-review' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'products-manage', 'page' => 'product-review']) }}"><i class="fa fa-arrow-circle-right"></i> Product Review</a>
                </li>
                <li class="{{ $currentModule === 'products-manage' && $currentPage === 'recently-deleted' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'products-manage', 'page' => 'recently-deleted']) }}"><i class="fa fa-arrow-circle-right"></i> Recently deleted</a>
                </li>
            </ul>
        </li>

        <!-- 7. Product Supplier -->
        <li class="cc-menu-group {{ $currentModule === 'product-supplier' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-id-card"></i>
                    <span>Product Supplier</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentModule === 'product-supplier' && $currentPage === 'add' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'product-supplier', 'page' => 'add']) }}"><i class="fa fa-plus"></i> Add</a>
                </li>
                <li class="{{ $currentModule === 'product-supplier' && $currentPage === 'manage' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'product-supplier', 'page' => 'manage']) }}"><i class="fa fa-arrow-circle-right"></i> Manage</a>
                </li>
            </ul>
        </li>

        <!-- 8. Campaign -->
        <li class="cc-menu-group {{ $currentModule === 'campaign' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-folder"></i>
                    <span>Campaign</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentModule === 'campaign' && $currentPage === 'add' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'campaign', 'page' => 'add']) }}"><i class="far fa-circle"></i> Add</a>
                </li>
                <li class="{{ $currentModule === 'campaign' && $currentPage === 'manage' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'campaign', 'page' => 'manage']) }}"><i class="far fa-circle"></i> Manage</a>
                </li>
                <li class="{{ $currentModule === 'campaign' && $currentPage === 'coupon' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'campaign', 'page' => 'coupon']) }}"><i class="fa fa-arrow-circle-right"></i> Coupon</a>
                </li>
                <li class="{{ $currentModule === 'campaign' && $currentPage === 'sms-campaign' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'campaign', 'page' => 'sms-campaign']) }}"><i class="fa fa-comment-alt"></i> SMS Campaign</a>
                </li>
                <li class="{{ $currentModule === 'campaign' && $currentPage === 'landing-page' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'campaign', 'page' => 'landing-page']) }}"><i class="fa fa-laptop"></i> Landing Page</a>
                </li>
            </ul>
        </li>

        <!-- 9. Landing Page -->
        <li class="cc-menu-group {{ $currentModule === 'landing-page' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-folder"></i>
                    <span>Landing Page</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentModule === 'landing-page' && $currentPage === 'manage' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'landing-page', 'page' => 'manage']) }}"><i class="far fa-circle"></i> Manage</a>
                </li>
            </ul>
        </li>

        <!-- 10. Category -->
        <li class="cc-menu-group {{ str_contains($currentRoute, 'categories') || $currentModule === 'category' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-folder"></i>
                    <span>Category</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentRoute === 'admin.categories.index' ? 'active' : '' }}">
                    <a href="{{ route('admin.categories.index') }}"><i class="far fa-circle"></i> category</a>
                </li>
                <li class="{{ $currentModule === 'category' && $currentPage === 'sub-category' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'category', 'page' => 'sub-category']) }}"><i class="far fa-circle"></i> sub category</a>
                </li>
                <li class="{{ $currentModule === 'category' && $currentPage === 'sub-sub-category' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'category', 'page' => 'sub-sub-category']) }}"><i class="far fa-circle"></i> sub sub category</a>
                </li>
            </ul>
        </li>

        <!-- 11. Brand -->
        <li class="cc-menu-group {{ $currentModule === 'brand' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-folder"></i>
                    <span>Brand</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentModule === 'brand' && $currentPage === 'add' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'brand', 'page' => 'add']) }}"><i class="fa fa-plus"></i> Add</a>
                </li>
                <li class="{{ $currentModule === 'brand' && $currentPage === 'manage' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'brand', 'page' => 'manage']) }}"><i class="fa fa-arrow-circle-right"></i> Manage</a>
                </li>
            </ul>
        </li>

        <!-- 12. Slider -->
        <li class="cc-menu-group {{ str_contains($currentRoute, 'sliders') || $currentModule === 'slider' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-sliders-h"></i>
                    <span>Slider</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentRoute === 'admin.sliders.index' ? 'active' : '' }}">
                    <a href="{{ route('admin.sliders.index') }}"><i class="fa fa-bullhorn"></i> Advertise Banner</a>
                </li>
                <li class="{{ $currentModule === 'slider' && $currentPage === 'landing-sliders' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'slider', 'page' => 'landing-sliders']) }}"><i class="fa fa-arrow-circle-right"></i> Landing Sliders</a>
                </li>
            </ul>
        </li>

        <!-- 13. Admin -->
        <li class="cc-menu-group {{ str_contains($currentRoute, 'profile') || $currentModule === 'admin' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-user-secret"></i>
                    <span>Admin</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentModule === 'admin' && $currentPage === 'add' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'admin', 'page' => 'add']) }}"><i class="fa fa-plus"></i> Add</a>
                </li>
                <li class="{{ $currentRoute === 'admin.profile' ? 'active' : '' }}">
                    <a href="{{ route('admin.profile') }}"><i class="fa fa-arrow-circle-right"></i> Manage</a>
                </li>
            </ul>
        </li>

        <!-- 14. Customer & Subscriber -->
        <li class="cc-menu-group {{ $currentModule === 'customer-subscriber' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-users"></i>
                    <span>Customer & Subscriber</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentModule === 'customer-subscriber' && $currentPage === 'customers' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'customer-subscriber', 'page' => 'customers']) }}"><i class="fa fa-user"></i> Customers</a>
                </li>
                <li class="{{ $currentModule === 'customer-subscriber' && $currentPage === 'users' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'customer-subscriber', 'page' => 'users']) }}"><i class="fa fa-user"></i> Users</a>
                </li>
                <li class="{{ $currentModule === 'customer-subscriber' && $currentPage === 'wholeseller' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'customer-subscriber', 'page' => 'wholeseller']) }}"><i class="fa fa-user"></i> WholeSeller</a>
                </li>
            </ul>
        </li>

        <!-- 15. Report -->
        <li class="cc-menu-group {{ $currentModule === 'report' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-file-alt"></i>
                    <span>Report</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li><a href="{{ route('admin.module.page', ['module' => 'report', 'page' => 'profit-loss-report']) }}"><i class="fa fa-arrow-circle-right"></i> Profit/Loss Report</a></li>
                <li><a href="{{ route('admin.module.page', ['module' => 'report', 'page' => 'product-stock-and-profit']) }}"><i class="fa fa-arrow-circle-right"></i> Product Stock and Profit</a></li>
                <li><a href="{{ route('admin.module.page', ['module' => 'report', 'page' => 'product-stock']) }}"><i class="fa fa-arrow-circle-right"></i> Product Stock</a></li>
                <li><a href="{{ route('admin.module.page', ['module' => 'report', 'page' => 'stock-management-activities']) }}"><i class="fa fa-boxes"></i> Stock Management Activities</a></li>
                <li><a href="{{ route('admin.module.page', ['module' => 'report', 'page' => 'order-report']) }}"><i class="fa fa-arrow-circle-right"></i> Order Report</a></li>
                <li><a href="{{ route('admin.module.page', ['module' => 'report', 'page' => 'office-sale-report']) }}"><i class="fa fa-arrow-circle-right"></i> Office Sale Report</a></li>
                <li><a href="{{ route('admin.module.page', ['module' => 'report', 'page' => 'order-profit']) }}"><i class="fa fa-arrow-circle-right"></i> Order Profit</a></li>
                <li><a href="{{ route('admin.module.page', ['module' => 'report', 'page' => 'sale-profit']) }}"><i class="fa fa-arrow-circle-right"></i> Sale Profit</a></li>
                <li><a href="{{ route('admin.module.page', ['module' => 'report', 'page' => 'purchase-report']) }}"><i class="fa fa-arrow-circle-right"></i> Purchase Report</a></li>
                <li><a href="{{ route('admin.module.page', ['module' => 'report', 'page' => 'supplier-report']) }}"><i class="fa fa-arrow-circle-right"></i> Supplier Report</a></li>
                <li><a href="{{ route('admin.module.page', ['module' => 'report', 'page' => 'account-report']) }}"><i class="fa fa-arrow-circle-right"></i> Account Report</a></li>
                <li><a href="{{ route('admin.module.page', ['module' => 'report', 'page' => 'stock-report']) }}"><i class="fa fa-arrow-circle-right"></i> Stock Report</a></li>
                <li><a href="{{ route('admin.module.page', ['module' => 'report', 'page' => 'customer-order-history']) }}"><i class="fa fa-arrow-circle-right"></i> Customer Order History</a></li>
                <li><a href="{{ route('admin.module.page', ['module' => 'report', 'page' => 'delivery-return-report']) }}"><i class="fa fa-arrow-circle-right"></i> Delivery Return Report</a></li>
                <li><a href="{{ route('admin.module.page', ['module' => 'report', 'page' => 'account-purpose']) }}"><i class="fa fa-folder"></i> Account Purpose</a></li>
            </ul>
        </li>

        <!-- 16. Pages -->
        <li class="cc-menu-group {{ $currentModule === 'pages' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-file"></i>
                    <span>Pages</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentModule === 'pages' && $currentPage === 'add' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'pages', 'page' => 'add']) }}"><i class="fa fa-plus"></i> Add</a>
                </li>
                <li class="{{ $currentModule === 'pages' && $currentPage === 'manage' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'pages', 'page' => 'manage']) }}"><i class="fa fa-arrow-circle-right"></i> Manage</a>
                </li>
            </ul>
        </li>

        <!-- 17. Setting -->
        <li class="cc-menu-group {{ str_contains($currentRoute, 'settings') || $currentModule === 'setting' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-cog"></i>
                    <span>Setting</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentRoute === 'admin.settings.index' ? 'active' : '' }}">
                    <a href="{{ route('admin.settings.index') }}"><i class="fa fa-cogs"></i> General Setting</a>
                </li>
                <li class="{{ $currentModule === 'setting' && $currentPage === 'footer-setting' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'setting', 'page' => 'footer-setting']) }}"><i class="fa fa-cogs"></i> Footer Setting</a>
                </li>
                <li class="{{ $currentModule === 'setting' && $currentPage === 'color-setting' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'setting', 'page' => 'color-setting']) }}"><i class="fa fa-tint"></i> Color Setting</a>
                </li>
                <li class="{{ $currentModule === 'setting' && $currentPage === 'city' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'setting', 'page' => 'city']) }}"><i class="fa fa-plus"></i> City</a>
                </li>
                <li class="{{ $currentModule === 'setting' && $currentPage === 'sub-city' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'setting', 'page' => 'sub-city']) }}"><i class="fa fa-plus"></i> Sub City</a>
                </li>
                <li class="{{ $currentModule === 'setting' && $currentPage === 'courier' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'setting', 'page' => 'courier']) }}"><i class="fa fa-arrow-circle-right"></i> Courier</a>
                </li>
                <li class="{{ $currentModule === 'setting' && $currentPage === 'courier-api-setup' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'setting', 'page' => 'courier-api-setup']) }}"><i class="fa fa-arrow-circle-right"></i> Courier API Setup</a>
                </li>
                <li class="{{ $currentModule === 'setting' && $currentPage === 'comment' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'setting', 'page' => 'comment']) }}"><i class="fa fa-arrow-circle-right"></i> Comment</a>
                </li>
                <li>
                    <a href="{{ route('admin.settings.index') }}"><i class="fa fa-plus"></i> Delivery Charge</a>
                </li>
                <li class="{{ $currentModule === 'setting' && $currentPage === 'header-marquee-text' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'setting', 'page' => 'header-marquee-text']) }}"><i class="fa fa-arrow-circle-right"></i> Header marquee text</a>
                </li>
            </ul>
        </li>

        <!-- 18. HR -->
        <li class="cc-menu-group {{ $currentModule === 'hr' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-address-book"></i>
                    <span>HR</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentModule === 'hr' && $currentPage === 'manage-employees' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'hr', 'page' => 'manage-employees']) }}"><i class="fa fa-users-cog"></i> Manage Employees</a>
                </li>
                <li class="{{ $currentModule === 'hr' && $currentPage === 'salary-breakdown-list' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'hr', 'page' => 'salary-breakdown-list']) }}"><i class="fa fa-link"></i> Salary Breakdown List</a>
                </li>
                <li class="{{ $currentModule === 'hr' && $currentPage === 'salary-deduction-list' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'hr', 'page' => 'salary-deduction-list']) }}"><i class="fa fa-cut"></i> Salary Deduction List</a>
                </li>
            </ul>
        </li>

        <!-- 19. Company Management -->
        <li class="cc-menu-group {{ $currentModule === 'company-management' ? 'open' : '' }}">
            <div class="cc-menu-header">
                <div class="cc-menu-title">
                    <i class="fa fa-briefcase"></i>
                    <span>Company Management</span>
                </div>
                <i class="fa fa-chevron-down cc-menu-arrow"></i>
            </div>
            <ul class="cc-submenu">
                <li class="{{ $currentModule === 'company-management' && $currentPage === 'stock-order-analysis' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'company-management', 'page' => 'stock-order-analysis']) }}"><i class="fa fa-chart-bar"></i> Stock & Order Analysis</a>
                </li>
                <li class="{{ $currentModule === 'company-management' && $currentPage === 'manage-loan' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'company-management', 'page' => 'manage-loan']) }}"><i class="fa fa-money-bill"></i> Manage Loan</a>
                </li>
                <li class="{{ $currentModule === 'company-management' && $currentPage === 'manage-investment' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'company-management', 'page' => 'manage-investment']) }}"><i class="fa fa-dollar-sign"></i> Manage Investment</a>
                </li>
                <li class="{{ $currentModule === 'company-management' && $currentPage === 'manage-assets' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'company-management', 'page' => 'manage-assets']) }}"><i class="fa fa-arrow-circle-right"></i> Manage Assets</a>
                </li>
                <li class="{{ $currentModule === 'company-management' && $currentPage === 'directors' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'company-management', 'page' => 'directors']) }}"><i class="fa fa-user-secret"></i> Directors</a>
                </li>
                <li class="{{ $currentModule === 'company-management' && $currentPage === 'bill-statements' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'company-management', 'page' => 'bill-statements']) }}"><i class="fa fa-money-bill"></i> Bill Statements</a>
                </li>
                <li class="{{ $currentModule === 'company-management' && $currentPage === 'credit-statements' ? 'active' : '' }}">
                    <a href="{{ route('admin.module.page', ['module' => 'company-management', 'page' => 'credit-statements']) }}"><i class="fa fa-money-bill"></i> Credit Statements</a>
                </li>
            </ul>
        </li>
    </ul>
</aside>
