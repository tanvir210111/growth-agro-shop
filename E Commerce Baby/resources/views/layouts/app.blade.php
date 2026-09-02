<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', \App\Models\Setting::get('site_title', 'Growth Agro | Universal E-Commerce & Online Store'))</title>
    <meta name="description" content="@yield('meta_description', \App\Models\Setting::get('meta_description', 'Discover quality products at the best prices with fast nationwide Cash on Delivery.'))">

    <!-- Favicon -->
    @php
        $siteFavicon = \App\Models\Setting::get('site_favicon', '/favicon.ico');
    @endphp
    <link rel="icon" type="image/png" href="{{ \Illuminate\Support\Str::startsWith($siteFavicon, ['http://', 'https://', '/']) ? $siteFavicon : asset($siteFavicon) }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Core E-Commerce Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/baby-fashion.css') }}?v={{ time() }}">
    
    <!-- Centralized Meta/Facebook Pixel -->
    @include('partials.meta-pixel')

    @stack('styles')
</head>
<body>
    <!-- Main Navigation Header -->
    @include('partials.header')

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="container" style="margin-top: 1rem;">
            <div style="background: #ECFDF5; border-left: 4px solid #10B981; color: #065F46; padding: 0.85rem 1.25rem; border-radius: 8px; font-weight: 600; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <span>✓ {{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.parentElement.remove()" style="background:none; border:none; color:inherit; font-size:1.1rem; cursor:pointer;">&times;</button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container" style="margin-top: 1rem;">
            <div style="background: #FEF2F2; border-left: 4px solid #EF4444; color: #991B1B; padding: 0.85rem 1.25rem; border-radius: 8px; font-weight: 600; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <span>⚠️ {{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.parentElement.remove()" style="background:none; border:none; color:inherit; font-size:1.1rem; cursor:pointer;">&times;</button>
            </div>
        </div>
    @endif

    <!-- Main Page Content -->
    <main class="site-main">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('partials.footer')

    <!-- Slide-out Cart Drawer -->
    @include('partials.cart-drawer')

    <!-- Quick View Modal -->
    @include('partials.quick-view-modal')

    <!-- Floating WhatsApp Widget -->
    @include('partials.whatsapp-widget')

    <!-- Toast Notification Container -->
    <div id="toastNotification" class="toast-notification" style="display: none;"></div>

    <!-- Global Unified Tracking Helper -->
    <script src="{{ asset('js/growth-agro-tracking.js') }}?v={{ time() }}"></script>

    <!-- Global Frontend JavaScript -->
    <script src="{{ asset('js/baby-fashion.js') }}?v={{ time() }}"></script>
    
    @stack('scripts')
</body>
</html>