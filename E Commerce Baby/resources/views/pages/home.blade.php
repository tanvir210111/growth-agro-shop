@extends('layouts.app')

@section('title', \App\Models\Setting::get('site_title', 'Growth Agro | Universal E-Commerce & Premium Products'))

@section('content')

    <!-- Hero Banner Slider -->
    <section class="hero-slider-section">
        <div class="container">
            <div class="hero-slider-container" id="heroSliderContainer" tabindex="0" role="region" aria-label="Hero Carousel">
                @php
                    $slides = !empty($sliders) ? $sliders : [];
                @endphp

                @if(!empty($slides) && count($slides) > 0)
                    @foreach($slides as $index => $slide)
                        @php
                            $slideImg = !empty($slide['image']) ? $slide['image'] : '/uploads/sliders/hero_banner_1.webp';
                            $slideLink = !empty($slide['link']) ? $slide['link'] : route('shop');
                            $slideTitle = !empty($slide['title']) ? $slide['title'] : "SUMMER SALE\nUP TO 40% OFF";
                            $slideSubtitle = !empty($slide['subtitle']) ? $slide['subtitle'] : 'TRENDING NOW';
                            $slideDesc = !empty($slide['description']) ? $slide['description'] : 'Discover amazing deals on all quality verified products with nationwide Cash on Delivery.';
                            $btnText = !empty($slide['button_text']) ? $slide['button_text'] : 'SHOP NOW →';
                        @endphp
                        <div class="hero-slide {{ $index === 0 ? 'active' : '' }}" data-slide-index="{{ $index }}" role="group" aria-roledescription="slide" aria-label="Slide {{ $index + 1 }} of {{ count($slides) }}">
                            <div class="hero-slide-inner">
                                <div class="hero-content">
                                    <span class="hero-eyebrow">{{ $slideSubtitle }}</span>
                                    <h1 class="hero-headline">{!! nl2br(e($slideTitle)) !!}</h1>
                                    <p class="hero-desc">{{ $slideDesc }}</p>
                                    <a href="{{ $slideLink }}" class="hero-cta-btn">{{ $btnText }}</a>
                                </div>
                                <div class="hero-artwork-wrap">
                                    <img src="{{ \Illuminate\Support\Str::startsWith($slideImg, ['http://', 'https://', '/']) ? $slideImg : asset($slideImg) }}" alt="{{ strip_tags($slideTitle) }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <!-- Clean Modern Universal Fallback Hero -->
                    <div class="hero-slide active" data-slide-index="0" role="group" aria-roledescription="slide" aria-label="Slide 1 of 1">
                        <div class="hero-slide-inner">
                            <div class="hero-content">
                                <span class="hero-eyebrow">TRENDING NOW</span>
                                <h1 class="hero-headline">SUPER SALE<br><span style="color: #EA580C;">UP TO 40% OFF</span></h1>
                                <p class="hero-desc">Discover amazing deals on quality products with nationwide Cash on Delivery.</p>
                                <a href="{{ route('shop') }}" class="hero-cta-btn">SHOP NOW &rarr;</a>
                            </div>
                            <div class="hero-artwork-wrap">
                                <div style="background: rgba(255,255,255,0.9); backdrop-filter: blur(8px); padding: 2rem 2.5rem; border-radius: 16px; text-align: center; border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 8px 24px rgba(0,0,0,0.05); width: 100%; max-width: 380px;">
                                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">🛍️</div>
                                    <div style="font-weight: 800; font-size: 1.15rem; color: #0F172A; margin-bottom: 0.25rem;">Universal Marketplace</div>
                                    <div style="font-size: 0.85rem; color: #64748B;">Curated for premium quality & value</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if(!empty($slides) && count($slides) > 1)
                    <!-- Navigation Arrows -->
                    <button type="button" class="hero-nav-arrow hero-prev" id="heroPrevBtn" aria-label="Previous Slide">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" class="hero-nav-arrow hero-next" id="heroNextBtn" aria-label="Next Slide">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <!-- Pagination Dots -->
                    <div class="slider-dots" id="heroSliderDots">
                        @foreach($slides as $index => $slide)
                            <button type="button" class="slider-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}" aria-label="Slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- Categories Showcase -->
    <section class="categories-section">
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">Categories</span>
                <h2 class="section-title">Shop Our Top Categories</h2>
            </div>

            <div class="categories-grid">
                @forelse($collections as $col)
                    @php
                        $colImg = !empty($col['image']) ? $col['image'] : '/images/placeholder.webp';
                    @endphp
                    <a href="{{ route('collection.show', $col['handle']) }}" class="category-card">
                        <div class="category-img-wrap">
                            <img src="{{ \Illuminate\Support\Str::startsWith($colImg, ['http://', 'https://', '/']) ? $colImg : asset($colImg) }}" alt="{{ $col['title'] }}" loading="lazy">
                        </div>
                        <h4 class="category-title">{{ $col['title'] }}</h4>
                        <span class="category-count">{{ $col['item_count'] ?? 0 }} Items</span>
                    </a>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #64748b;">
                        <p>No categories available currently.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- New Arrivals Section -->
    <section class="products-section">
        <div class="container">
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
                <div>
                    <span class="section-subtitle" style="text-transform: uppercase; font-size: 0.75rem; font-weight: 800; color: #EA580C; letter-spacing: 0.05em;">JUST LANDED</span>
                    <h2 class="section-title" style="font-size: 1.75rem; font-weight: 800; margin: 0.2rem 0 0;">New Arrivals</h2>
                </div>
                <div>
                    <a href="{{ route('shop') }}" style="color: #EA580C; font-weight: 700; font-size: 0.9rem; text-decoration: none;">
                        View All New Arrivals &rarr;
                    </a>
                </div>
            </div>

            @if(!empty($newArrivals) && count($newArrivals) > 0)
                <div class="products-grid">
                    @foreach($newArrivals as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 3rem 1rem; background: #ffffff; border-radius: var(--radius-md); border: 1px solid var(--color-border); color: #64748b;">
                    <p>New arrivals will be listed here soon.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Promotional Split Banner -->
    <section class="promo-banners-section" style="padding: 1rem 0 2.5rem;">
        <div class="container">
            <div class="promo-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
                @php
                    $promo1Img = \App\Models\Setting::get('promo_banner_1_image');
                    $promo1Link = \App\Models\Setting::get('promo_banner_1_link', route('shop'));
                    $promo2Img = \App\Models\Setting::get('promo_banner_2_image');
                    $promo2Link = \App\Models\Setting::get('promo_banner_2_link', route('shop'));
                @endphp

                <!-- Promo Card 1 -->
                <div class="promo-card" style="background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%); border: 1px solid #BFDBFE; border-radius: var(--radius-lg); overflow: hidden; padding: 2rem; position: relative;">
                    @if(!empty($promo1Img))
                        <a href="{{ $promo1Link }}" style="display:block; width:100%; height:100%;">
                            <img src="{{ \Illuminate\Support\Str::startsWith($promo1Img, ['http://', 'https://', '/']) ? $promo1Img : asset($promo1Img) }}" alt="Promotion" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                        </a>
                    @else
                        <div style="max-width: 280px;">
                            <span style="font-size: 0.78rem; font-weight: 800; color: #2563EB; letter-spacing: 0.05em; text-transform: uppercase;">SPECIAL COLLECTION</span>
                            <h3 style="font-size: 1.5rem; font-weight: 800; color: #1E3A8A; margin: 0.4rem 0 0.8rem; line-height: 1.2;">UP TO 30% OFF</h3>
                            <p style="font-size: 0.85rem; color: #475569; margin-bottom: 1.25rem;">Discover top rated products tailored for optimal value.</p>
                            <a href="{{ $promo1Link }}" class="btn-primary" style="padding: 0.6rem 1.4rem; font-size: 0.85rem; background: #2563EB;">SHOP NOW &rarr;</a>
                        </div>
                    @endif
                </div>

                <!-- Promo Card 2 -->
                <div class="promo-card" style="background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); border: 1px solid #FCD34D; border-radius: var(--radius-lg); overflow: hidden; padding: 2rem; position: relative;">
                    @if(!empty($promo2Img))
                        <a href="{{ $promo2Link }}" style="display:block; width:100%; height:100%;">
                            <img src="{{ \Illuminate\Support\Str::startsWith($promo2Img, ['http://', 'https://', '/']) ? $promo2Img : asset($promo2Img) }}" alt="Featured Collection" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                        </a>
                    @else
                        <div style="max-width: 280px;">
                            <span style="font-size: 0.78rem; font-weight: 800; color: #D97706; letter-spacing: 0.05em; text-transform: uppercase;">DAILY ESSENTIALS</span>
                            <h3 style="font-size: 1.5rem; font-weight: 800; color: #78350F; margin: 0.4rem 0 0.8rem; line-height: 1.2;">MIN 20% OFF</h3>
                            <p style="font-size: 0.85rem; color: #475569; margin-bottom: 1.25rem;">Quality verified catalog items ready for doorstep dispatch.</p>
                            <a href="{{ $promo2Link }}" class="btn-primary" style="padding: 0.6rem 1.4rem; font-size: 0.85rem; background: #D97706;">SHOP NOW &rarr;</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Proven Bestsellers Section -->
    <section class="products-section" style="background: #FAFAFA; padding: 2rem 0;">
        <div class="container">
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.25rem;">
                <div>
                    <span class="section-subtitle" style="text-transform: uppercase; font-size: 0.75rem; font-weight: 800; color: #EA580C; letter-spacing: 0.05em;">POPULAR & TRENDING</span>
                    <h2 class="section-title" style="font-size: 1.6rem; font-weight: 800; margin: 0.15rem 0 0;">Proven Bestsellers</h2>
                </div>
                <div>
                    <a href="{{ route('shop') }}" style="color: #EA580C; font-weight: 700; font-size: 0.88rem; text-decoration: none;">
                        Explore All &rarr;
                    </a>
                </div>
            </div>

            @if(!empty($bestsellers) && count($bestsellers) > 0)
                <div class="products-grid">
                    @foreach($bestsellers as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 3rem 1rem; background: #ffffff; border-radius: var(--radius-md); border: 1px solid var(--color-border); color: #64748b;">
                    <p>Featured bestsellers will be listed here soon.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Trust / Features Strip -->
    <section class="trust-badges-section" style="padding: 1.75rem 0; background: #ffffff; border-top: 1px solid var(--color-border);">
        <div class="container">
            <div class="trust-badges-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                <div class="trust-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: var(--radius-md); background: #F8FAFC; border: 1px solid var(--color-border);">
                    <div class="trust-icon" style="font-size: 1.6rem; width: 44px; height: 44px;">🚚</div>
                    <div class="trust-info">
                        <h4 style="font-size: 0.88rem; font-weight: 700; margin-bottom: 0.1rem; color: #0F172A;">Cash on Delivery</h4>
                        <p style="font-size: 0.75rem; color: #64748B; margin: 0;">Pay when you receive</p>
                    </div>
                </div>

                <div class="trust-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: var(--radius-md); background: #F8FAFC; border: 1px solid var(--color-border);">
                    <div class="trust-icon" style="font-size: 1.6rem; width: 44px; height: 44px;">⚡</div>
                    <div class="trust-info">
                        <h4 style="font-size: 0.88rem; font-weight: 700; margin-bottom: 0.1rem; color: #0F172A;">Fast Delivery</h4>
                        <p style="font-size: 0.75rem; color: #64748B; margin: 0;">Inside 1-3 working days</p>
                    </div>
                </div>

                <div class="trust-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: var(--radius-md); background: #F8FAFC; border: 1px solid var(--color-border);">
                    <div class="trust-icon" style="font-size: 1.6rem; width: 44px; height: 44px;">🔄</div>
                    <div class="trust-info">
                        <h4 style="font-size: 0.88rem; font-weight: 700; margin-bottom: 0.1rem; color: #0F172A;">Easy Returns</h4>
                        <p style="font-size: 0.75rem; color: #64748B; margin: 0;">7-day easy return</p>
                    </div>
                </div>

                <div class="trust-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: var(--radius-md); background: #F8FAFC; border: 1px solid var(--color-border);">
                    <div class="trust-icon" style="font-size: 1.6rem; width: 44px; height: 44px;">🔒</div>
                    <div class="trust-info">
                        <h4 style="font-size: 0.88rem; font-weight: 700; margin-bottom: 0.1rem; color: #0F172A;">100% Secure</h4>
                        <p style="font-size: 0.75rem; color: #64748B; margin: 0;">Secure verified payments</p>
                    </div>
                </div>

                <div class="trust-item" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: var(--radius-md); background: #F8FAFC; border: 1px solid var(--color-border);">
                    <div class="trust-icon" style="font-size: 1.6rem; width: 44px; height: 44px;">⭐</div>
                    <div class="trust-info">
                        <h4 style="font-size: 0.88rem; font-weight: 700; margin-bottom: 0.1rem; color: #0F172A;">Best Quality</h4>
                        <p style="font-size: 0.75rem; color: #64748B; margin: 0;">Premium quality products</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
