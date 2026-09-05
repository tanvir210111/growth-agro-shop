@extends('layouts.app')

@section('title', 'Checkout (Cash on Delivery) | ' . \App\Models\Setting::get('site_title', 'Growth Agro'))

@section('content')
<div class="container checkout-section">
    <h1 style="font-family: var(--font-heading); font-size: 2rem; margin-bottom: 2rem; text-align: center;">
        Cash on Delivery Checkout (ক্যাশ অন ডেলিভারি)
    </h1>

    <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
        @csrf

        @if(!empty($directProduct))
            <input type="hidden" name="direct_product_id" value="{{ $directProduct['id'] }}">
            <input type="hidden" name="direct_size" value="{{ $directProduct['size'] }}">
            <input type="hidden" name="direct_quantity" value="{{ $directProduct['quantity'] }}">
        @endif

        <div class="checkout-grid">
            <!-- Left: Delivery Information Form -->
            <div class="checkout-card">
                <h3>1. Delivery Address & Contact</h3>

                <div class="form-group">
                    <label for="customer_name">Full Name (আপনার নাম)*</label>
                    <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="e.g. Rahat Ahmed" required value="{{ old('customer_name') }}">
                </div>

                <div class="form-group">
                    <label for="customer_phone">Mobile Number (সচল মোবাইল নম্বর)*</label>
                    <input type="tel" id="customer_phone" name="customer_phone" class="form-control" placeholder="01XXXXXXXXX" required value="{{ old('customer_phone') }}">
                    <small style="color: var(--color-text-light); font-size: 0.78rem;">Our support team will call you to confirm your order.</small>
                </div>

                <div class="form-group">
                    <label for="customer_address">Full Street Address (সম্পূর্ণ ঠিকানা)*</label>
                    <textarea id="customer_address" name="customer_address" class="form-control" rows="3" placeholder="House no, Road no, Area, Thana, District" required>{{ old('customer_address') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Select Delivery Area (ডেলিভারি এলাকা)*</label>
                    <div class="delivery-area-options">
                        <label class="area-option-card selected" id="areaInsideDhakaLabel">
                            <input type="radio" name="delivery_area" value="inside_dhaka" checked onchange="updateCheckoutDeliveryArea('inside_dhaka')" style="display:none;">
                            <span class="area-name">📍 Inside Dhaka</span>
                            <span class="area-charge" id="insideChargeLabel">৳ 70</span>
                            <small style="color: #666; font-size: 0.75rem;">Delivery in 24-48 Hours</small>
                        </label>

                        <label class="area-option-card" id="areaOutsideDhakaLabel">
                            <input type="radio" name="delivery_area" value="outside_dhaka" onchange="updateCheckoutDeliveryArea('outside_dhaka')" style="display:none;">
                            <span class="area-name">🚚 Outside Dhaka</span>
                            <span class="area-charge" id="outsideChargeLabel">৳ 130</span>
                            <small style="color: #666; font-size: 0.75rem;">Delivery in 2-4 Days</small>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="order_notes">Order Notes / Special Requests (ঐচ্ছিক)</label>
                    <input type="text" id="order_notes" name="order_notes" class="form-control" placeholder="e.g. Please deliver after 3 PM" value="{{ old('order_notes') }}">
                </div>
            </div>

            <!-- Right: Order Review & Total -->
            <div class="checkout-card">
                <h3>2. Order Summary</h3>

                <!-- Items list -->
                <div style="margin-bottom: 1.5rem; max-height: 250px; overflow-y: auto;">
                    @if(!empty($directProduct))
                        <div class="cart-item">
                            <img src="{{ $directProduct['image'] }}" alt="{{ $directProduct['title'] }}" class="cart-item-img" style="width:60px; height:60px;">
                            <div class="cart-item-details">
                                <h4 class="cart-item-title">{{ $directProduct['title'] }}</h4>
                                <div class="cart-item-variant">Size: {{ $directProduct['size'] }} &times; {{ $directProduct['quantity'] }}</div>
                                <div style="font-weight: 700; color: var(--color-primary);">৳ {{ number_format($directProduct['price'] * $directProduct['quantity']) }}</div>
                            </div>
                        </div>
                    @else
                        @foreach($cartSummary['items'] as $item)
                            <div class="cart-item">
                                <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="cart-item-img" style="width:60px; height:60px;">
                                <div class="cart-item-details">
                                    <h4 class="cart-item-title">{{ $item['title'] }}</h4>
                                    <div class="cart-item-variant">Size: {{ $item['size'] }} &times; {{ $item['quantity'] }}</div>
                                    <div style="font-weight: 700; color: var(--color-primary);">৳ {{ number_format($item['price'] * $item['quantity']) }}</div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Price Rows -->
                @php
                    $subtotal = !empty($directProduct) ? ($directProduct['price'] * $directProduct['quantity']) : $cartSummary['subtotal'];
                    $isFree = $subtotal >= 3000;
                    $initialShipping = $isFree ? 0 : 70;
                @endphp

                <div class="summary-row">
                    <span>Subtotal</span>
                    <strong id="summarySubtotal" data-subtotal="{{ $subtotal }}">৳ {{ number_format($subtotal) }}</strong>
                </div>

                <div class="summary-row">
                    <span>Delivery Charge</span>
                    <strong id="summaryShipping" style="color: var(--color-primary);">
                        {{ $isFree ? 'FREE (৳ 0)' : '৳ 70' }}
                    </strong>
                </div>

                <div class="summary-row total-row">
                    <span>Total Payable (ক্যাশ)</span>
                    <span class="val" id="summaryTotal">৳ {{ number_format($subtotal + $initialShipping) }}</span>
                </div>

                <div style="background: var(--color-bg-warm); padding: 1rem; border-radius: var(--radius-sm); margin: 1.5rem 0; font-size: 0.85rem; border: 1px solid #FFE0B2;">
                    <strong>💵 Payment Method:</strong> Cash on Delivery. Pay only after receiving and checking your package!
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; font-size: 1.1rem; padding: 1rem;">
                    ✓ Confirm Order (অর্ডার নিশ্চিত করুন)
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const subtotal = {{ $subtotal }};
    const isFreeShipping = {{ $isFree ? 'true' : 'false' }};

    function updateCheckoutDeliveryArea(area) {
        document.getElementById('areaInsideDhakaLabel').classList.toggle('selected', area === 'inside_dhaka');
        document.getElementById('areaOutsideDhakaLabel').classList.toggle('selected', area === 'outside_dhaka');

        let shipping = 0;
        if (!isFreeShipping) {
            shipping = (area === 'inside_dhaka') ? 70 : 130;
        }

        document.getElementById('summaryShipping').textContent = (shipping === 0) ? 'FREE (৳ 0)' : `৳ ${shipping}`;
        document.getElementById('summaryTotal').textContent = `৳ ${(subtotal + shipping).toLocaleString()}`;
    }

    // Shared Event ID for InitiateCheckout deduplication
    const icEventId = 'ic_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);

    // Growth Agro Unified Checkout Started Tracking
    if (window.GrowthAgroTracking) {
        window.GrowthAgroTracking.track('checkout_started', {
            event_id: icEventId,
            entity_type: 'checkout',
            event_value: {{ (float)($subtotal ?? 0) }},
            properties: {
                subtotal: {{ (float)($subtotal ?? 0) }},
                items_count: {{ count($cartSummary['items'] ?? []) }}
            }
        });
    }

    // Meta Pixel: InitiateCheckout Event
    if (typeof window.fbq === 'function') {
        window.fbq('track', 'InitiateCheckout', {
            content_ids: @json(collect($cartSummary['items'] ?? [])->pluck('slug')->values()->toArray()),
            content_type: 'product',
            value: {{ (float)($subtotal ?? 0) }},
            currency: 'BDT',
            num_items: {{ (int)($cartSummary['item_count'] ?? count($cartSummary['items'] ?? [])) }}
        }, {
            eventID: icEventId
        });
    }
</script>
@endpush
@endsection
