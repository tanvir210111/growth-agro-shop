<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Baby Fashion BD Admin</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Admin & Captain Crown Specific Styles with Cache Busting -->
    <link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('admin/css/captain-crown.css') }}?v={{ time() }}">
    @stack('styles')
</head>
<body>
    <div class="cc-wrapper">
        <!-- Top Navbar -->
        @include('admin.partials.header')

        <!-- Left Sidebar Accordion Navigation -->
        @include('admin.partials.sidebar')

        <!-- Main Content Area -->
        <main class="cc-main-content">
            <!-- Flash Message Alerts -->
            @if(session('success'))
                <div style="background:#dcfce7; border-left:4px solid #16a34a; color:#15803d; padding:12px 16px; border-radius:4px; margin-bottom:20px; font-weight:600; display:flex; justify-content:space-between; align-items:center;">
                    <span><i class="fa fa-check-circle" style="margin-right:6px;"></i> {{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:inherit; font-size:16px; cursor:pointer;">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div style="background:#fee2e2; border-left:4px solid #dc2626; color:#b91c1c; padding:12px 16px; border-radius:4px; margin-bottom:20px; font-weight:600; display:flex; justify-content:space-between; align-items:center;">
                    <span><i class="fa fa-exclamation-circle" style="margin-right:6px;"></i> {{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:inherit; font-size:16px; cursor:pointer;">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div style="background:#fee2e2; border-left:4px solid #dc2626; color:#b91c1c; padding:12px 16px; border-radius:4px; margin-bottom:20px; font-size:13px;">
                    <strong style="display:block; margin-bottom:4px;"><i class="fa fa-exclamation-triangle"></i> Please correct the following errors:</strong>
                    <ul style="margin-left:20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Admin JS -->
    <script src="{{ asset('admin/js/admin.js') }}"></script>
    <script>
        // Accordion functionality for Exact Captain Crown Sidebar
        document.querySelectorAll('.cc-menu-header').forEach(header => {
            header.addEventListener('click', function () {
                const group = this.closest('.cc-menu-group');
                const wasOpen = group.classList.contains('open');

                // Optional: keep single open or multi open
                group.classList.toggle('open');
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
