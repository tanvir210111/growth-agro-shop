@extends('layouts.app')

@section('title', $product['title'] . ' | Baby Fashion BD')
@section('meta_description', $product['short_description'] ?? 'Premium baby wear from Baby Fashion BD')

@section('content')
<div class="container product-detail-section">
    <!-- Breadcrumb -->
    <div style="font-size: 0.85rem; color: var(--color-text-light); margin-bottom: 1.5rem;">
        <a href="{{ route('home') }}">Home</a> &rsaquo; 
        <a href="{{ route('collection.show', $product['category_handle']) }}">{{ $product['category_name'] }}</a> &rsaquo; 
        <span>{{ $product['title'] }}</span>
    </div>

    <div class="product-detail-grid">
        <!-- Left: Image Gallery -->
        <div>
            <div class="modal-gallery-main" style="border: 1px solid var(--color-border); aspect-ratio: 1/1;">
                <img id="mainProductImage" src="{{ $product['primary_image'] }}" alt="{{ $product['title'] }}">
            </div>
            
            <div class="gallery-thumbs">
                @foreach(($product['gallery'] ?? [$product['primary_image']]) as $img)
                    <div class="gallery-thumb" onclick="document.getElementById('mainProductImage').src='{{ $img }}'">
                        <img src="{{ $img }}" alt="{{ $product['title'] }}">
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right: Details & Order Box -->
        <div>
            <span class="product-category" style="font-size: 0.85rem;">{{ $product['category_name'] }}</span>
            <h1 class="product-detail-title">{{ $product['title'] }}</h1>
            <div class="detail-sku">SKU: <strong>{{ $product['sku'] }}</strong> | Stock: <span style="color: var(--color-success); font-weight: 700;">In Stock ({{ $product['stock'] }} Available)</span></div>

            <!-- Rating -->
            <div class="product-rating" style="font-size: 0.95rem; margin-bottom: 1rem;">
                <span class="rating-stars">★★★★★</span>
                <span style="font-weight: 700; color: var(--color-text-main);">{{ $product['rating'] }}</span>
                <span class="rating-count">({{ $product['reviews_count'] }} Verified Customer Reviews)</span>
            </div>

            <!-- Price -->
            <div class="detail-price-wrap">
                <span class="detail-current-price">৳ {{ number_format($product['price']) }}</span>
                @if(!empty($product['original_price']))
                    <span class="detail-original-price">৳ {{ number_format($product['original_price']) }}</span>
                    <span class="discount-tag" style="font-size: 0.88rem; padding: 4px 10px;">{{ $product['discount_percent'] }}% OFF</span>
                @endif
            </div>

            <!-- Size Selector -->
            <div class="option-group">
                <div class="option-label">
                    <span>Select Size/Age: <strong id="selectedSizeLabel" style="color: var(--color-primary);">{{ $product['sizes'][0] ?? 'Standard' }}</strong></span>
                    <span style="font-size: 0.8rem; color: var(--color-primary); cursor: pointer;">📏 Size Guide</span>
                </div>
                <div class="size-selector-pills">
                    @foreach(($product['sizes'] ?? ['Standard']) as $idx => $size)
                        <button type="button" class="size-pill-lg {{ $idx === 0 ? 'selected' : '' }}" onclick="selectProductSize(this, '{{ $size }}')">
                            {{ $size }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Quantity & Add to Cart -->
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                <div class="qty-control" style="padding: 4px;">
                    <button class="qty-btn" type="button" onclick="adjustDetailQty(-1)" style="width: 32px; height: 32px; font-size: 1.1rem;">-</button>
                    <span class="qty-val" id="detailQty" style="font-size: 1rem; min-width: 32px;">1</span>
                    <button class="qty-btn" type="button" onclick="adjustDetailQty(1)" style="width: 32px; height: 32px; font-size: 1.1rem;">+</button>
                </div>

                <button type="button" class="btn-primary" style="flex: 1; justify-content: center; font-size: 1.05rem;" onclick="addDetailToBag({{ $product['id'] }})">
                    🛒 Add to Shopping Bag
                </button>
            </div>

            <!-- 1-Click Fast Cash on Delivery Order Box -->
            <div class="instant-cod-box">
                <h4>⚡ Direct Order (Cash on Delivery)</h4>
                <p>Order quickly in 1 minute without registering! Pay cash when your parcel arrives.</p>

                <form action="{{ route('checkout.process') }}" method="POST">
                    @csrf
                    <input type="hidden" name="direct_product_id" value="{{ $product['id'] }}">
                    <input type="hidden" name="direct_size" id="directSizeInput" value="{{ $product['sizes'][0] ?? 'Standard' }}">
                    <input type="hidden" name="direct_quantity" id="directQtyInput" value="1">

                    <div class="cod-form-group">
                        <input type="text" name="customer_name" class="cod-form-input" placeholder="Your Full Name (আপনার নাম)*" required>
                    </div>

                    <div class="cod-form-group">
                        <input type="tel" name="customer_phone" class="cod-form-input" placeholder="Mobile Number (মোবাইল নম্বর 01XXXXXXXXX)*" required>
                    </div>

                    <div class="cod-form-group">
                        <input type="text" name="customer_address" class="cod-form-input" placeholder="Full Delivery Address (সম্পূর্ণ ঠিকানা)*" required>
                    </div>

                    <div class="cod-form-group">
                        <select name="delivery_area" class="cod-form-input" required>
                            <option value="inside_dhaka">Inside Dhaka - ৳ 70</option>
                            <option value="outside_dhaka">Outside Dhaka - ৳ 130</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; background: #26A69A; font-size: 1rem; padding: 0.9rem;">
                        ✓ Confirm Order Now (ক্যাশ অন ডেলিভারি)
                    </button>
                </form>
            </div>

            <!-- Description & Feature Points -->
            <div style="margin-top: 2rem; background: #fff; padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                <h3 style="font-family: var(--font-heading); font-size: 1.2rem; margin-bottom: 0.8rem;">Product Details</h3>
                <div style="font-size: 0.92rem; color: var(--color-text-muted); line-height: 1.7;">
                    {!! $product['description'] !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if(count($relatedProducts) > 0)
        <div style="margin-top: 4rem;">
            <div class="section-header">
                <span class="section-subtitle">You May Also Like</span>
                <h2 class="section-title">Matching Items</h2>
            </div>
            <div class="products-grid">
                @foreach($relatedProducts as $relProduct)
                    @include('partials.product-card', ['product' => $relProduct])
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    let currentDetailSize = "{{ $product['sizes'][0] ?? 'Standard' }}";
    let currentDetailQty = 1;

    function selectProductSize(btn, size) {
        document.querySelectorAll('.size-pill-lg').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        currentDetailSize = size;
        document.getElementById('selectedSizeLabel').textContent = size;
        document.getElementById('directSizeInput').value = size;
    }

    function adjustDetailQty(delta) {
        currentDetailQty = Math.max(1, currentDetailQty + delta);
        document.getElementById('detailQty').textContent = currentDetailQty;
        document.getElementById('directQtyInput').value = currentDetailQty;
    }

    function addDetailToBag(productId) {
        addToCart(productId, currentDetailSize, null, currentDetailQty);
    }

    // Growth Agro Unified Product View Tracking
    if (window.GrowthAgroTracking) {
        window.GrowthAgroTracking.track('product_view', {
            entity_type: 'product',
            entity_id: '{{ $product["slug"] }}',
            event_value: {{ (float)$product["price"] }}
        });
    }

    // Meta Pixel: ViewContent Event
    if (typeof window.fbq === 'function') {
        window.fbq('track', 'ViewContent', {
            content_ids: ['{{ $product["slug"] }}'],
            content_name: '{{ addslashes($product["title"]) }}',
            content_type: 'product',
            value: {{ (float)$product["price"] }},
            currency: 'BDT'
        });
    }
</script>
@endpush
@endsection
