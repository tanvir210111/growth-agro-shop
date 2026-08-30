@extends('layouts.app')

@section('title', 'Your Shopping Bag | Baby Fashion BD')

@section('content')
<div class="container" style="padding: 2.5rem 1rem 4rem;">
    <h1 style="font-family: var(--font-heading); font-size: 2rem; margin-bottom: 1.5rem;">
        Shopping Bag ({{ $cartSummary['item_count'] }} Items)
    </h1>

    @if(empty($cartSummary['items']))
        <div style="text-align: center; padding: 4rem 1rem; background: #ffffff; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <div style="font-size: 3.5rem; margin-bottom: 1rem;">🛍️</div>
            <h2 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 0.5rem;">Your bag is currently empty</h2>
            <p style="color: var(--color-text-muted); margin-bottom: 1.5rem;">Explore our adorable matching sets and baby clothes!</p>
            <a href="{{ route('shop') }}" class="btn-primary" style="padding: 0.85rem 2rem;">Shop All Collections &rarr;</a>
        </div>
    @else
        <div class="checkout-grid">
            <!-- Left: Items list -->
            <div class="checkout-card">
                <h3>Bag Items</h3>
                <div style="display: flex; flex-direction: column; gap: 1.2rem;">
                    @foreach($cartSummary['items'] as $item)
                        <div class="cart-item" style="padding-bottom: 1.2rem;">
                            <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="cart-item-img" style="width: 84px; height: 84px;">
                            <div class="cart-item-details">
                                <h4 class="cart-item-title" style="font-size: 1rem;">{{ $item['title'] }}</h4>
                                <div class="cart-item-variant">Size: <strong>{{ $item['size'] }}</strong></div>
                                <div class="cart-item-price-row" style="margin-top: 0.8rem;">
                                    <div class="qty-control">
                                        <button class="qty-btn" onclick="updateCartItemQty('{{ $item['id'] }}', {{ $item['quantity'] - 1 }}); window.location.reload();">-</button>
                                        <span class="qty-val">{{ $item['quantity'] }}</span>
                                        <button class="qty-btn" onclick="updateCartItemQty('{{ $item['id'] }}', {{ $item['quantity'] + 1 }}); window.location.reload();">+</button>
                                    </div>
                                    <div style="font-size: 1.1rem; font-weight: 800; color: var(--color-primary);">
                                        ৳ {{ number_format($item['price'] * $item['quantity']) }}
                                    </div>
                                    <button class="cart-item-remove" onclick="removeCartItem('{{ $item['id'] }}'); window.location.reload();">Remove</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right: Summary & Checkout -->
            <div class="checkout-card">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <strong style="color: var(--color-text-main);">৳ {{ number_format($cartSummary['subtotal']) }}</strong>
                </div>

                <div class="summary-row">
                    <span>Free Shipping Status:</span>
                    <span style="color: var(--color-success); font-weight: 600;">
                        {{ $cartSummary['free_shipping']['qualified'] ? 'Eligible for FREE Delivery' : 'Add ৳ ' . number_format($cartSummary['free_shipping']['remaining']) . ' more' }}
                    </span>
                </div>

                <div class="summary-row total-row">
                    <span>Estimated Total:</span>
                    <span class="val">৳ {{ number_format($cartSummary['subtotal']) }}</span>
                </div>

                <div style="margin-top: 1.5rem;">
                    <a href="{{ route('checkout.index') }}" class="btn-checkout" style="font-size: 1.05rem; padding: 1rem;">
                        Proceed to Checkout (COD) &rarr;
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
