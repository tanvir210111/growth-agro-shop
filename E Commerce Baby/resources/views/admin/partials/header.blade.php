<header class="cc-topbar">
    <div style="display:flex; align-items:center; gap:16px;">
        <a href="{{ route('admin.dashboard') }}" class="cc-logo-area" style="text-decoration:none;">
            <img src="{{ asset('images/logo.png') }}" alt="Baby Fashion BD Logo">
            <span>admin</span>
        </a>
        <button type="button" class="cc-hamburger-btn" id="sidebarToggleBtn" aria-label="Toggle Sidebar">
            <i class="fa fa-bars"></i>
        </button>
    </div>

    <div style="display:flex; align-items:center; gap:16px;">
        <!-- Live Store Link -->
        <a href="{{ route('home') }}" target="_blank" style="font-size:12px; font-weight:600; color:#0284c7; text-decoration:none; display:flex; align-items:center; gap:5px;">
            <i class="fa fa-external-link-alt"></i> Live Store
        </a>

        <!-- Admin Profile -->
        <a href="{{ route('admin.profile') }}" class="cc-user-profile-btn">
            <img src="{{ Auth::guard('admin')->user()->avatar ? asset(Auth::guard('admin')->user()->avatar) : asset('images/logo.png') }}" alt="Admin Avatar">
            <span>admin</span>
        </a>

        <!-- Logout Form -->
        <form action="{{ route('admin.logout') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" style="background:none; border:none; color:#ef4444; font-size:14px; cursor:pointer; padding:6px 10px;" title="Logout">
                <i class="fa fa-sign-out-alt"></i>
            </button>
        </form>
    </div>
</header>
