@extends('layouts.app')

@section('title', 'Order Confirmed #' . $order['order_number'] . ' | Baby Fashion BD')

@section('content')
<div class="container" style="padding: 3rem 1rem 5rem; max-width: 760px;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 2.5rem; box-shadow: var(--shadow-md); text-align: center;">
        
        <!-- Checkmark badge -->
        <div style="width: 72px; height: 72px; background: #E8F8F5; color: #00B894; border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto 1.5rem;">
            ✓
        </div>

        <span class="hero-tag" style="background: var(--color-primary-light); color: var(--color-primary);">Order Placed Successfully!</span>
        <h1 style="font-family: var(--font-heading); font-size: 2rem; margin: 0.5rem 0;">Thank You, {{ $order['customer_name'] }}!</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem; margin-bottom: 2rem;">
            Your order <strong>#{{ $order['order_number'] }}</strong> has been received and is now pending confirmation. Our customer service team will call you shortly on <strong>{{ $order['customer_phone'] }}</strong>.
        </p>

        <!-- Order Summary Card -->
        <div style="background: var(--color-bg-gray); border-radius: var(--radius-md); padding: 1.5rem; text-align: left; margin-bottom: 2rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; margin-bottom: 1rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem;">Order Invoice Details</h3>

            <div style="display: flex; flex-direction: column; gap: 0.6rem; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--color-text-muted);">Order Number:</span>
                    <strong>#{{ $order['order_number'] }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--color-text-muted);">Delivery Address:</span>
                    <span>{{ $order['customer_address'] }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--color-text-muted);">Delivery Region:</span>
                    <span>{{ $order['delivery_area_label'] }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--color-text-muted);">Payment Method:</span>
                    <span>Cash on Delivery</span>
                </div>
            </div>

            <!-- Items -->
            <div style="margin-top: 1.2rem; border-top: 1px dashed var(--color-border); padding-top: 1rem;">
                <h4 style="font-size: 0.92rem; margin-bottom: 0.8rem;">Ordered Items:</h4>
                @foreach($order['items'] as $it)
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; font-size: 0.88rem;">
                        <div>
                            <strong>{{ $it['title'] }}</strong> ({{ $it['size'] }}) &times; {{ $it['quantity'] }}
                        </div>
                        <div style="font-weight: 700; color: var(--color-primary);">
                            ৳ {{ number_format($it['price'] * $it['quantity']) }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Totals -->
            <div style="border-top: 2px solid #ddd; margin-top: 1rem; padding-top: 0.8rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.9rem;">
                    <span>Subtotal:</span>
                    <span>৳ {{ number_format($order['subtotal']) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.9rem;">
                    <span>Shipping:</span>
                    <span>{{ $order['shipping'] === 0 ? 'FREE' : '৳ ' . number_format($order['shipping']) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 800; color: var(--color-primary); margin-top: 0.6rem;">
                    <span>Total Payable:</span>
                    <span>৳ {{ number_format($order['total']) }}</span>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('home') }}" class="btn-primary">
                Return to Home &rarr;
            </a>
            <a href="https://wa.me/8801560016740?text=Hi%20Baby%20Fashion%20BD,%20I%20have%20an%20inquiry%20regarding%20Order%20{{ $order['order_number'] }}" class="btn-primary" style="background:#25D366;">
                Need Help on WhatsApp
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function() {
        const orderNo = "{{ $order['order_number'] }}";
        const totalVal = {{ (float)($order['total'] ?? 0) }};
        const itemsCount = {{ count($order['items'] ?? []) }};
        const dedupeKey = 'ga_tracked_order_' + orderNo;

        if (!sessionStorage.getItem(dedupeKey) && window.GrowthAgroTracking) {
            sessionStorage.setItem(dedupeKey, '1');
            window.GrowthAgroTracking.track('purchase', {
                entity_type: 'order',
                entity_id: orderNo,
                event_value: totalVal,
                properties: {
                    items_count: itemsCount,
                    currency: 'BDT'
                }
            });
        }

        // Meta Pixel Purchase Event with Deduplication Guard
        const metaDedupeKey = 'meta_tracked_purchase_' + orderNo;
        if (!sessionStorage.getItem(metaDedupeKey) && typeof window.fbq === 'function') {
            sessionStorage.setItem(metaDedupeKey, '1');
            window.fbq('track', 'Purchase', {
                content_ids: @json(collect($order['items'] ?? [])->pluck('title')->values()->toArray()),
                content_name: '{{ addslashes($order["items"][0]["title"] ?? "Order #" . $order["order_number"]) }}',
                content_type: 'product',
                value: totalVal,
                currency: 'BDT',
                num_items: {{ array_sum(array_column($order['items'] ?? [], 'quantity')) ?: 1 }}
            });
        }
    })();
</script>
@endpush
@endsection
