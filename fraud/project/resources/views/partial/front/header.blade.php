@php
    $startUrl = auth()->check() ? route('user.fraud.index') : route('login');
    $nav = [
        ['label' => 'হোম', 'href' => route('frontend.index'), 'active' => request()->routeIs('frontend.index')],
        ['label' => 'কুরিয়ার', 'href' => route('frontend.index').'#couriers'],
        ['label' => 'প্রাইসিং', 'href' => url('/priceing')],
        ['label' => 'ফিচার', 'href' => route('frontend.index').'#features'],
        ['label' => 'সাপোর্ট', 'href' => url('/contact')],
    ];
@endphp
<header class="site-header" id="siteHeader">
    <div class="container">
        <div class="header-inner">
            <a class="brand-logo" href="{{ route('frontend.index') }}">
                @if($gs && !empty($gs->logo))
                    <img src="{{ asset('assets/images/logo/'.$gs->logo) }}" alt="{{ $gs->title }}">
                @else
                    <img src="{{ asset('assets/front/img/bd/logo.png') }}" alt="{{ $gs->title ?? 'BD Courier' }}" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex'">
                    <span class="fallback" style="display:none"><i class="fas fa-shield-alt"></i> {{ $gs->title ?? 'BD Courier' }}</span>
                @endif
            </a>

            <nav class="nav-pill" aria-label="Main">
                @foreach($nav as $item)
                    <a href="{{ $item['href'] }}" class="{{ !empty($item['active']) ? 'active' : '' }}">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="header-actions">
                @auth
                    <a href="{{ route('user.dashboard') }}" class="btn btn-ghost btn-sm">ড্যাশবোর্ড</a>
                    <a href="{{ route('user.fraud.index') }}" class="btn btn-primary btn-sm">চেক করুন <i class="fas fa-arrow-right"></i></a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">লগইন</a>
                    <a href="{{ $startUrl }}" class="btn btn-primary btn-sm">শুরু করুন <i class="fas fa-arrow-right"></i></a>
                @endauth
            </div>

            <button class="mobile-toggle" type="button" id="mobileToggle" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div class="mobile-menu" id="mobileMenu">
            @foreach($nav as $item)
                <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
            @endforeach
            <div class="m-actions">
                @auth
                    <a href="{{ route('user.dashboard') }}" class="btn btn-outline btn-md">ড্যাশবোর্ড</a>
                    <a href="{{ route('user.fraud.index') }}" class="btn btn-primary btn-md">চেক করুন</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline btn-md">লগইন</a>
                    <a href="{{ $startUrl }}" class="btn btn-primary btn-md">ফ্রি অ্যাকাউন্ট খুলুন</a>
                @endauth
            </div>
        </div>
    </div>
    <div id="google_translate_element" style="display:none;"></div>
</header>
