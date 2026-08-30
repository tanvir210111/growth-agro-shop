@extends('layouts.app')

@section('title', 'Baby Fashion BD | Cute & Premium Organic Baby Clothing')

@section('content')

    <!-- Hero Banner Slider -->
    <section class="hero-slider-section">
        <div class="container">
            <div class="hero-slider-container">
                @foreach($collections as $index => $col)
                    <div class="hero-slide {{ $index === 0 ? 'active' : '' }}">
                        <a href="{{ route('collection.show', $col['handle']) }}" style="display:block; width:100%; height:100%;">
                            <img src="{{ asset($col['banner_image']) }}" alt="{{ $col['title'] }}">
                        </a>
                    </div>
                @endforeach

                <!-- Slider Controls -->
                <button type="button" class="slider-nav-btn prev" id="heroPrevBtn" aria-label="Previous Slide">‹</button>
                <button type="button" class="slider-nav-btn next" id="heroNextBtn" aria-label="Next Slide">›</button>

                <div class="slider-dots">
                    @foreach($collections as $index => $col)
                        <div class="slider-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}" title="{{ $col['title'] }}"></div>
                    @endforeach
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
                @foreach($collections as $col)
                    <a href="{{ route('collection.show', $col['handle']) }}" class="category-card">
                        <div class="category-img-wrap">
                            <img src="{{ $col['image'] }}" alt="{{ $col['title'] }}" loading="lazy">
                        </div>
                        <h4 class="category-title">{{ $col['title'] }}</h4>
                        <span class="category-count">{{ $col['item_count'] ?? 12 }} Items</span>
                    </a>
                @endforeach
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

            <div class="products-grid">
                @foreach($newArrivals as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>

            <div style="text-align: center; margin-top: 2.5rem;">
                <a href="{{ route('collection.show', 'new-arrival') }}" class="btn-primary" style="padding: 0.85rem 2.5rem;">
                    View All New Arrivals &rarr;
                </a>
            </div>
        </div>
    </section>

    <!-- Promotional Split Banner -->
    <section class="promo-banners-section">
        <div class="container">
            <div class="promo-grid">
                <div class="promo-card">
                    <a href="{{ route('collection.show', 'baby-boys') }}" style="display:block; width:100%; height:100%;">
                        <img src="{{ asset('images/banner1.jpg') }}" alt="Baby Boy Sets" loading="lazy">
                    </a>
                </div>

                <div class="promo-card">
                    <a href="{{ route('collection.show', 'baby-girl') }}" style="display:block; width:100%; height:100%;">
                        <img src="{{ asset('images/banner2.jpg') }}" alt="Baby Girl Sets" loading="lazy">
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Proven Bestsellers Section -->
    <section class="products-section" style="background: #FFFDF9; padding: 3.5rem 0;">
        <div class="container">
            <div class="section-header">
                <span class="section-subtitle">Mom & Baby Favorites</span>
                <h2 class="section-title">Proven Bestsellers</h2>
            </div>

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
                    <div class="trust-icon">🌿</div>
                    <div class="trust-info">
                        <h4>100% Combed Cotton</h4>
                        <p>Ultra-breathable hypoallergenic fabrics safe for baby skin.</p>
                    </div>
                </div>

                <div class="trust-item">
                    <div class="trust-icon">🔄</div>
                    <div class="trust-info">
                        <h4>7-Day Easy Exchange</h4>
                        <p>Hassle-free size exchange and return support.</p>
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
