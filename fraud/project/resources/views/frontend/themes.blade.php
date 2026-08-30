{{-- আপনার লেআউটের নাম frontend.layout বা layouts.front যা সঠিক তা ব্যবহার করুন --}}
@extends('layouts.front') 

@section('meta')
      {{-- ১. সাধারণ এসইও ট্যাগ (Search Engine Optimization) --}}
    <title>{{ __('Product') }} - {{ $gs->title }}</title>
    <meta name="description" content="আপনার ব্যবসার জন্য আধুনিক এবং রেসপনসিভ ডিজাইন বেছে নিন।">
    <meta name="keywords" content="Product,laravel theme,theme,price plan,price plan,price,portfolio,our port folio,service,our service,সার্ভিস,about us,aboutus,আমাদের সম্পর্কে,Creative design,creativedesign,www.creativedesign.com.bd,creativedesign.com.bd,web designer in bd,web designer in bangladesh,web designer in lalmonir hat,web designer in rangpur,graphics design,graphics designer in bd,graphics designer in lalmonir hat,graphics designer in rangpur,seo expert in bd,seo expert in lalmonir hat,seo expert in rangpur,Laravel expert in bd,Laravel expert in lalmonir hat,Laravel expert in ranpur, news protal in lalmonir hat,Laravel expert in rangpur,news protal,newsprotal,laravel newsprotal in bd,news protal in bd,laravel bangla news protal,laravel ecommerce,laravel ecommerce in bd,ecommece,commerce,bd ecommerce,laravelecommerce,woocommerce,wordpress ecommerce,wordpress wocommerce,ecommerce website,ecommerce website in bd,ecommerce website inb lalmonir hat,wordpress news protal,news theme,laravel teme,laravel news protal theme,theme,bd theme,themebazar,epaper theme,laravel epaper,it solution bd.it solution,itsolutionbd,shadinitsolution,shadhin it solution,sadhinitsolution,www.shadinitsolution.com,shadhinitsolution.com,webdesigner in bhola,software developer in bhola,graphics designer in bhola">
    <meta name="author" content="{{ __('Service') }} - {{ $gs->title }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- ২. ওপেন গ্রাফ ট্যাগ (Facebook, LinkedIn, WhatsApp-এ শেয়ার করার জন্য) --}}
    <meta property="og:title" content="{{ __('Product') }} - {{ $gs->title }}" />
    <meta property="og:description" content="আপনার ব্যবসার জন্য আধুনিক এবং রেসপনসিভ ডিজাইন বেছে নিন।" />
    {{-- এখানে og:image এর জন্য একটি ডিফল্ট ব্যানার ইমেজ ব্যবহার করা ভালো --}}
    <meta property="og:image" content="https://www.creativedesign.com.bd/assets/images/logo/theme.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{ __('Product') }} - {{ $gs->title }}" />

    {{-- ৩. টুইটার কার্ড ট্যাগ (Twitter-এর জন্য) --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('Product') }} - {{ $gs->title }}">
    <meta name="twitter:description" content="আপনার ব্যবসার জন্য আধুনিক এবং রেসপনসিভ ডিজাইন বেছে নিন।">
    <meta name="twitter:image" content="https://www.creativedesign.com.bd/assets/images/logo/theme.jpg">
@endsection

@section('contents')
<section class="page-hero">
    <div class="container">
        <h1>আমাদের প্রিমিয়াম <span>থিমসমূহ</span></h1>
        <p>আপনার ব্যবসার জন্য আধুনিক এবং রেসপনসিভ ডিজাইন বেছে নিন।</p>
    </div>
</section>

<section class="section-pad bg-surface">
    <div class="container">

        <div class="row g-4">
            @php 
                // ডাটাবেস থেকে সকল একটিভ প্রোডাক্ট পেজিনেট করা হচ্ছে (প্রতি পেজে ৯টি করে)
                // ইমেজ দ্রুত লোড করার জন্য এবং ডাটাবেস কুয়েরি অপ্টিমাইজ করতে paginate ব্যবহার করা হয়েছে।
                $products = \App\Models\Product::where('status', 1)->orderBy('id', 'desc')->paginate(9); 
            @endphp
            
            @forelse($products as $product)
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 theme-card">
                    <div class="position-relative overflow-hidden">
                        {{-- ইমেজ দ্রুত লোড হওয়ার জন্য loading="lazy" এবং টাইটেল ও অল্ট ট্যাগ ব্যবহার করা হয়েছে --}}
                        <img src="{{ asset('assets/images/products/'.$product->photo) }}" 
                             class="card-img-top" 
                             style="height: 230px; object-fit: cover;" 
                             alt="{{ $product->name }}"
                             loading="lazy">
                        <div class="price-badge">মূল্যঃ ৳ {{ $product->price }}</div>
                    </div>
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold mb-2">{{ $product->name }}</h5>
                        <p class="text-muted small mb-4">{{ Str::limit($product->description, 80) }}</p>
                        
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('frontend.product.details', $product->slug) }}" class="btn btn-outline-dark rounded-pill px-3 fw-bold btn-sm">বিস্তারিত</a>
                            <a href="{{ $product->purchase_link }}" target="_blank" class="btn btn-orange rounded-pill px-4 fw-bold btn-sm">কিনুন</a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <h4 class="text-muted">বর্তমানে কোনো থিম পাওয়া যায়নি।</h4>
            </div>
            @endforelse
        </div>

        {{-- পেজিনেশন লিঙ্ক দেখানোর জন্য --}}
        <div class="row mt-5">
            <div class="col-12 d-flex justify-content-center custom-pagination">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</section>

<style>
    .theme-card { transition: 0.3s; border: 1px solid #eee; }
    .theme-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; border-bottom: 3px solid #ff6600; }
    .price-badge {
        position: absolute; top: 15px; left: 15px;
        background: #ff6600; color: #fff;
        padding: 5px 15px; border-radius: 50px; font-weight: bold; font-size: 14px;
    }
    .btn-orange { background: #ff6600; color: #fff; border: none; }
    .btn-orange:hover { background: #333; color: #fff; }

    /* পেজিনেশন ডিজাইন বুটস্ট্র্যাপের সাথে সামঞ্জস্য করার জন্য */
    .custom-pagination svg { width: 20px; }
    .custom-pagination nav > div:first-child { display: none; }
    .custom-pagination nav { display: flex; justify-content: center; }
</style>
@endsection