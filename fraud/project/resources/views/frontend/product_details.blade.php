@extends('layouts.front')
@section('meta')
     {{-- ১. সাধারণ এসইও ট্যাগ (Search Engine Optimization) --}}
    <title>{{ $product->name }} - {{ $gs->title }}</title>
    <meta name="description" content="{{ $product->meta_description}}">
    <meta name="keywords" content="{{ $product->meta_keyword }}">
    <meta name="author" content="{{ $gs->title }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- ২. ওপেন গ্রাফ ট্যাগ (Facebook, LinkedIn, WhatsApp-এ শেয়ার করার জন্য) --}}
    <meta property="og:title" content="{{ $product->name }} - {{ $gs->title }}" />
    <meta property="og:description" content="{{ $product->meta_description}}" />
    {{-- এখানে og:image এর জন্য একটি ডিফল্ট ব্যানার ইমেজ ব্যবহার করা ভালো --}}
    <meta property="og:image" content="{{ asset('assets/images/products/'.$product->photo) }}" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{ $product->name }} - {{ $gs->title }}" />

    {{-- ৩. টুইটার কার্ড ট্যাগ (Twitter-এর জন্য) --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $product->name }} - {{ $gs->title }}">
    <meta name="twitter:description" content="{{ $product->meta_description}}">
    <meta name="twitter:image" content="{{ asset('assets/images/products/'.$product->photo) }}">
@endsection
@section('contents')
<section class="product-details-area py-5 bg-white">
    <div class="container py-lg-4">
        {{-- ব্রেডক্রাম্ব --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('frontend.index') }}" class="text-decoration-none text-muted">হোম</a></li>
                <li class="breadcrumb-item"><a href="{{ route('frontend.themes') }}" class="text-decoration-none text-muted">থিমসমূহ</a></li>
                <li class="breadcrumb-item active text-orange fw-bold" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="row g-5 align-items-start">
            {{-- ইমেজ সেকশন (col-lg-6 করা হয়েছে যাতে ইমেজটি বড় দেখায়) --}}
            <div class="col-lg-6">
                <div class="product-image-wrapper shadow-lg rounded-4 overflow-hidden border bg-light">
                    <img src="{{ asset('assets/images/products/'.$product->photo) }}" 
                         class="img-fluid w-100 main-product-img" 
                         style="max-height: 450px; width: 100%; object-fit: contain; padding: 10px;" 
                         alt="{{ $product->name }}">
                </div>
            </div>

            {{-- ইনফরমেশন সেকশন (col-lg-6) --}}
            <div class="col-lg-6">
                <div class="ps-lg-3">
                    <span class="badge bg-soft-orange text-orange mb-2 px-3 py-2 rounded-pill">প্রিমিয়াম প্রোডাক্ট</span>
                    <h1 class="fw-bold mb-3 text-dark">{{ $product->name }}</h1>
                    
                    <div class="price-box mb-4 d-flex align-items-center gap-3">
                        <h2 class="fw-bold mb-0 text-orange" style="font-size: 2.5rem;">৳ {{ $product->price }}</h2>
                        @if($product->previous_price)
                            <del class="text-muted fs-4">৳ {{ $product->previous_price }}</del>
                        @endif
                    </div>

                    <p class="text-muted mb-4 fs-5 leading-relaxed">{{ $product->description }}</p>

                    <div class="d-flex flex-wrap gap-3 mb-5">
                        <a href="{{ $product->demo_link }}" target="_blank" class="btn btn-dark btn-lg px-5 rounded-3 shadow-sm transition">
                            <i class="fas fa-eye me-2"></i> লাইভ ডেমো
                        </a>
                        <a href="{{ $product->purchase_link }}" target="_blank" class="btn btn-orange btn-lg px-5 rounded-3 shadow transition">
                            <i class="fas fa-shopping-cart me-2"></i> এখনই কিনুন
                        </a>
                    </div>

                    <div class="feature-card p-4 rounded-4 border bg-light shadow-sm">
                        <h6 class="fw-bold mb-3 text-dark"><i class="fas fa-star text-warning me-2"></i> বিশেষ সুবিধাসমূহ:</h6>
                        <ul class="list-unstyled mb-0 row">
                            <li class="col-6 mb-3 d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i> রেসপনসিভ ডিজাইন
                            </li>
                            <li class="col-6 mb-3 d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i> লাইফটাইম আপডেট
                            </li>
                            <li class="col-6 d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i> প্রিমিয়াম সাপোর্ট
                            </li>
                            <li class="col-6 d-flex align-items-center">
                                <i class="fas fa-check-circle text-success me-2"></i> এসইও ফ্রেন্ডলি
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5 pt-4">
            <div class="col-12">
                <div class="p-4 p-md-5 border rounded-4 bg-light shadow-sm">
                    <h3 class="fw-bold mb-4 border-bottom pb-3 text-dark">থিমের বিস্তারিত বিবরণ</h3>
                    <div class="details-content custom-html-content">
                        {!! $product->details !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .bg-soft-orange { background-color: rgba(255, 102, 0, 0.1); }
    .text-orange { color: #ff6600 !important; }
    .btn-orange { background: #ff6600 !important; color: #fff !important; border: none; font-weight: 600; }
    .btn-orange:hover { background: #e65c00 !important; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(255,102,0,0.2) !important; }
    .btn-dark:hover { transform: translateY(-3px); }
    .transition { transition: all 0.3s ease; }
    .main-product-img { transition: transform 0.5s ease; }
    .product-image-wrapper:hover .main-product-img { transform: scale(1.02); }
    .leading-relaxed { line-height: 1.7; }
    .custom-html-content { font-size: 1.1rem; line-height: 1.8; color: #333; }
    .custom-html-content img { max-width: 100%; height: auto !important; border-radius: 10px; margin: 15px 0; }
</style>
@endsection