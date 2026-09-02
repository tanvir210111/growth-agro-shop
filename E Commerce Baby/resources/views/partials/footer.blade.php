<footer class="main-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Col 1: About & Location -->
            <div class="footer-col footer-about">
                <div class="brand-logo" style="margin-bottom: 1.2rem;">
                    <img src="{{ asset('images/logo.png') }}" alt="Baby Fashion BD" style="height: 52px; background:#fff; padding: 4px 8px; border-radius: var(--radius-sm);">
                </div>
                <p>
                    Baby Fashion BD is Bangladesh's trusted baby clothing store providing 100% breathable organic cotton, hypoallergenic fabrics, and stylish matching sets crafted for your little ones' joy & comfort.
                </p>
                <div class="social-links">
                    <a href="https://facebook.com" target="_blank" class="social-btn" aria-label="Facebook">f</a>
                    <a href="https://instagram.com" target="_blank" class="social-btn" aria-label="Instagram">ig</a>
                    <a href="https://youtube.com" target="_blank" class="social-btn" aria-label="YouTube">yt</a>
                </div>
            </div>

            <!-- Col 2: Categories -->
            <div class="footer-col">
                <h4>Shop Categories</h4>
                <ul class="footer-links-list">
                    <li><a href="{{ route('shop') }}">All Collections</a></li>
                    @php
                        $footerCategories = \App\Models\Category::where('status', true)->where('handle', '!=', 'all-collection')->orderBy('sort_order', 'asc')->take(5)->get();
                    @endphp
                    @foreach($footerCategories as $fCat)
                        <li><a href="{{ route('collection.show', $fCat->handle) }}">{{ $fCat->title }}</a></li>
                    @endforeach
                </ul>
            </div>

            <!-- Col 3: Quick Links & Policies -->
            <div class="footer-col">
                <h4>Customer Care</h4>
                <ul class="footer-links-list">
                    <li><a href="{{ route('about') }}">About Us</a></li>
                    <li><a href="{{ route('contact') }}">Contact Us</a></li>
                    <li><a href="{{ route('policy', 'return') }}">7-Day Return & Refund</a></li>
                    <li><a href="{{ route('policy', 'shipping') }}">Shipping & Delivery Terms</a></li>
                    <li><a href="{{ route('policy', 'terms') }}">Terms & Conditions</a></li>
                </ul>
            </div>

            <!-- Col 4: Contact Info -->
            <div class="footer-col">
                <h4>Store Location & Support</h4>
                <ul class="footer-contact-list">
                    <li>
                        <span>📍</span>
                        <span>Kuwaiti Moshjid Road, Dhali Bari, Bashundhara R/A 1229, Dhaka, Bangladesh</span>
                    </li>
                    <li>
                        <span>📞</span>
                        <span><strong>Phone:</strong> +880 1560-016740</span>
                    </li>
                    <li>
                        <span>✉️</span>
                        <span><strong>Email:</strong> support@babyfashionbd.com</span>
                    </li>
                    <li>
                        <span>⏰</span>
                        <span><strong>Delivery:</strong> Inside Dhaka (24-48h) | Outside Dhaka (2-4 Days)</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div>
                &copy; {{ date('Y') }} <strong>Baby Fashion BD</strong>. All rights reserved. Made with love for happy babies.
            </div>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <span>Payment Accepted: Cash on Delivery | bKash | Nagad</span>
            </div>
        </div>
    </div>
</footer>
