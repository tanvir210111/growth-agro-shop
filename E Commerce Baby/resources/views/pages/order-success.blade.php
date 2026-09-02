@extends('layouts.app')

@section('title', 'অর্ডার কনফার্মেশন #' . $order['order_number'] . ' | Growth Agro')

@section('content')
<div class="container" style="padding: 2.5rem 1rem 4.5rem; max-width: 740px;">
    <div style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 2.5rem 2rem; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.06), 0 8px 10px -6px rgba(0, 0, 0, 0.04); text-align: center;">
        
        <!-- Large Success Checkmark Badge -->
        <div style="width: 76px; height: 76px; background: #ecfdf5; color: #10b981; border: 2px solid #a7f3d0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.4rem; font-weight: bold; margin: 0 auto 1.25rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
            ✓
        </div>

        <span style="display: inline-block; background: #f0fdf4; color: #166534; font-size: 0.85rem; font-weight: 700; padding: 0.35rem 0.9rem; border-radius: 9999px; border: 1px solid #bbf7d0; margin-bottom: 0.75rem;">
            অর্ডার সফল হয়েছে • Order Confirmed
        </span>
        <h1 style="font-family: var(--font-heading, sans-serif); font-size: 1.85rem; font-weight: 800; color: #0f172a; margin: 0.3rem 0 0.6rem;">
            ধন্যবাদ, {{ $order['customer_name'] }}!
        </h1>
        <p style="color: #475569; font-size: 0.98rem; line-height: 1.6; margin-bottom: 2rem; max-width: 580px; margin-left: auto; margin-right: auto;">
            আপনার অর্ডার <strong>#{{ $order['order_number'] }}</strong> সফলভাবে গ্রহণ করা হয়েছে। অর্ডারটি নিশ্চিত করতে আমাদের প্রতিনিধি শীঘ্রই <strong>{{ $order['customer_phone'] }}</strong> নম্বরে আপনার সাথে ফোনে যোগাযোগ করবেন।
        </p>

        <!-- Order Summary Card -->
        <div style="background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; padding: 1.5rem; text-align: left; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.75rem;">
                <h3 style="font-family: var(--font-heading, sans-serif); font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0;">
                    অর্ডারের বিবরণ (Order Invoice)
                </h3>
                <span style="font-size: 0.85rem; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 0.2rem 0.6rem; border-radius: 6px;">
                    #{{ $order['order_number'] }}
                </span>
            </div>

            <!-- Customer & Delivery Info -->
            <div style="display: flex; flex-direction: column; gap: 0.65rem; font-size: 0.92rem; color: #334155;">
                <div style="display: flex; justify-content: space-between; gap: 1rem;">
                    <span style="color: #64748b;">গ্রাহকের নাম:</span>
                    <strong style="color: #0f172a; text-align: right;">{{ $order['customer_name'] }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 1rem;">
                    <span style="color: #64748b;">মোবাইল নম্বর:</span>
                    <strong style="color: #0f172a; text-align: right;">{{ $order['customer_phone'] }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 1rem;">
                    <span style="color: #64748b;">ডেলিভারি ঠিকানা:</span>
                    <span style="color: #0f172a; text-align: right; max-width: 65%;">{{ $order['customer_address'] }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 1rem;">
                    <span style="color: #64748b;">ডেলিভারি অঞ্চল:</span>
                    <span style="color: #0f172a; text-align: right;">{{ $order['delivery_area_label'] }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; gap: 1rem;">
                    <span style="color: #64748b;">পেমেন্ট পদ্ধতি:</span>
                    <span style="color: #166534; font-weight: 600; text-align: right;">ক্যাশ অন ডেলিভারি (Cash on Delivery)</span>
                </div>
            </div>

            <!-- Ordered Items List -->
            <div style="margin-top: 1.25rem; border-top: 1px dashed #cbd5e1; padding-top: 1rem;">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 0.85rem;">
                    অর্ডারকৃত পণ্যসমূহ:
                </h4>
                <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                    @foreach($order['items'] as $it)
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.92rem; padding: 0.4rem 0; border-bottom: 1px solid #f1f5f9;">
                            <div>
                                <strong style="color: #0f172a;">{{ $it['title'] ?? $it['name'] ?? 'Product' }}</strong>
                                @if(!empty($it['size']) && $it['size'] !== 'Default' && $it['size'] !== 'Standard')
                                    <span style="color: #64748b; font-size: 0.85rem;">({{ $it['size'] }})</span>
                                @endif
                                <span style="color: #64748b; margin-left: 0.35rem;">&times; {{ $it['quantity'] }} টি</span>
                            </div>
                            <div style="font-weight: 700; color: #0f172a;">
                                ৳ {{ number_format($it['price'] * $it['quantity']) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Financial Totals -->
            <div style="border-top: 2px solid #e2e8f0; margin-top: 1.25rem; padding-top: 0.9rem; font-size: 0.93rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.45rem; color: #475569;">
                    <span>সাবটোটাল (Subtotal):</span>
                    <span>৳ {{ number_format($order['subtotal']) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.45rem; color: #475569;">
                    <span>ডেলিভারি চার্জ (Delivery):</span>
                    <span style="{{ (float)$order['shipping'] === 0.0 ? 'color:#16a34a; font-weight:700;' : '' }}">
                        {{ (float)$order['shipping'] === 0.0 ? 'ফ্রি (FREE)' : '৳ ' . number_format($order['shipping']) }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 1.25rem; font-weight: 800; color: #dc2626; margin-top: 0.75rem; padding-top: 0.6rem; border-top: 1px solid #e2e8f0;">
                    <span>সর্বমোট বিল (Total Payable):</span>
                    <span>৳ {{ number_format($order['total']) }}</span>
                </div>
            </div>
        </div>

        <!-- Action CTAs -->
        <div class="no-print" style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <button type="button" onclick="window.print()" id="btnDownloadReceipt" class="btn-primary" style="background:#0F172A; border-color:#0F172A; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 700; cursor: pointer; color: #fff;">
                📄 রসিদ ডাউনলোড / প্রিন্ট করুন
            </button>
            <a href="{{ route('home') }}" class="btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.6rem; border-radius: 8px; text-decoration: none; font-weight: 700;">
                মূল ওয়েবসাইটে ফিরে যান &rarr;
            </a>
            <a href="https://wa.me/8801560016740?text=Hi%20Growth%20Agro,%20I%20have%20an%20inquiry%20regarding%20Order%20{{ $order['order_number'] }}" class="btn-primary" style="background:#25D366; border-color:#25D366; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 700;">
                💬 হোয়াটসঅ্যাপে যোগাযোগ
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    body {
        background: #ffffff !important;
        color: #0f172a !important;
        font-size: 12pt;
    }
    header, footer, .no-print, nav, .site-header, .site-footer {
        display: none !important;
    }
    .container {
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
    }
}
</style>
@endpush

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
