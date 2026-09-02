@extends('layouts.app')

@section('title', \App\Models\Setting::get('site_title', 'Growth Agro | Universal E-Commerce & Premium Products'))

@section('content')

    <!-- Hero Banner Slider -->
    <section class="hero-slider-section">
        <div class="container">
            <div class="hero-slider-container">
                @php
                    $slides = !empty($sliders) ? $sliders : (!empty($collections) ? $collections : []);
                @endphp

                @if(!empty($slides))
                    @foreach($slides as $index => $slide)
                        @php
                            $slideImg = !empty($slide['image']) ? $slide['image'] : (!empty($slide['banner_image']) ? $slide['banner_image'] : 'images/banners/all-collection.jpg');
                            $slideLink = !empty($slide['link']) ? $slide['link'] : (!empty($slide['handle']) ? route('collection.show', $slide['handle']) : route('shop'));
                            $slideTitle = !empty($slide['title']) ? $slide['title'] : 'Banner';
                        @endphp
                        <div class="hero-slide {{ $index === 0 ? 'active' : '' }}">
                            <a href="{{ $slideLink }}" style="display:block; width:100%; height:100%;">
                                <img src="{{ \Illuminate\Support\Str::startsWith($slideImg, ['http://', 'https://', '/']) ? $slideImg : asset($slideImg) }}" alt="{{ $slideTitle }}">
                            </a>
                        </div>
                    @endforeach
                @else
                    <div class="hero-slide active">
                        <a href="{{ route('shop') }}" style="display:block; width:100%; height:100%;">
                            <img src="{{ asset('images/banners/all-collection.jpg') }}" alt="Shop All">
                        </a>
                    </div>
                @endif

                <!-- Slider Controls -->
                <button type="button" class="slider-nav-btn prev" id="heroPrevBtn" aria-label="Previous Slide">‹</button>
                <button type="button" class="slider-nav-btn next" id="heroNextBtn" aria-label="Next Slide">›</button>

                <div class="slider-dots">
                    @if(!empty($slides))
                        @foreach($slides as $index => $slide)
                            <div class="slider-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}" title="{{ $slide['title'] ?? '' }}"></div>
                        @endforeach
                    @else
                        <div class="slider-dot active" data-slide="0"></div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Showcase -->
    <section class="categories-section">
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">Collections</span>
                <h2 class="section-title">Shop Our Top Categories</h2>
            </div>

            <div class="categories-grid">
                @forelse($collections as $col)
                    @php
                        $colImg = !empty($col['image']) ? $col['image'] : 'images/banners/all-collection.jpg';
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
            <div class="section-header">
                <span class="section-subtitle">Just Landed</span>
                <h2 class="section-title">New Arrivals</h2>
            </div>

            @if(!empty($newArrivals) && count($newArrivals) > 0)
                <div class="products-grid">
                    @foreach($newArrivals as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>

                <div style="text-align: center; margin-top: 2.5rem;">
                    <a href="{{ route('shop') }}" class="btn-primary" style="padding: 0.85rem 2.5rem;">
                        View All New Arrivals &rarr;
                    </a>
                </div>
            @else
                <div style="text-align: center; padding: 3rem 1rem; background: #ffffff; border-radius: var(--radius-md); border: 1px solid var(--color-border); color: #64748b;">
                    <p>New arrivals will be listed here soon.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Promotional Split Banner -->
    <section class="promo-banners-section">
        <div class="container">
            <div class="promo-grid">
                @php
                    $promo1Img = \App\Models\Setting::get('promo_banner_1_image', 'images/banner1.jpg');
                    $promo1Link = \App\Models\Setting::get('promo_banner_1_link', route('shop'));
                    $promo2Img = \App\Models\Setting::get('promo_banner_2_image', 'images/banner2.jpg');
                    $promo2Link = \App\Models\Setting::get('promo_banner_2_link', route('shop'));
                @endphp
                <div class="promo-card">
                    <a href="{{ $promo1Link }}" style="display:block; width:100%; height:100%;">
                        <img src="{{ \Illuminate\Support\Str::startsWith($promo1Img, ['http://', 'https://', '/']) ? $promo1Img : asset($promo1Img) }}" alt="Special Promotion" loading="lazy">
                    </a>
                </div>

                <div class="promo-card">
                    <a href="{{ $promo2Link }}" style="display:block; width:100%; height:100%;">
                        <img src="{{ \Illuminate\Support\Str::startsWith($promo2Img, ['http://', 'https://', '/']) ? $promo2Img : asset($promo2Img) }}" alt="Featured Collection" loading="lazy">
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Proven Bestsellers Section -->
    <section class="products-section" style="background: #FFFDF9; padding: 3.5rem 0;">
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">Customer Favorites</span>
                <h2 class="section-title">Proven Bestsellers</h2>
            </div>

            @if(!empty($bestsellers) && count($bestsellers) > 0)
                <div class="products-grid">
                    @foreach($bestsellers as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>

                <div style="text-align: center; margin-top: 2.5rem;">
                    <a href="{{ route('shop') }}" class="btn-primary" style="padding: 0.85rem 2.5rem;">
                        Explore Complete Catalog &rarr;
                    </a>
                </div>
            @else
                <div style="text-align: center; padding: 3rem 1rem; background: #ffffff; border-radius: var(--radius-md); border: 1px solid var(--color-border); color: #64748b;">
                    <p>Featured bestsellers will be listed here soon.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Trust Badges Section -->
    <section class="trust-badges-section">
        <div class="container">
            <div class="trust-badges-grid">
                <div class="trust-item">
                    <div class="trust-icon">🚚</div>
                    <div class="trust-info">
                        <h4>Cash on Delivery</h4>
                        <p>Pay cash upon receiving your order anywhere in Bangladesh.</p>
                    </div>
                </div>

                <div class="trust-item">
                    <div class="trust-icon">🛡️</div>
                    <div class="trust-info">
                        <h4>100% Quality Guaranteed</h4>
                        <p>Premium authentic products directly sourced for guaranteed satisfaction.</p>
                    </div>
                </div>

                <div class="trust-item">
                    <div class="trust-icon">🔄</div>
                    <div class="trust-info">
                        <h4>7-Day Easy Exchange</h4>
                        <p>Hassle-free exchange and dedicated customer support.</p>
                    </div>
                </div>

                <div class="trust-item">
                    <div class="trust-icon">🎁</div>
                    <div class="trust-info">
                        <h4>Free Delivery > ৳3,000</h4>
                        <p>Enjoy free doorstep shipping on orders above ৳3,000.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
