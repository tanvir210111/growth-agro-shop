@extends('layouts.app')

@section('title', 'About Us | ' . \App\Models\Setting::get('site_title', 'Growth Agro'))

@section('content')
<div class="container" style="padding: 3rem 1rem 5rem; max-width: 860px;">
    <div style="background: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--color-border); padding: 3rem; box-shadow: var(--shadow-sm);">
        <span class="hero-tag" style="background: var(--color-primary-light); color: var(--color-primary);">About Our Store</span>
        <h1 style="font-family: var(--font-heading); font-size: 2.2rem; margin: 0.5rem 0 1.5rem; color: var(--color-text-main);">
            Welcome to {{ \App\Models\Setting::get('site_name', 'Growth Agro') }}
        </h1>

        <div style="font-size: 1rem; color: var(--color-text-muted); line-height: 1.8; display: flex; flex-direction: column; gap: 1.2rem;">
            <p>
                At <strong>{{ \App\Models\Setting::get('site_name', 'Growth Agro') }}</strong>, our mission is to deliver authentic, top-tier products across electronics, fashion, beauty, home essentials, groceries, and daily lifestyle categories with nationwide Cash on Delivery.
            </p>

            <h3 style="font-family: var(--font-heading); color: var(--color-text-main); margin-top: 1rem;">Why Customers Across Bangladesh Trust Us</h3>
            <ul style="padding-left: 1.5rem; display: flex; flex-direction: column; gap: 0.6rem;">
                <li><strong>100% Genuine & Quality Tested:</strong> Every product is inspected for quality and reliability.</li>
                <li><strong>Nationwide Cash on Delivery (COD):</strong> Order with total peace of mind; pay when your parcel arrives.</li>
                <li><strong>Fast & Safe Dispatch:</strong> Swift doorstep delivery across all 64 districts.</li>
                <li><strong>7-Day Easy Exchange:</strong> Dedicated customer care support for fast resolutions.</li>
            </ul>

            <div style="margin-top: 1.5rem; background: var(--color-bg-warm); padding: 1.5rem; border-radius: var(--radius-md); border-left: 4px solid var(--color-primary);">
                <strong>Store Location:</strong><br>
                {{ \App\Models\Setting::get('store_address', 'Mirpur, Dhaka-1216, Bangladesh') }}<br>
                <strong>Helpline:</strong> {{ \App\Models\Setting::get('support_phone', '01560-016740') }} | <strong>Email:</strong> {{ \App\Models\Setting::get('support_email', 'support@growthagro.shop') }}
            </div>
        </div>
    </div>
</div>
@endsection
