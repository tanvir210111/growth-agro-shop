@extends('layouts.front')

@section('meta')
    {{-- ১. সাধারণ এসইও ট্যাগ (Search Engine Optimization) --}}
    <title>{{ __('Portfolio') }} - {{ $gs->title }}</title>
    <meta name="description" content="আমাদের সাম্প্রতিক কাজসমূহ">
    <meta name="keywords" content="portfolio,our port folio,service,our service,সার্ভিস,about us,aboutus,আমাদের সম্পর্কে,Creative design,creativedesign,www.creativedesign.com.bd,creativedesign.com.bd,web designer in bd,web designer in bangladesh,web designer in lalmonir hat,web designer in rangpur,graphics design,graphics designer in bd,graphics designer in lalmonir hat,graphics designer in rangpur,seo expert in bd,seo expert in lalmonir hat,seo expert in rangpur,Laravel expert in bd,Laravel expert in lalmonir hat,Laravel expert in ranpur, news protal in lalmonir hat,Laravel expert in rangpur,news protal,newsprotal,laravel newsprotal in bd,news protal in bd,laravel bangla news protal,laravel ecommerce,laravel ecommerce in bd,ecommece,commerce,bd ecommerce,laravelecommerce,woocommerce,wordpress ecommerce,wordpress wocommerce,ecommerce website,ecommerce website in bd,ecommerce website inb lalmonir hat,wordpress news protal,news theme,laravel teme,laravel news protal theme,theme,bd theme,themebazar,epaper theme,laravel epaper,it solution bd.it solution,itsolutionbd,shadinitsolution,shadhin it solution,sadhinitsolution,www.shadinitsolution.com,shadhinitsolution.com,webdesigner in bhola,software developer in bhola,graphics designer in bhola">
    <meta name="author" content="{{ __('Service') }} - {{ $gs->title }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- ২. ওপেন গ্রাফ ট্যাগ (Facebook, LinkedIn, WhatsApp-এ শেয়ার করার জন্য) --}}
    <meta property="og:title" content="{{ __('Portfolio') }} - {{ $gs->title }}" />
    <meta property="og:description" content="আমাদের সাম্প্রতিক কাজসমূহ" />
    {{-- এখানে og:image এর জন্য একটি ডিফল্ট ব্যানার ইমেজ ব্যবহার করা ভালো --}}
    <meta property="og:image" content="https://www.creativedesign.com.bd/assets/images/logo/port.webp" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{ __('Portfolio') }} - {{ $gs->title }}" />

    {{-- ৩. টুইটার কার্ড ট্যাগ (Twitter-এর জন্য) --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('Portfolio') }} - {{ $gs->title }}">
    <meta name="twitter:description" content="আমাদের সাম্প্রতিক কাজসমূহ">
    <meta name="twitter:image" content="https://www.creativedesign.com.bd/assets/images/logo/port.webp">
@endsection

@section('contents')

    @php
        // ১. ডাটাবেস থেকে ক্যাটাগরি এবং পোর্টফোলিও সংগ্রহ
        $portfolio_categories = \DB::table('portfolio_categories')->get();

        // পরিবর্তন: get() এর পরিবর্তে paginate(9) ব্যবহার করা হয়েছে (প্রতি পেজে ৯টি আইটেম দেখাবে)
        $portfolios = \App\Models\Portfolio::with('category')->orderBy('id', 'desc')->paginate(6);
    @endphp

    <section class="page-hero">
        <div class="container">
            <h1>আমাদের <span>সাম্প্রতিক</span> কাজসমূহ</h1>
            <p class="lead opacity-75">আমরা আমাদের ক্লায়েন্টদের জন্য সেরা মানের ডিজিটাল সলিউশন নিশ্চিত করি।</p>
        </div>
    </section>

    <section id="portfolio" class="py-5 bg-light position-relative" style="margin-top: -30px;">
        <div class="container">
            
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <div class="portfolio-filter">
                        <button class="btn-filter active" data-filter="*">সকল</button>
                        @foreach($portfolio_categories as $p_cat)
                            <button class="btn-filter" data-filter=".{{ $p_cat->slug }}">{{ $p_cat->name }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="row portfolio-container g-4">
                @forelse($portfolios as $portfolio)
                <div class="col-lg-4 col-md-6 portfolio-item {{ $portfolio->category->slug }}">
                    <div class="card border-0 shadow-sm overflow-hidden h-100 portfolio-wrapper">
                        <div class="portfolio-img-box position-relative">
                            <img src="{{ asset('assets/images/portfolio/'.$portfolio->photo) }}" class="img-fluid w-100" alt="{{ $portfolio->title }}">
                            
                            <div class="portfolio-overlay d-flex flex-column align-items-center justify-content-center">
                                <h5 class="text-white fw-bold mb-1">{{ $portfolio->title }}</h5>
                                <p class="text-white-50 small mb-3">{{ $portfolio->category->name }}</p>
                                <a href="{{ $portfolio->url }}" target="_blank" class="btn btn-light btn-sm rounded-pill px-4 fw-bold text-orange">
                                    <i class="fas fa-link me-1"></i> লাইভ ভিজিট
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted fs-5">বর্তমানে কোনো প্রজেক্ট পাওয়া যায়নি।</p>
                </div>
                @endforelse
            </div>

            {{-- ২. পেজিনেশন লিঙ্ক যুক্ত করা হয়েছে --}}
            <div class="row mt-5">
                <div class="col-12 d-flex justify-content-center">
                    {{-- Bootstrap 5 পেজিনেশন স্টাইল ব্যবহার করা হয়েছে --}}
                    {{ $portfolios->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>
    </section>

    <section class="py-5 bg-white">
        <div class="container py-4 text-center">
            <div class="custom-package-card p-5 shadow-sm rounded-4 border mx-auto" style="max-width: 850px; border-radius: 20px;">
                <h2 class="fw-bold mb-3" style="color: #222;">আপনার প্রজেক্ট শুরু করতে চান?</h2>
                <p class="text-muted mb-4 fs-5">আমাদের দক্ষ টিমের সাথে আপনার আইডিয়া শেয়ার করুন এবং আজই শুরু করুন।</p>
                <a href="{{ url('/contact') }}" class="btn btn-outline-dark btn-lg px-5 py-2 rounded-pill fw-bold" style="border-width: 1.5px;">যোগাযোগ করুন</a>
            </div>
        </div>
    </section>

    <style>
        .portfolio-filter button {
            background: #fff;
            border: 1px solid #ddd;
            padding: 10px 25px;
            margin: 5px;
            border-radius: 50px;
            font-weight: 600;
            transition: 0.3s;
            color: #555;
            cursor: pointer;
        }
        .portfolio-filter button.active, .portfolio-filter button:hover {
            background: #ff6600;
            color: #fff;
            border-color: #ff6600;
        }

        .portfolio-wrapper { border-radius: 15px; transition: 0.4s; }
        .portfolio-img-box img { height: 280px; object-fit: cover; transition: 0.5s; }
        .portfolio-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 102, 0, 0.9); opacity: 0;
            transition: 0.4s ease-in-out; transform: translateY(20px);
        }
        .portfolio-item:hover .portfolio-overlay { opacity: 1; transform: translateY(0); }
        .portfolio-item:hover img { transform: scale(1.1); }
        .text-orange { color: #ff6600 !important; }

        /* পেজিনেশন কাস্টম স্টাইল (আপনার থিমের কালার অনুযায়ী) */
        .pagination .page-link {
            color: #333;
            border-radius: 50%;
            margin: 0 5px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ddd;
        }
        .pagination .active .page-link {
            background-color: #ff6600;
            border-color: #ff6600;
            color: white;
        }
        .pagination .page-link:hover {
            background-color: #eee;
            color: #ff6600;
        }
    </style>

@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js"></script>

    <script>
        $(window).on('load', function() {
            var $grid = $('.portfolio-container').isotope({
                itemSelector: '.portfolio-item',
                layoutMode: 'fitRows',
                percentPosition: true
            });

            $('.portfolio-filter').on('click', 'button', function() {
                $('.portfolio-filter button').removeClass('active');
                $(this).addClass('active');

                var filterValue = $(this).attr('data-filter');
                $grid.isotope({ filter: filterValue });
            });
        });
    </script>
@endsection