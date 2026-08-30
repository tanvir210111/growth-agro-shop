@extends('layouts.front')

@section('meta')
    {{-- ১. সাধারণ এসইও ট্যাগ (Search Engine Optimization) --}}
    <title>{{ __('Our Team') }} - {{ $gs->title }}</title>
    <meta name="description" content="আপনার ব্যবসার জন্য সঠিক প্যাকেজটি বেছে নিন। আমরা দিচ্ছি সাশ্রয়ী মূল্যে সেরা সার্ভিস।">
    <meta name="keywords" content="our team,team,my team,price plan,price plan,price,portfolio,our port folio,service,our service,সার্ভিস,about us,aboutus,আমাদের সম্পর্কে,Creative design,creativedesign,www.creativedesign.com.bd,creativedesign.com.bd,web designer in bd,web designer in bangladesh,web designer in lalmonir hat,web designer in rangpur,graphics design,graphics designer in bd,graphics designer in lalmonir hat,graphics designer in rangpur,seo expert in bd,seo expert in lalmonir hat,seo expert in rangpur,Laravel expert in bd,Laravel expert in lalmonir hat,Laravel expert in ranpur, news protal in lalmonir hat,Laravel expert in rangpur,news protal,newsprotal,laravel newsprotal in bd,news protal in bd,laravel bangla news protal,laravel ecommerce,laravel ecommerce in bd,ecommece,commerce,bd ecommerce,laravelecommerce,woocommerce,wordpress ecommerce,wordpress wocommerce,ecommerce website,ecommerce website in bd,ecommerce website inb lalmonir hat,wordpress news protal,news theme,laravel teme,laravel news protal theme,theme,bd theme,themebazar,epaper theme,laravel epaper,it solution bd.it solution,itsolutionbd,shadinitsolution,shadhin it solution,sadhinitsolution,www.shadinitsolution.com,shadhinitsolution.com,webdesigner in bhola,software developer in bhola,graphics designer in bhola">
    <meta name="author" content="{{ __('Our Team') }} - {{ $gs->title }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- ২. ওপেন গ্রাফ ট্যাগ (Facebook, LinkedIn, WhatsApp-এ শেয়ার করার জন্য) --}}
    <meta property="og:title" content="{{ __('Our Team') }} - {{ $gs->title }}" />
    <meta property="og:description" content="আপনার ব্যবসার জন্য সঠিক প্যাকেজটি বেছে নিন। আমরা দিচ্ছি সাশ্রয়ী মূল্যে সেরা সার্ভিস।" />
    {{-- এখানে og:image এর জন্য একটি ডিফল্ট ব্যানার ইমেজ ব্যবহার করা ভালো --}}
    <meta property="og:image" content="https://www.creativedesign.com.bd/assets/images/logo/team.png" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{ __('Our Team') }} - {{ $gs->title }}" />

    {{-- ৩. টুইটার কার্ড ট্যাগ (Twitter-এর জন্য) --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('Our Team') }} - {{ $gs->title }}">
    <meta name="twitter:description" content="আপনার ব্যবসার জন্য সঠিক প্যাকেজটি বেছে নিন। আমরা দিচ্ছি সাশ্রয়ী মূল্যে সেরা সার্ভিস।">
    <meta name="twitter:image" content="https://www.creativedesign.com.bd/assets/images/logo/team.png">
@endsection

@section('contents')

    <section class="page-hero">
        <div class="container">
            <h1>আমাদের <span>দক্ষ</span> টিম</h1>
            <p class="lead opacity-75">অভিজ্ঞ টিম নিয়ে আমরা দিচ্ছি আপনার ব্যবসার সেরা ডিজিটাল সমাধান।</p>
        </div>
    </section>

    <section class="py-5 bg-light position-relative" style="margin-top: -50px;">
        <div class="container">
            <div class="row g-4 justify-content-center">
                
                @forelse($teams as $team)
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="team-card shadow-lg border-0 text-center">
                        <div class="image-box position-relative overflow-hidden">
                            {{-- ডাটাবেসের ছবি প্রদর্শন --}}
                            <img src="{{ asset('assets/images/team/'.$team->photo) }}" class="img-fluid" alt="{{ $team->name }}">
                            
                            {{-- হোভার করলে সোশ্যাল লিঙ্ক দেখাবে --}}
                            <div class="social-overlay d-flex align-items-center justify-content-center">
                                @if($team->facebook)
                                    <a href="{{ $team->facebook }}" target="_blank" class="mx-2"><i class="fab fa-facebook-f"></i></a>
                                @endif
                                @if($team->twitter)
                                    <a href="{{ $team->twitter }}" target="_blank" class="mx-2"><i class="fab fa-twitter"></i></a>
                                @endif
                                @if($team->linkedin)
                                    <a href="{{ $team->linkedin }}" target="_blank" class="mx-2"><i class="fab fa-linkedin-in"></i></a>
                                @endif
                            </div>
                        </div>
                        
                        <div class="info-box p-4 bg-white">
                            <h5 class="fw-bold mb-1 text-dark">{{ $team->name }}</h5>
                            <p class="small text-uppercase fw-bold" style="color: #ff6600;">{{ $team->designation }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">বর্তমানে কোনো টিম মেম্বার তথ্য পাওয়া যায়নি।</p>
                </div>
                @endforelse

            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container py-4 text-center">
            <div class="p-5 shadow-sm rounded-4 border mx-auto" style="max-width: 900px; border-radius: 20px;">
                <h2 class="fw-bold mb-3" style="color: #222;">কাস্টম প্যাকেজ প্রয়োজন?</h2>
                <p class="text-muted mb-4 fs-5">আপনার যদি বিশেষ কোনো চাহিদা থাকে তবে আমাদের সরাসরি কল দিন অথবা মেসেজ করুন।</p>
                <a href="{{ url('/contact') }}" class="btn btn-outline-dark btn-lg px-5 py-2 rounded-pill fw-bold" style="border-width: 1.5px;">যোগাযোগ করুন</a>
            </div>
        </div>
    </section>

    <style>
        .team-card {
            border-radius: 20px;
            overflow: hidden;
            transition: 0.4s;
            background: #fff;
        }

        .image-box {
            height: 320px;
            background: #f8f9fa;
        }

        .image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.5s;
        }

        .team-card:hover .image-box img {
            transform: scale(1.1);
        }

        .social-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 102, 0, 0.85); /* অরেঞ্জ ওভারলে */
            opacity: 0;
            transition: 0.4s;
        }

        .team-card:hover .social-overlay {
            opacity: 1;
        }

        .social-overlay a {
            width: 40px;
            height: 40px;
            background: #fff;
            color: #ff6600;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: 0.3s;
            font-size: 18px;
        }

        .social-overlay a:hover {
            background: #333;
            color: #fff;
            transform: translateY(-5px);
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
        }

        .info-box {
            border-top: 1px solid #f1f1f1;
        }
    </style>

@endsection