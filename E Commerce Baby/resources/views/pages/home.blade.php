@extends('layouts.app')

@section('title', \App\Models\Setting::get('site_title', 'Growth Agro | Universal E-Commerce & Premium Products'))

@section('content')

    <!-- Hero Banner Slider -->
    <section class="hero-slider-section">
        <div class="container">
            <div class="hero-slider-container" style="border-radius: var(--radius-lg); overflow: hidden; background: linear-gradient(135deg, #FFF7ED 0%, #FFEDD5 100%); min-height: 380px; position: relative; border: 1px solid #FED7AA;">
                @php
                    $slides = !empty($sliders) ? $sliders : [];
                @endphp

                @if(!empty($slides))
                    @foreach($slides as $index => $slide)
                        @php
                            $slideImg = !empty($slide['image']) ? $slide['image'] : (!empty($slide['banner_image']) ? $slide['banner_image'] : '');
                            $slideLink = !empty($slide['link']) ? $slide['link'] : route('shop');
                            $slideTitle = !empty($slide['title']) ? $slide['title'] : 'Banner';
                        @endphp
                        <div class="hero-slide {{ $index === 0 ? 'active' : '' }}">
                            <a href="{{ $slideLink }}" style="display:block; width:100%; height:100%;">
                                @if(!empty($slideImg))
                                    <img src="{{ \Illuminate\Support\Str::startsWith($slideImg, ['http://', 'https://', '/']) ? $slideImg : asset($slideImg) }}" alt="{{ $slideTitle }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div style="padding: 3.5rem 2.5rem; max-width: 600px;">
                                        <span style="display: inline-block; font-size: 0.85rem; font-weight: 700; color: #EA580C; background: #FFEDD5; padding: 4px 12px; border-radius: 9999px; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">Special Offer</span>
                                        <h1 style="font-family: var(--font-heading); font-size: 2.5rem; font-weight: 800; color: #0F172A; line-height: 1.2; margin-bottom: 1rem;">{{ $slideTitle }}</h1>
                                        <p style="color: #64748B; font-size: 1.05rem; margin-bottom: 1.75rem;">Discover amazing deals on all curated products with fast delivery.</p>
                                        <span class="btn-primary" style="padding: 0.85rem 2.2rem; font-size: 1rem; display: inline-flex;">SHOP NOW &rarr;</span>
                                    </div>
                                @endif
                            </a>
                        </div>
                    @endforeach
                @else
                    <!-- Clean Modern Fallback Hero -->
                    <div class="hero-slide active">
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 3.5rem 3rem; min-height: 380px; flex-wrap: wrap; gap: 2rem;">
                            <div style="max-width: 550px;">
                                <span style="display: inline-block; font-size: 0.82rem; font-weight: 800; color: #EA580C; background: #FFEDD5; padding: 4px 12px; border-radius: 9999px; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Trending Now
                                </span>
                                <h1 style="font-family: var(--font-heading); font-size: 2.6rem; font-weight: 800; color: #0F172A; line-height: 1.15; margin-bottom: 0.85rem;">
                                    SUPER SALE <br><span style="color: #EA580C;">UP TO 40% OFF</span>
                                </h1>
                                <p style="color: #64748B; font-size: 1.05rem; margin-bottom: 1.75rem; line-height: 1.5;">
                                    Discover amazing deals on all quality verified products with nationwide Cash on Delivery.
                                </p>
                                <a href="{{ route('shop') }}" class="btn-primary" style="padding: 0.9rem 2.5rem; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 14px rgba(234, 88, 12, 0.3);">
                                    SHOP NOW &rarr;
                                </a>
                            </div>
                            <div style="display: flex; gap: 1rem; align-items: center; justify-content: center; flex: 1; min-width: 260px;">
                                <div style="background: rgba(255,255,255,0.8); backdrop-filter: blur(8px); padding: 2rem; border-radius: var(--radius-lg); text-align: center; border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
                                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">🛍️</div>
                                    <div style="font-weight: 700; font-size: 1.1rem; color: #0F172A;">Quality Products</div>
                                    <div style="font-size: 0.85rem; color: #64748B;">Curated for your daily needs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if(!empty($slides) && count($slides) > 1)
                    <!-- Slider Controls -->
                    <button type="button" class="slider-nav-btn prev" id="heroPrevBtn" aria-label="Previous Slide">‹</button>
                    <button type="button" class="slider-nav-btn next" id="heroNextBtn" aria-label="Next Slide">›</button>

                    <div class="slider-dots">
                        @foreach($slides as $index => $slide)
                            <div class="slider-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}" title="{{ $slide['title'] ?? '' }}"></div>
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

            <div class="categories-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 1.25rem;">
                @forelse($collections as $col)
                    @php
                        $colImg = !empty($col['image']) ? $col['image'] : 'images/banners/all-collection.jpg';
                    @endphp
                    <a href="{{ route('collection.show', $col['handle']) }}" class="category-card" style="background: #fff; border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 1.25rem 0.75rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: all 0.2s ease;">
                        <div class="category-img-wrap" style="width: 60px; height: 60px; margin-bottom: 0.75rem; border-radius: 50%; background: #FFF7ED; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 4px;">
                            <img src="{{ \Illuminate\Support\Str::startsWith($colImg, ['http://', 'https://', '/']) ? $colImg : asset($colImg) }}" alt="{{ $col['title'] }}" loading="lazy" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>
                        <h4 class="category-title" style="font-size: 0.92rem; font-weight: 700; color: var(--color-text-main); margin-bottom: 0.2rem;">{{ $col['title'] }}</h4>
                        <span class="category-count" style="font-size: 0.75rem; color: #64748b;">{{ $col['item_count'] ?? 0 }} Items</span>
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
    <section class="products-section" style="background: #FAFAFA; padding: 3.5rem 0;">
        <div class="container">
            <div class="section-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
                <div>
                    <span class="section-subtitle" style="text-transform: uppercase; font-size: 0.75rem; font-weight: 800; color: #EA580C; letter-spacing: 0.05em;">POPULAR & TRENDING</span>
                    <h2 class="section-title" style="font-size: 1.75rem; font-weight: 800; margin: 0.2rem 0 0;">Proven Bestsellers</h2>
                </div>
                <div>
                    <a href="{{ route('shop') }}" style="color: #EA580C; font-weight: 700; font-size: 0.9rem; text-decoration: none;">
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
    <section class="trust-badges-section" style="padding: 3rem 0; background: #ffffff; border-top: 1px solid var(--color-border);">
        <div class="container">
            <div class="trust-badges-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <div class="trust-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: var(--radius-md); background: #F8FAFC; border: 1px solid var(--color-border);">
                    <div class="trust-icon" style="font-size: 2rem;">🚚</div>
                    <div class="trust-info">
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.15rem; color: #0F172A;">Cash on Delivery</h4>
                        <p style="font-size: 0.8rem; color: #64748B; margin: 0;">Pay when you receive</p>
                    </div>
                </div>

                <div class="trust-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: var(--radius-md); background: #F8FAFC; border: 1px solid var(--color-border);">
                    <div class="trust-icon" style="font-size: 2rem;">⚡</div>
                    <div class="trust-info">
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.15rem; color: #0F172A;">Fast Delivery</h4>
                        <p style="font-size: 0.8rem; color: #64748B; margin: 0;">Inside 1-3 working days</p>
                    </div>
                </div>

                <div class="trust-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: var(--radius-md); background: #F8FAFC; border: 1px solid var(--color-border);">
                    <div class="trust-icon" style="font-size: 2rem;">🔄</div>
                    <div class="trust-info">
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.15rem; color: #0F172A;">Easy Returns</h4>
                        <p style="font-size: 0.8rem; color: #64748B; margin: 0;">7-day easy return</p>
                    </div>
                </div>

                <div class="trust-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: var(--radius-md); background: #F8FAFC; border: 1px solid var(--color-border);">
                    <div class="trust-icon" style="font-size: 2rem;">🔒</div>
                    <div class="trust-info">
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.15rem; color: #0F172A;">100% Secure</h4>
                        <p style="font-size: 0.8rem; color: #64748B; margin: 0;">Secure verified payments</p>
                    </div>
                </div>

                <div class="trust-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: var(--radius-md); background: #F8FAFC; border: 1px solid var(--color-border);">
                    <div class="trust-icon" style="font-size: 2rem;">⭐</div>
                    <div class="trust-info">
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.15rem; color: #0F172A;">Best Quality</h4>
                        <p style="font-size: 0.8rem; color: #64748B; margin: 0;">Premium quality products</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
