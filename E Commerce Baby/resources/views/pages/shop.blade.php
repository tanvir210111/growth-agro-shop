@extends('layouts.app')

@section('title', ($currentCollection['title'] ?? 'Shop All') . ' | ' . \App\Models\Setting::get('site_title', 'Growth Agro'))
@section('meta_description', $currentCollection['description'] ?? 'Explore our catalog of quality products at ' . \App\Models\Setting::get('site_title', 'Growth Agro'))

@section('content')
<div class="container" style="padding: 2rem 1rem 4rem;">
    <!-- Breadcrumb -->
    <div style="font-size: 0.85rem; color: var(--color-text-light); margin-bottom: 1.25rem;">
        <a href="{{ route('home') }}">Home</a> &rsaquo;
        @if(($currentCollection['handle'] ?? '') !== 'all-collection')
            <a href="{{ route('categories') }}">Categories</a> &rsaquo;
            @if(!empty($currentCollection['ancestors']))
                @foreach($currentCollection['ancestors'] as $ancestor)
                    <a href="{{ route('collection.show', $ancestor['handle']) }}">{{ $ancestor['title'] }}</a> &rsaquo;
                @endforeach
            @elseif(!empty($currentCollection['parent_handle']))
                <a href="{{ route('collection.show', $currentCollection['parent_handle']) }}">{{ $currentCollection['parent_title'] }}</a> &rsaquo;
            @endif
        @endif
        <span>{{ $currentCollection['title'] ?? 'Shop' }}</span>
    </div>

    <!-- Category Header Area -->
    <div style="margin-bottom: 2rem; background: #fff; padding: 2rem; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
        <h1 style="font-family: var(--font-heading); font-size: 2rem; color: var(--color-text-main); margin-bottom: 0.4rem;">
            {{ $currentCollection['title'] ?? 'All Collection' }}
        </h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem; margin: 0; max-width: 750px; line-height: 1.6;">
            {{ $currentCollection['description'] ?? 'Browse through our complete selection of quality curated products.' }}
        </p>
    </div>

    <!-- Layout Grid: Sidebar + Product Catalog -->
    <div class="catalog-layout-grid">
        <!-- Filter Drawer Overlay (Mobile) -->
        <div class="filter-drawer-overlay" id="filterDrawerOverlay"></div>

        <!-- Sidebar Filter (Desktop sidebar, mobile slide-out drawer) -->
        <aside class="catalog-sidebar" id="catalogSidebar">
            <div class="sidebar-header">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                    <h4 style="font-size: 1.05rem; font-weight: 700; margin: 0; color: #0f172a;">Filter By</h4>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <a href="{{ route('shop') }}" style="font-size: 0.8rem; color: #ea580c; font-weight: 700;">Clear All</a>
                    <button type="button" class="filter-drawer-close" id="filterDrawerClose" aria-label="Close Filter Drawer">✕</button>
                </div>
            </div>

            <div class="sidebar-body">
                <!-- Category Filter List -->
                <div style="margin-bottom: 1.5rem;">
                    <h5 style="font-size: 0.88rem; font-weight: 700; margin-bottom: 0.75rem; color: #334155; text-transform: uppercase; letter-spacing: 0.03em;">Categories</h5>
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.88rem; display: flex; flex-direction: column; gap: 0.5rem;">
                        <li>
                            <a href="{{ route('collection.show', 'all-collection') }}" style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; color: {{ ($currentCollection['handle'] ?? '') === 'all-collection' ? '#ea580c' : '#475569' }}; font-weight: {{ ($currentCollection['handle'] ?? '') === 'all-collection' ? '700' : '500' }};">
                                <span>All Products</span>
                            </a>
                        </li>
                        @foreach($collections as $col)
                            @if($col['handle'] !== 'all-collection')
                                <li>
                                    <a href="{{ route('collection.show', $col['handle']) }}" style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; color: {{ ($currentCollection['handle'] ?? '') === $col['handle'] ? '#ea580c' : '#475569' }}; font-weight: {{ ($currentCollection['handle'] ?? '') === $col['handle'] ? '700' : '600' }};">
                                        <span>{{ $col['title'] }}</span>
                                        <span style="font-size: 0.75rem; color: #94a3b8; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $col['item_count'] ?? 0 }}</span>
                                    </a>
                                    @if(!empty($col['children']) && count($col['children']) > 0)
                                        <ul style="list-style: none; padding: 0; margin: 4px 0 6px; display: flex; flex-direction: column; gap: 3px;">
                                            @foreach($col['children'] as $child)
                                                @include('partials.category-sidebar-item', ['category' => $child, 'depth' => 1, 'currentCollection' => $currentCollection])
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                <!-- Price Range Filter Form -->
                <div>
                    <h5 style="font-size: 0.88rem; font-weight: 700; margin-bottom: 0.75rem; color: #334155; text-transform: uppercase; letter-spacing: 0.03em;">Price Range</h5>
                    <form method="GET" action="{{ url()->current() }}">
                        <input type="hidden" name="sort" value="{{ $sort ?? 'newest' }}">
                        <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min ৳" style="width: 50%; padding: 0.6rem 0.75rem; border: 1px solid var(--color-border); border-radius: var(--radius-xs); font-size: 0.88rem;">
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max ৳" style="width: 50%; padding: 0.6rem 0.75rem; border: 1px solid var(--color-border); border-radius: var(--radius-xs); font-size: 0.88rem;">
                        </div>
                        <button type="submit" class="btn-primary" style="width: 100%; padding: 0.65rem; font-size: 0.88rem; justify-content: center;">
                            Apply Filter
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Product Grid Area -->
        <main class="catalog-main">
            <!-- Sort & Count Bar / Mobile Filter Trigger Bar -->
            <div class="catalog-toolbar">
                <!-- Mobile Filter Trigger Button -->
                <button type="button" class="mobile-filter-trigger-btn" id="openFilterDrawerBtn" aria-label="Open Filter Menu">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                    <span>Filters</span>
                    @if(request('min_price') || request('max_price'))
                        <span class="active-filter-badge">1</span>
                    @endif
                </button>

                <div class="catalog-product-count">
                    Showing <strong style="color: #ea580c;">{{ count($products) }}</strong> products
                </div>

                <form method="GET" action="{{ url()->current() }}" class="catalog-sort-form">
                    @if(request('min_price')) <input type="hidden" name="min_price" value="{{ request('min_price') }}"> @endif
                    @if(request('max_price')) <input type="hidden" name="max_price" value="{{ request('max_price') }}"> @endif
                    <label for="sortSelect" class="sort-label">Sort:</label>
                    <select id="sortSelect" name="sort" onchange="this.form.submit()" class="sort-select">
                        <option value="newest" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="price_asc" {{ ($sort ?? '') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ ($sort ?? '') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="rating" {{ ($sort ?? '') === 'rating' ? 'selected' : '' }}>Top Rated</option>
                    </select>
                </form>
            </div>

            <!-- Products Grid -->
            @if(count($products) > 0)
                <div class="products-grid">
                    @foreach($products as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 4rem 1rem; background: #ffffff; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📦</div>
                    <h3 style="color: #1e293b; margin-bottom: 0.4rem;">No products found</h3>
                    <p style="color: #64748b; margin: 0 0 1.5rem;">Try choosing another category or clearing your filters.</p>
                    <a href="{{ route('shop') }}" class="btn-primary" style="padding: 0.75rem 2rem;">View All Products</a>
                </div>
            @endif
        </main>
    </div>
</div>

@push('scripts')
<script>
    if (window.GrowthAgroTracking) {
        @if(!empty($currentCollection['handle']))
            window.GrowthAgroTracking.track('category_view', {
                entity_type: 'category',
                entity_id: '{{ $currentCollection["handle"] }}',
                properties: { title: '{{ $currentCollection["title"] }}', items_count: {{ count($products) }} }
            });
        @endif

        @if(request()->filled('search') || request()->filled('q'))
            window.GrowthAgroTracking.track('search', {
                entity_type: 'search',
                entity_id: '{{ request("search") ?: request("q") }}',
                properties: {
                    query: '{{ request("search") ?: request("q") }}',
                    results_count: {{ count($products) }}
                }
            });
        @endif
    }
</script>
@endpush
@endsection
