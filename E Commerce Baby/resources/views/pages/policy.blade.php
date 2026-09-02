@extends('layouts.app')

@section('title', 'Store Policies | ' . \App\Models\Setting::get('site_title', 'Growth Agro'))

@section('content')
<div class="container" style="padding: 3rem 1rem 5rem; max-width: 860px;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 3rem; box-shadow: var(--shadow-sm);">
        <span class="hero-tag" style="background: var(--color-primary-light); color: var(--color-primary);">Transparency & Trust</span>
        <h1 style="font-family: var(--font-heading); font-size: 2.2rem; margin: 0.5rem 0 1.5rem;">
            {{ \App\Models\Setting::get('site_name', 'Growth Agro') }} Policies
        </h1>

        <div style="display: flex; flex-direction: column; gap: 2rem; font-size: 0.95rem; color: var(--color-text-muted); line-height: 1.8;">
            <section>
                <h3 style="font-family: var(--font-heading); color: var(--color-text-main); font-size: 1.3rem; margin-bottom: 0.5rem;">
                    1. 7-Day Easy Return & Exchange Policy
                </h3>
                <p>
                    If the size does not fit your baby or you receive an incorrect product, you can exchange it within 7 days of receiving the delivery. The item must be unworn, unwashed, and in its original packaging with all tags attached. Please contact our helpline or WhatsApp with your Order ID to initiate an exchange.
                </p>
            </section>

            <section>
                <h3 style="font-family: var(--font-heading); color: var(--color-text-main); font-size: 1.3rem; margin-bottom: 0.5rem;">
                    2. Shipping & Delivery Guidelines
                </h3>
                <p>
                    We deliver across all 64 districts in Bangladesh using reliable courier services.
                </p>
                <ul style="padding-left: 1.5rem; margin-top: 0.5rem;">
                    <li><strong>Inside Dhaka City:</strong> Delivery within 24 to 48 hours (৳ 70 delivery charge).</li>
                    <li><strong>Outside Dhaka / Suburbs:</strong> Delivery within 2 to 4 business days (৳ 130 delivery charge).</li>
                    <li><strong>Free Delivery Offer:</strong> All orders with a subtotal of ৳ 3,000 or more enjoy 100% Free Shipping anywhere in Bangladesh!</li>
                </ul>
            </section>

            <section>
                <h3 style="font-family: var(--font-heading); color: var(--color-text-main); font-size: 1.3rem; margin-bottom: 0.5rem;">
                    3. Cash on Delivery (COD) Inspection
                </h3>
                <p>
                    You are encouraged to check the outer parcel before payment. In case of any dispute or damage, you may reject the parcel on the spot or notify our hotline immediately at <strong>+880 1560-016740</strong>.
                </p>
            </section>
        </div>
    </div>
</div>
@endsection
