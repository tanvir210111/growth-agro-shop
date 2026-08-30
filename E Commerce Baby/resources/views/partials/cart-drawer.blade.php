<!-- Cart Drawer Backdrop Overlay -->
<div class="cart-drawer-overlay" id="cartDrawerOverlay"></div>

<!-- Slide-out Cart Drawer -->
<div class="cart-drawer" id="cartDrawer">
    <div class="drawer-header">
        <h3 class="drawer-title">
            <span>🛍️</span> Your Shopping Bag (<span class="cart-badge-count">0</span>)
        </h3>
        <button type="button" class="drawer-close-btn" id="cartCloseBtn" aria-label="Close Bag">✕</button>
    </div>

    <!-- Free Delivery Meter -->
    <div class="drawer-free-shipping">
        <div class="shipping-progress-text" id="shippingProgressText">
            Add ৳ 3,000 to get FREE Delivery!
        </div>
        <div class="shipping-progress-bar">
            <div class="shipping-progress-fill" id="shippingProgressFill" style="width: 0%;"></div>
        </div>
    </div>

    <!-- Cart Items Scroll Area -->
    <div class="drawer-items" id="drawerItems">
        <!-- Rendered dynamically by baby-fashion.js -->
    </div>

    <!-- Drawer Footer / Checkout CTA -->
    <div class="drawer-footer">
        <div class="drawer-subtotal-row">
            <span class="drawer-subtotal-label">Subtotal</span>
            <span class="drawer-subtotal-val" id="drawerSubtotal">৳ 0</span>
        </div>
        <a href="{{ route('checkout.index') }}" class="btn-checkout" id="drawerCheckoutBtn">
            <span>🚀</span> Proceed to Checkout (COD) &rarr;
        </a>
        <div style="text-align: center; margin-top: 0.6rem;">
            <a href="{{ route('cart.index') }}" style="font-size: 0.85rem; color: var(--color-text-muted); text-decoration: underline;">View Full Cart Page</a>
        </div>
    </div>
</div>
