@props(['product'])

<div class="product-card">
    <div class="product-media">
        <!-- Badges -->
        @if(!empty($product['discount_percent']))
            <span class="product-badge badge-sale">{{ $product['discount_percent'] }}% OFF</span>
        @elseif(!empty($product['is_new']))
            <span class="product-badge badge-new">NEW</span>
        @endif

        <!-- Images -->
        <a href="{{ route('product.show', $product['slug']) }}">
            <img src="{{ $product['primary_image'] }}" alt="{{ $product['title'] }}" class="product-img main-img" loading="lazy">
            @if(!empty($product['secondary_image']))
                <img src="{{ $product['secondary_image'] }}" alt="{{ $product['title'] }}" class="product-img hover-img" loading="lazy">
            @endif
        </a>

        <!-- Quick View Overlay Button -->
        <button type="button" class="quick-view-btn" onclick="openQuickView('{{ $product['slug'] }}')">
            👁️ Quick View
        </button>
    </div>

    <div class="product-info">
        <span class="product-category">{{ $product['category_name'] }}</span>
        
        <h3 class="product-title">
            <a href="{{ route('product.show', $product['slug']) }}" title="{{ $product['title'] }}">
                {{ $product['title'] }}
            </a>
        </h3>

        <div class="product-rating">
            <span class="rating-stars">★★★★★</span>
            <span class="rating-count">({{ $product['reviews_count'] ?? 15 }})</span>
        </div>

        <div class="product-price-wrap">
            <span class="current-price">৳ {{ number_format($product['price']) }}</span>
            @if(!empty($product['original_price']))
                <span class="original-price">৳ {{ number_format($product['original_price']) }}</span>
            @endif
        </div>

        <!-- Size Pills Preview -->
        <div class="product-sizes-preview">
            @foreach(array_slice($product['sizes'] ?? [], 0, 4) as $size)
                <span class="size-pill">{{ $size }}</span>
            @endforeach
        </div>

        <button type="button" class="btn-card-action" onclick="addToCart({{ $product['id'] }}, '{{ $product['sizes'][0] ?? 'Standard' }}', null, 1)">
            🛍️ Add to Bag
        </button>
    </div>
</div>
