@extends('layouts.app')

@section('title', $product['title'] . ' | ' . \App\Models\Setting::get('site_title', 'Growth Agro'))
@section('meta_description', $product['short_description'] ?: strip_tags(substr($product['description'] ?? '', 0, 160)))

@section('content')
<div class="container product-detail-section">
    <!-- Breadcrumb -->
    <div style="font-size: 0.85rem; color: var(--color-text-light); margin-bottom: 1.5rem;">
        <a href="{{ route('home') }}">Home</a> &rsaquo;
        <a href="{{ route('collection.show', $product['category_handle'] ?? 'all-collection') }}">{{ $product['category_name'] ?? 'Products' }}</a> &rsaquo;
        <span>{{ $product['title'] }}</span>
    </div>

    <div class="product-detail-grid">
        <!-- Left: Image Gallery -->
        <div>
            <div class="modal-gallery-main" style="border: 1px solid var(--color-border); aspect-ratio: 1/1; border-radius: var(--radius-md); overflow: hidden; background: #fff;">
                <img id="mainProductImage" src="{{ $product['primary_image'] }}" alt="{{ $product['title'] }}" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            
            @php
                $gallery = !empty($product['gallery']) && is_array($product['gallery']) ? $product['gallery'] : [$product['primary_image']];
            @endphp
            @if(count($gallery) > 1)
                <div class="gallery-thumbs" style="display: flex; gap: 0.5rem; margin-top: 0.75rem; overflow-x: auto;">
                    @foreach($gallery as $img)
                        <div class="gallery-thumb" onclick="document.getElementById('mainProductImage').src='{{ $img }}'" style="cursor: pointer; width: 64px; height: 64px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); overflow: hidden; background: #fff;">
                            <img src="{{ $img }}" alt="{{ $product['title'] }}" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right: Details & Order Box -->
        <div>
            <span class="product-category" style="font-size: 0.85rem; color: var(--color-primary); font-weight: 600;">{{ $product['category_name'] ?? 'General' }}</span>
            <h1 class="product-detail-title" style="margin: 0.3rem 0 0.6rem; font-size: 1.8rem; line-height: 1.3;">{{ $product['title'] }}</h1>
            <div class="detail-sku" style="font-size: 0.88rem; color: #64748b; margin-bottom: 0.8rem;">
                SKU: <strong>{{ $product['sku'] }}</strong> | Stock: <span style="color: var(--color-success); font-weight: 700;">In Stock ({{ $product['stock'] }} Available)</span>
            </div>

            <!-- Rating -->
            <div class="product-rating" style="font-size: 0.95rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.4rem;">
                <span class="rating-stars" style="color: #f59e0b;">★★★★★</span>
                <span style="font-weight: 700; color: var(--color-text-main);">{{ $product['rating'] ?? 4.9 }}</span>
                <span class="rating-count" style="color: #64748b; font-size: 0.85rem;">({{ $product['reviews_count'] ?? 24 }} Customer Reviews)</span>
            </div>

            <!-- Price -->
            <div class="detail-price-wrap" style="display: flex; align-items: baseline; gap: 0.75rem; margin-bottom: 1.25rem;">
                <span class="detail-current-price" style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary);">৳ {{ number_format($product['price']) }}</span>
                @if(!empty($product['original_price']) && $product['original_price'] > $product['price'])
                    <span class="detail-original-price" style="font-size: 1.1rem; text-decoration: line-through; color: #94a3b8;">৳ {{ number_format($product['original_price']) }}</span>
                    <span class="discount-tag" style="font-size: 0.82rem; padding: 3px 8px; background: #fee2e2; color: #dc2626; border-radius: var(--radius-sm); font-weight: 700;">{{ $product['discount_percent'] }}% OFF</span>
                @endif
            </div>

            <!-- Option / Variant Selector -->
            @php
                $sizes = !empty($product['sizes']) && is_array($product['sizes']) ? $product['sizes'] : ['Standard'];
            @endphp
            @if(count($sizes) > 1 || (count($sizes) === 1 && $sizes[0] !== 'Standard'))
                <div class="option-group" style="margin-bottom: 1.25rem;">
                    <div class="option-label" style="font-size: 0.88rem; font-weight: 600; margin-bottom: 0.5rem;">
                        <span>Select Option/Variant: <strong id="selectedSizeLabel" style="color: var(--color-primary);">{{ $sizes[0] }}</strong></span>
                    </div>
                    <div class="size-selector-pills" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        @foreach($sizes as $idx => $size)
                            <button type="button" class="size-pill-lg {{ $idx === 0 ? 'selected' : '' }}" onclick="selectProductSize(this, '{{ $size }}')">
                                {{ $size }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quantity & Add to Cart -->
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                <div class="qty-control" style="padding: 4px; border: 1px solid var(--color-border); border-radius: var(--radius-sm); display: flex; align-items: center;">
                    <button class="qty-btn" type="button" onclick="adjustDetailQty(-1)" style="width: 32px; height: 32px; font-size: 1.1rem; border: none; background: #f1f5f9; cursor: pointer; border-radius: var(--radius-sm);">-</button>
                    <span class="qty-val" id="detailQty" style="font-size: 1rem; min-width: 36px; text-align: center; font-weight: 600;">1</span>
                    <button class="qty-btn" type="button" onclick="adjustDetailQty(1)" style="width: 32px; height: 32px; font-size: 1.1rem; border: none; background: #f1f5f9; cursor: pointer; border-radius: var(--radius-sm);">+</button>
                </div>

                <button type="button" class="btn-primary" style="flex: 1; min-width: 200px; justify-content: center; font-size: 1rem; padding: 0.85rem 1.5rem;" onclick="addDetailToBag({{ $product['id'] }})">
                    🛒 Add to Shopping Bag
                </button>
            </div>

            <!-- Trust / Guarantee Strip -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-bottom: 1.5rem; background: #f8fafc; padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid var(--color-border); text-align: center; font-size: 0.8rem; color: #475569;">
                <div>🚚 <strong>Cash on Delivery</strong></div>
                <div>🔄 <strong>7-Day Returns</strong></div>
                <div>🛡️ <strong>100% Guaranteed</strong></div>
            </div>

            <!-- 1-Click Fast Cash on Delivery Order Box -->
            <div class="instant-cod-box" style="background: #FFFDF9; border: 1px solid #fed7aa; border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.5rem;">
                <h4 style="margin: 0 0 0.4rem; color: #9a3412; font-size: 1.05rem;">⚡ Direct Order (Cash on Delivery)</h4>
                <p style="margin: 0 0 1rem; font-size: 0.85rem; color: #64748b;">Order quickly in 1 minute without registering! Pay cash upon parcel delivery.</p>

                <form action="{{ route('checkout.process') }}" method="POST">
                    @csrf
                    <input type="hidden" name="direct_product_id" value="{{ $product['id'] }}">
                    <input type="hidden" name="direct_size" id="directSizeInput" value="{{ $sizes[0] ?? 'Standard' }}">
                    <input type="hidden" name="direct_quantity" id="directQtyInput" value="1">

                    <div class="cod-form-group" style="margin-bottom: 0.6rem;">
                        <input type="text" name="customer_name" class="cod-form-input" placeholder="Your Full Name (আপনার নাম)*" required style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                    </div>

                    <div class="cod-form-group" style="margin-bottom: 0.6rem;">
                        <input type="tel" name="customer_phone" class="cod-form-input" placeholder="Mobile Number (মোবাইল নম্বর 01XXXXXXXXX)*" required style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                    </div>

                    <div class="cod-form-group" style="margin-bottom: 0.6rem;">
                        <input type="text" name="customer_address" class="cod-form-input" placeholder="Full Delivery Address (সম্পূর্ণ ঠিকানা)*" required style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: 0.9rem;">
                    </div>

                    <div class="cod-form-group" style="margin-bottom: 0.8rem;">
                        <select name="delivery_area" class="cod-form-input" required style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: 0.9rem; background: #fff;">
                            <option value="inside_dhaka">Inside Dhaka - ৳ 70</option>
                            <option value="outside_dhaka">Outside Dhaka - ৳ 130</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; background: #26A69A; font-size: 1rem; padding: 0.85rem;">
                        ✓ Confirm Order Now (ক্যাশ অন ডেলিভারি)
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Product Information Tabs -->
    <div style="margin-top: 3rem; background: #fff; border-radius: var(--radius-md); border: 1px solid var(--color-border); overflow: hidden;">
        <div style="display: flex; border-bottom: 1px solid var(--color-border); background: #f8fafc; overflow-x: auto;" id="productTabs">
            <button type="button" class="tab-btn active" onclick="switchProductTab('tab-description', this)" style="padding: 1rem 1.5rem; font-weight: 700; border: none; background: transparent; cursor: pointer; border-bottom: 2px solid var(--color-primary); color: var(--color-primary); font-size: 0.95rem;">
                Description
            </button>
            <button type="button" class="tab-btn" onclick="switchProductTab('tab-specifications', this)" style="padding: 1rem 1.5rem; font-weight: 600; border: none; background: transparent; cursor: pointer; color: #64748b; font-size: 0.95rem;">
                Specifications
            </button>
            <button type="button" class="tab-btn" onclick="switchProductTab('tab-reviews', this)" style="padding: 1rem 1.5rem; font-weight: 600; border: none; background: transparent; cursor: pointer; color: #64748b; font-size: 0.95rem;">
                Reviews ({{ $product['reviews_count'] ?? 24 }})
            </button>
            <button type="button" class="tab-btn" onclick="switchProductTab('tab-delivery', this)" style="padding: 1rem 1.5rem; font-weight: 600; border: none; background: transparent; cursor: pointer; color: #64748b; font-size: 0.95rem;">
                Delivery & Returns
            </button>
        </div>

        <div style="padding: 2rem;">
            <!-- Tab 1: Description -->
            <div id="tab-description" class="tab-content" style="display: block; line-height: 1.8; color: #334155; font-size: 0.95rem;">
                {!! $product['description'] !!}
            </div>

            <!-- Tab 2: Specifications -->
            <div id="tab-specifications" class="tab-content" style="display: none;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                    <tbody>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.75rem 0; font-weight: 600; width: 30%; color: #64748b;">SKU Code</td>
                            <td style="padding: 0.75rem 0; color: #1e293b;">{{ $product['sku'] }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.75rem 0; font-weight: 600; color: #64748b;">Category</td>
                            <td style="padding: 0.75rem 0; color: #1e293b;">{{ $product['category_name'] ?? 'General' }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.75rem 0; font-weight: 600; color: #64748b;">Availability</td>
                            <td style="padding: 0.75rem 0; color: #10b981; font-weight: 600;">In Stock ({{ $product['stock'] }} units available)</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 0.75rem 0; font-weight: 600; color: #64748b;">Available Options</td>
                            <td style="padding: 0.75rem 0; color: #1e293b;">{{ implode(', ', $sizes) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem 0; font-weight: 600; color: #64748b;">Authenticity</td>
                            <td style="padding: 0.75rem 0; color: #1e293b;">100% Genuine & Quality Tested</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Tab 3: Reviews -->
            <div id="tab-reviews" class="tab-content" style="display: none;">
                <div style="display: flex; gap: 2rem; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap;">
                    <div style="text-align: center; padding: 1.5rem 2rem; background: #f8fafc; border-radius: var(--radius-md);">
                        <div style="font-size: 2.5rem; font-weight: 800; color: var(--color-primary); line-height: 1;">{{ $product['rating'] ?? 4.9 }}</div>
                        <div style="color: #f59e0b; margin: 0.25rem 0;">★★★★★</div>
                        <div style="font-size: 0.8rem; color: #64748b;">Based on {{ $product['reviews_count'] ?? 24 }} verified reviews</div>
                    </div>
                    <div style="flex: 1; min-width: 250px;">
                        <p style="margin: 0 0 0.5rem; font-size: 0.95rem; font-weight: 600;">Customer Feedback Summary</p>
                        <p style="margin: 0; font-size: 0.88rem; color: #64748b; line-height: 1.6;">
                            98% of customers recommended this product for its high quality, accurate representation, and prompt doorstep delivery.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Delivery & Returns -->
            <div id="tab-delivery" class="tab-content" style="display: none; line-height: 1.7; font-size: 0.9rem; color: #334155;">
                <h4 style="margin: 0 0 0.5rem; font-size: 1rem;">Doorstep Delivery</h4>
                <ul style="margin: 0 0 1.5rem; padding-left: 1.25rem;">
                    <li><strong>Inside Dhaka:</strong> Delivery within 24-48 hours (৳ 70 delivery charge).</li>
                    <li><strong>Outside Dhaka:</strong> Delivery within 2-4 business days (৳ 130 delivery charge).</li>
                    <li><strong>Cash on Delivery:</strong> Available across all districts of Bangladesh.</li>
                </ul>

                <h4 style="margin: 0 0 0.5rem; font-size: 1rem;">Easy 7-Day Exchange & Returns</h4>
                <p style="margin: 0;">
                    If you receive a defective or mismatched product, you may request an exchange within 7 days of receiving your package. Contact our customer support team for fast assistance.
                </p>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if(!empty($relatedProducts) && count($relatedProducts) > 0)
        <div style="margin-top: 4rem;">
            <div class="section-header">
                <span class="section-subtitle">You May Also Like</span>
                <h2 class="section-title">Matching Products</h2>
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
    let currentDetailSize = "{{ $sizes[0] ?? 'Standard' }}";
    let currentDetailQty = 1;

    function selectProductSize(btn, size) {
        document.querySelectorAll('.size-pill-lg').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        currentDetailSize = size;
        const sizeLabel = document.getElementById('selectedSizeLabel');
        if (sizeLabel) sizeLabel.textContent = size;
        const directSize = document.getElementById('directSizeInput');
        if (directSize) directSize.value = size;
    }

    function adjustDetailQty(delta) {
        currentDetailQty = Math.max(1, currentDetailQty + delta);
        const qtyEl = document.getElementById('detailQty');
        if (qtyEl) qtyEl.textContent = currentDetailQty;
        const directQty = document.getElementById('directQtyInput');
        if (directQty) directQty.value = currentDetailQty;
    }

    function addDetailToBag(productId) {
        triggerDetailInitiateCheckout();
        addToCart(productId, currentDetailSize, null, currentDetailQty);
    }

    function switchProductTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.style.borderBottom = 'none';
            b.style.color = '#64748b';
            b.style.fontWeight = '600';
        });

        const activeContent = document.getElementById(tabId);
        if (activeContent) activeContent.style.display = 'block';

        btn.style.borderBottom = '2px solid var(--color-primary)';
        btn.style.color = 'var(--color-primary)';
        btn.style.fontWeight = '700';
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

    // Meta Pixel: InitiateCheckout Event (On Order Form Interaction / Add to Bag)
    let detailCheckoutStarted = false;
    function triggerDetailInitiateCheckout() {
        if (detailCheckoutStarted) return;
        detailCheckoutStarted = true;

        if (typeof window.fbq === 'function') {
            window.fbq('track', 'InitiateCheckout', {
                content_ids: ['{{ $product["slug"] }}'],
                content_name: '{{ addslashes($product["title"]) }}',
                content_type: 'product',
                value: {{ (float)$product["price"] }} * currentDetailQty,
                currency: 'BDT',
                num_items: currentDetailQty
            });
        }
    }

    const codForm = document.querySelector('.instant-cod-box form');
    if (codForm) {
        codForm.addEventListener('focusin', triggerDetailInitiateCheckout, { once: true, passive: true });
        codForm.addEventListener('submit', triggerDetailInitiateCheckout);
    }
</script>
@endpush
@endsection
