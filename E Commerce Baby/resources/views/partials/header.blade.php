<header class="main-header">
    <div class="container">
        <div class="header-inner">
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Open Menu">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>

            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="brand-logo">
                <img src="{{ asset('images/logo.png') }}?v=2" alt="Baby Fashion BD" style="height: 68px; width: auto; max-width: 185px; object-fit: contain;">
            </a>

            <!-- Search Bar with Live Predictive Dropdown -->
            <div class="header-search">
                <form action="{{ route('shop') }}" method="GET" class="search-form">
                    <input type="text" name="q" class="search-input live-search-input" placeholder="Search baby sets, rompers, t-shirts, dresses..." autocomplete="off">
                    <button type="submit" class="search-btn" aria-label="Search">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path d="M21 21l-4.35-4.35"></path></svg>
                    </button>
                </form>
                <div class="search-results-dropdown" id="searchResultsDropdown"></div>
            </div>

            <!-- Header Right Actions -->
            <div class="header-actions">
                <!-- Helpline Link -->
                <a href="tel:+8801560016740" class="header-action-btn" title="Call Customer Support">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"></path></svg>
                    <span style="font-size: 0.85rem; font-weight:700;">01560-016740</span>
                </a>

                <!-- Cart Drawer Toggle -->
                <button type="button" class="header-action-btn cart-toggle-btn cart-toggle-trigger">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 01-8 0"></path></svg>
                    <span style="font-weight: 700;">Bag</span>
                    <span class="header-badge cart-badge-count">0</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation Menu Bar -->
    <nav class="nav-bar">
        <div class="container">
            <ul class="nav-links">
                <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
                    <a href="{{ route('home') }}">Home</a>
                </li>
                <li class="nav-item {{ request()->is('collections/all-collection') ? 'active' : '' }}">
                    <a href="{{ route('collection.show', 'all-collection') }}">All Collection</a>
                </li>
                <li class="nav-item {{ request()->is('collections/baby-boys') ? 'active' : '' }}">
                    <a href="{{ route('collection.show', 'baby-boys') }}">Baby Boy</a>
                </li>
                <li class="nav-item {{ request()->is('collections/baby-girl') ? 'active' : '' }}">
                    <a href="{{ route('collection.show', 'baby-girl') }}">Baby Girl</a>
                </li>
                <li class="nav-item {{ request()->is('collections/new-arrival') ? 'active' : '' }}">
                    <a href="{{ route('collection.show', 'new-arrival') }}">New Arrival</a>
                </li>
                <li class="nav-item {{ request()->is('collections/maggie-t-shirt-sets') ? 'active' : '' }}">
                    <a href="{{ route('collection.show', 'maggie-t-shirt-sets') }}">Maggie T-Shirt Sets</a>
                </li>
                <li class="nav-item {{ request()->is('collections/winter-collection') ? 'active' : '' }}">
                    <a href="{{ route('collection.show', 'winter-collection') }}">Winter Collection</a>
                </li>
                <li class="nav-item highlight {{ request()->is('collections/clearance-sale') ? 'active' : '' }}">
                    <a href="{{ route('collection.show', 'clearance-sale') }}">🔥 Clearance Sale</a>
                </li>
                <li class="nav-item {{ request()->is('collections/backpack-toys') ? 'active' : '' }}">
                    <a href="{{ route('collection.show', 'backpack-toys') }}">Backpack & Toys</a>
                </li>
            </ul>
        </div>
    </nav>
</header>
