<footer class="main-footer">
    <div class="container">
        @php
            $siteLogo = \App\Models\Setting::get('site_logo');
            $siteName = \App\Models\Setting::get('site_name', 'Growth Agro');
            $sitePhone = \App\Models\Setting::get('support_phone', '01560-016740');
            $siteEmail = \App\Models\Setting::get('support_email', 'support@growthagro.shop');
            $siteAddress = \App\Models\Setting::get('store_address', 'Mirpur, Dhaka-1216, Bangladesh');
            $siteDescription = \App\Models\Setting::get('footer_description', 'Your one-stop shop for quality products at the best prices. Shop more, worry less.');
        @endphp

        <div class="footer-grid">
            <!-- Col 1: About & Location -->
            <div class="footer-col footer-about">
                <div class="brand-logo" style="margin-bottom: 1.2rem;">
                    @if(!empty($siteLogo))
                        <img src="{{ \Illuminate\Support\Str::startsWith($siteLogo, ['http://', 'https://', '/']) ? $siteLogo : asset($siteLogo) }}" alt="{{ $siteName }}" style="height: 48px; background:#fff; padding: 4px 8px; border-radius: var(--radius-sm); object-fit: contain;">
                    @else
                        <div style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 0.35rem; line-height: 1;">
                            <span style="color: var(--color-primary); font-size: 1.6rem;">✦</span>
                            <span>{{ $siteName }}</span>
                        </div>
                    @endif
                </div>
                <p style="color: #cbd5e1; font-size: 0.9rem; line-height: 1.6;">
                    {{ $siteDescription }}
                </p>
                <div class="social-links" style="display: flex; gap: 0.5rem; margin-top: 1rem;">
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
                        <span>{{ $siteAddress }}</span>
                    </li>
                    <li>
                        <span>📞</span>
                        <span><strong>Phone:</strong> {{ $sitePhone }}</span>
                    </li>
                    <li>
                        <span>✉️</span>
                        <span><strong>Email:</strong> {{ $siteEmail }}</span>
                    </li>
                    <li>
                        <span>⏰</span>
                        <span><strong>Delivery:</strong> Inside Dhaka (24-48h) | Outside Dhaka (2-4 Days)</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem; margin-top: 2.5rem; font-size: 0.85rem; color: #94a3b8; flex-wrap: wrap; gap: 1rem;">
            <div>
                &copy; {{ date('Y') }} <strong>{{ $siteName }}</strong>. All rights reserved.
            </div>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <span>Payment Accepted: Cash on Delivery | bKash | Nagad</span>
            </div>
        </div>
    </div>
</footer>
