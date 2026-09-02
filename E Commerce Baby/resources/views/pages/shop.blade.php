@extends('layouts.app')

@section('title', ($currentCollection['title'] ?? 'Shop All') . ' | ' . \App\Models\Setting::get('site_title', 'Growth Agro'))

@section('content')
<div class="container" style="padding: 2rem 1rem 4rem;">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 2rem;">
        <div style="font-size: 0.85rem; color: var(--color-text-light); margin-bottom: 0.5rem;">
            <a href="{{ route('home') }}">Home</a> &rsaquo; <span>{{ $currentCollection['title'] ?? 'Shop' }}</span>
        </div>
        <h1 style="font-family: var(--font-heading); font-size: 2.2rem; color: var(--color-text-main); margin-bottom: 0.4rem;">
            {{ $currentCollection['title'] ?? 'All Collection' }}
        </h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem; max-width: 650px;">
            {{ $currentCollection['description'] ?? 'Browse through our complete selection of quality curated products.' }}
        </p>
    </div>

    <!-- Category Filters -->
    <div style="display: flex; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 2rem;">
        <a href="{{ route('collection.show', 'all-collection') }}" class="size-pill-lg {{ ($currentCollection['handle'] ?? '') === 'all-collection' ? 'selected' : '' }}">
            All Collection
        </a>
        @foreach($collections as $col)
            @if($col['handle'] !== 'all-collection')
                <a href="{{ route('collection.show', $col['handle']) }}" class="size-pill-lg {{ ($currentCollection['handle'] ?? '') === $col['handle'] ? 'selected' : '' }}">
                    {{ $col['title'] }}
                </a>
            @endif
        @endforeach
    </div>

    <!-- Filter & Sort Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; background: #ffffff; padding: 1rem 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--color-border); margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div style="font-size: 0.9rem; font-weight: 600; color: var(--color-text-muted);">
            Showing <strong style="color: var(--color-primary);">{{ count($products) }}</strong> items
        </div>

        <form method="GET" action="{{ url()->current() }}" style="display: flex; align-items: center; gap: 0.8rem;">
            <label for="sortSelect" style="font-size: 0.88rem; font-weight: 600;">Sort By:</label>
            <select id="sortSelect" name="sort" onchange="this.form.submit()" style="padding: 0.5rem 1rem; border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: 0.88rem; background: #fff;">
                <option value="newest" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>Newest Items</option>
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
            <h3>No products found</h3>
            <p style="color: #777; margin: 0.5rem 0 1.5rem;">Try choosing another category or clearing your filters.</p>
            <a href="{{ route('shop') }}" class="btn-primary">View All Products</a>
        </div>
    @endif
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
