@extends('layouts.front')

@section('meta')
    {{-- ১. সাধারণ এসইও ট্যাগ (Search Engine Optimization) --}}
    <title>{{ __('Service') }} - {{ $gs->title }}</title>
    <meta name="description" content="আপনার অনলাইন ব্যবসার নির্ভরযোগ্য সহযোগী">
    <meta name="keywords" content="service,our service,সার্ভিস,about us,aboutus,আমাদের সম্পর্কে,Creative design,creativedesign,www.creativedesign.com.bd,creativedesign.com.bd,web designer in bd,web designer in bangladesh,web designer in lalmonir hat,web designer in rangpur,graphics design,graphics designer in bd,graphics designer in lalmonir hat,graphics designer in rangpur,seo expert in bd,seo expert in lalmonir hat,seo expert in rangpur,Laravel expert in bd,Laravel expert in lalmonir hat,Laravel expert in ranpur, news protal in lalmonir hat,Laravel expert in rangpur,news protal,newsprotal,laravel newsprotal in bd,news protal in bd,laravel bangla news protal,laravel ecommerce,laravel ecommerce in bd,ecommece,commerce,bd ecommerce,laravelecommerce,woocommerce,wordpress ecommerce,wordpress wocommerce,ecommerce website,ecommerce website in bd,ecommerce website inb lalmonir hat,wordpress news protal,news theme,laravel teme,laravel news protal theme,theme,bd theme,themebazar,epaper theme,laravel epaper,it solution bd.it solution,itsolutionbd,shadinitsolution,shadhin it solution,sadhinitsolution,www.shadinitsolution.com,shadhinitsolution.com,webdesigner in bhola,software developer in bhola,graphics designer in bhola">
    <meta name="author" content="{{ __('Service') }} - {{ $gs->title }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- ২. ওপেন গ্রাফ ট্যাগ (Facebook, LinkedIn, WhatsApp-এ শেয়ার করার জন্য) --}}
    <meta property="og:title" content="{{ __('Service') }} - {{ $gs->title }}" />
    <meta property="og:description" content="আপনার অনলাইন ব্যবসার নির্ভরযোগ্য সহযোগী" />
    {{-- এখানে og:image এর জন্য একটি ডিফল্ট ব্যানার ইমেজ ব্যবহার করা ভালো --}}
    <meta property="og:image" content="https://www.creativedesign.com.bd/assets/images/logo/service.webp" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{ __('Service') }} - {{ $gs->title }}" />

    {{-- ৩. টুইটার কার্ড ট্যাগ (Twitter-এর জন্য) --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('Service') }} - {{ $gs->title }}">
    <meta name="twitter:description" content="আপনার অনলাইন ব্যবসার নির্ভরযোগ্য সহযোগী">
    <meta name="twitter:image" content="https://www.creativedesign.com.bd/assets/images/logo/service.webp">

@endsection

@section('contents')

    @php
        $hero = \DB::table('about_section_contents')->where('id', 1)->first();
    @endphp

    <section class="page-hero">
        <div class="container">
            <h1>
                @if(!empty($hero->title))
                    {!! str_replace('নির্ভরযোগ্য', '<span>নির্ভরযোগ্য</span>', strip_tags($hero->title, '<span><br><strong><em>')) !!}
                @else
                    আমাদের প্রিমিয়াম <span>সেবাসমূহ</span>
                @endif
            </h1>
            <p>{{ $hero->subtitle ?? 'আমরা দিচ্ছি প্রিমিয়াম কোয়ালিটির ই-কমার্স ওয়েবসাইট, ডিজিটাল মার্কেটিং এবং সফটওয়্যার সলিউশন।' }}</p>
        </div>
    </section>

    <section id="our-services" class="py-5 bg-light">
        <div class="container-fluid px-lg-5 py-4">
            <div class="text-center mb-5">
                <h6 class="fw-bold text-uppercase" style="letter-spacing: 2px; color: #ff6600;">আমাদের দক্ষতা</h6>
                <h2 class="fw-bold" style="color: #1a1a1a;">আপনার ব্যবসার পূর্ণাঙ্গ সমাধান</h2>
                <div class="mx-auto mt-2" style="width: 60px; height: 3px; background: #ff6600;"></div>
            </div>

            <div class="row g-4">
                @php
                    $services = App\Models\Service::orderBy('id', 'desc')->get();
                @endphp

                @forelse($services as $service)
                    {{-- col-xl-3 মানে এক লাইনে ৪টি কার্ড --}}
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                        <div class="card h-100 border-0 shadow-sm p-4 text-center orange-hover-card">
                            <div class="icon-container mb-3 mx-auto d-flex align-items-center justify-content-center">
                                <i class="{{ $service->icon }}"></i>
                            </div>
                            <h5 class="fw-bold mb-2 card-title-text">{{ $service->title }}</h5>
                            <p class="text-muted mb-0" style="font-size: 0.9rem; line-height: 1.6;">
                                {{ $service->description }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">বর্তমানে কোনো সেবা যুক্ত করা নেই।</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="py-5 text-center text-white" style="background: #ff6600;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h3 class="fw-bold mb-3">আপনার ব্যবসার গ্রোথ পার্টনার হতে আমরা প্রস্তুত</h3>
                    <p class="mb-4 opacity-100">সব সমাধান এক ছাদের নিচে - আজই শুরু করুন!</p>
                    <a href="{{ URL::to('/freeconsultant') }}" class="btn btn-dark btn-lg rounded-pill px-5 fw-bold shadow">ফ্রি কনসালটেন্সি নিন</a>
                </div>
            </div>
        </div>
    </section>

    <style>
        :root {
            --brand-orange: #ff6600;
            --brand-dark: #1a1a1a;
        }
        
        .orange-hover-card {
            transition: all 0.3s ease;
            border-radius: 12px;
            background: #fff;
            border-bottom: 3px solid transparent !important;
        }
        
        .orange-hover-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(255, 102, 0, 0.1) !important;
            border-bottom: 3px solid var(--brand-orange) !important;
        }
        
        .icon-container {
            width: 65px;
            height: 65px;
            background: rgba(255, 102, 0, 0.08);
            color: var(--brand-orange);
            border-radius: 50%;
            font-size: 28px;
            transition: 0.3s;
        }
        
        .orange-hover-card:hover .icon-container {
            background: var(--brand-orange);
            color: #fff;
            transform: scale(1.1) rotate(5deg);
        }

        .card-title-text {
            color: var(--brand-dark);
            transition: 0.3s;
        }

        .orange-hover-card:hover .card-title-text {
            color: var(--brand-orange);
        }

        /* Hero Text Animation */
        .hero-section h1 {
            letter-spacing: -1px;
        }
    </style>

@endsection