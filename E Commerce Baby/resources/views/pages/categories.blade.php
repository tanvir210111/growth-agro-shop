@extends('layouts.app')

@section('title', 'All Categories | ' . \App\Models\Setting::get('site_title', 'Growth Agro'))
@section('meta_description', 'Explore all product categories at ' . \App\Models\Setting::get('site_title', 'Growth Agro') . ' with guaranteed quality and nationwide Cash on Delivery.')

@section('content')
<div class="container" style="padding: 2rem 1rem 5rem;">
    <!-- Breadcrumb -->
    <div style="font-size: 0.85rem; color: var(--color-text-light); margin-bottom: 1.5rem;">
        <a href="{{ route('home') }}">Home</a> &rsaquo; <span>All Categories</span>
    </div>

    <!-- Heading -->
    <div style="margin-bottom: 2rem;">
        <h1 style="font-family: var(--font-heading); font-size: 2.2rem; color: var(--color-text-main); margin-bottom: 0.5rem;">
            All Categories
        </h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
            Browse our comprehensive department categories to discover quality curated products.
        </p>
    </div>

    <!-- Category Search Bar -->
    <div style="margin-bottom: 2.5rem; max-width: 500px;">
        <div style="position: relative;">
            <input type="text" id="catSearchInput" onkeyup="filterCategoryCards()" placeholder="Search categories..." style="width: 100%; padding: 0.85rem 1.25rem 0.85rem 2.8rem; border: 1px solid var(--color-border); border-radius: var(--radius-md); font-size: 0.95rem; background: #fff;">
            <svg style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: #94a3b8;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path d="M21 21l-4.35-4.35"></path></svg>
        </div>
    </div>

    <!-- Categories Grid -->
    <div class="categories-grid" id="allCategoriesGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1.5rem;">
        @forelse($collections as $col)
            @php
                $colImg = !empty($col['image']) ? $col['image'] : '/images/placeholder.webp';
                $isAllCol = ($col['handle'] === 'all-collection');
            @endphp
            <a href="{{ $isAllCol ? route('shop') : route('collection.show', $col['handle']) }}" class="category-card cat-item-card" data-title="{{ strtolower($col['title']) }}" style="background: #fff; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 1.5rem 1rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: all 0.2s ease;">
                <div class="category-img-wrap" style="width: 72px; height: 72px; margin-bottom: 0.85rem; border-radius: 50%; background: #f8fafc; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 6px;">
                    <img src="{{ \Illuminate\Support\Str::startsWith($colImg, ['http://', 'https://', '/']) ? $colImg : asset($colImg) }}" alt="{{ $col['title'] }}" loading="lazy" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <h4 class="category-title" style="font-size: 1rem; font-weight: 700; color: var(--color-text-main); margin-bottom: 0.25rem;">{{ $col['title'] }}</h4>
                <span class="category-count" style="font-size: 0.8rem; color: #64748b;">{{ $col['item_count'] ?? 0 }} items</span>
            </a>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #64748b;">
                <p>No categories available currently.</p>
            </div>
        @endforelse
    </div>

    <!-- Browse All Products CTA Strip -->
    <div style="margin-top: 4rem; text-align: center; background: #fff; border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 2.5rem 1.5rem;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-text-main); margin-bottom: 0.4rem;">
            Can't find what you're looking for?
        </h3>
        <p style="color: #64748b; font-size: 0.92rem; margin-bottom: 1.25rem;">
            Explore our complete range of quality catalog products with instant delivery.
        </p>
        <a href="{{ route('shop') }}" class="btn-primary" style="padding: 0.85rem 2.5rem;">
            View All Products &rarr;
        </a>
    </div>
</div>

@push('scripts')
<script>
    function filterCategoryCards() {
        const query = (document.getElementById('catSearchInput').value || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.cat-item-card');
        cards.forEach(card => {
            const title = card.getAttribute('data-title') || '';
            if (!query || title.includes(query)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection
