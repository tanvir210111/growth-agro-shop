@extends('layouts.front')

@section('contents')
<section class="py-5 bg-light min-vh-100 d-flex justify-content-center align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                
                {{-- মেইন ইনভয়েস কার্ড --}}
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden invoice-card">
                    
                    {{-- ১. হেডার সেকশন (স্ট্যাটাস অনুযায়ী কালার চেঞ্জ হবে) --}}
                    <div class="card-header border-0 p-4 p-md-5 text-center position-relative overflow-hidden {{ $orderStatus == 'Active' ? 'bg-gradient-success' : ($orderStatus == 'Pending' ? 'bg-gradient-warning' : 'bg-gradient-danger') }}">
                        {{-- ব্যাকগ্রাউন্ড ডেকোরেশন --}}
                        <div class="header-decoration"></div>
                        
                        <div class="position-relative z-1 text-white">
                            @if($orderStatus == 'Active')
                                <div class="status-icon mb-3"><i class="fas fa-check-circle fa-4x animate__animated animate__bounceIn"></i></div>
                                <h2 class="fw-bold mb-1">পেমেন্ট সফল হয়েছে!</h2>
                                <p class="opacity-75 mb-0">আপনার অর্ডারটি কনফার্ম করা হয়েছে</p>
                            @elseif($orderStatus == 'Pending')
                                <div class="status-icon mb-3"><i class="fas fa-clock fa-4x"></i></div>
                                <h2 class="fw-bold mb-1">পেমেন্ট পেন্ডিং</h2>
                                <p class="opacity-75 mb-0">অ্যাডমিন কনফার্মেশনের অপেক্ষায়</p>
                            @else
                                <div class="status-icon mb-3"><i class="fas fa-times-circle fa-4x"></i></div>
                                <h2 class="fw-bold mb-1">পেমেন্ট ব্যর্থ</h2>
                                <p class="opacity-75 mb-0">দুঃখিত, লেনদেনটি সম্পন্ন হয়নি</p>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5 bg-white">
                        
                        {{-- ২. কাস্টমার ও ইনভয়েস তথ্য --}}
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <h6 class="text-uppercase text-muted small fw-bold mb-2">গ্রাহক তথ্য</h6>
                                <h5 class="fw-bold text-dark mb-1">{{ $order->customer->name ?? 'Guest' }}</h5>
                                <p class="text-muted small mb-0">{{ $order->customer->phone ?? 'N/A' }}</p>
                                <p class="text-muted small mb-0">{{ $order->customer->address ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h6 class="text-uppercase text-muted small fw-bold mb-2">অর্ডার তথ্য</h6>
                                <p class="mb-1"><span class="text-muted">ইনভয়েস:</span> <span class="fw-bold text-dark">#{{ $order->order_number }}</span></p>
                                <p class="mb-1"><span class="text-muted">তারিখ:</span> <span class="fw-bold text-dark">{{ $order->created_at->format('d M, Y') }}</span></p>
                                <p class="mb-0"><span class="text-muted">মেথড:</span> <span class="badge bg-light text-dark border">{{ $order->payment_method }}</span></p>
                            </div>
                        </div>

                        {{-- ৩. আইটেম টেবিল --}}
                        <div class="table-responsive mb-4">
                            <table class="table table-borderless table-striped rounded-3 overflow-hidden">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-3 ps-4 text-secondary text-uppercase small">বিবরণ</th>
                                        <th class="py-3 text-secondary text-uppercase small text-end">ধরণ</th>
                                        <th class="py-3 pe-4 text-secondary text-uppercase small text-end">টাকা</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- হোস্টিং প্যাকেজ --}}
                                    @if($hostingPlan)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $hostingPlan->name }}</div>
                                            <div class="small text-muted">Hosting Package (Yearly)</div>
                                        </td>
                                        <td class="text-end"><span class="badge bg-info bg-opacity-10 text-info">Hosting</span></td>
                                        <td class="pe-4 text-end fw-bold">৳ {{ number_format($hostingPlan->price_yearly, 2) }}</td>
                                    </tr>
                                    @endif

                                    {{-- ডোমেইন --}}
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $order->description }}</div>
                                            
                                            {{-- [LOGIC FIX] ডেসক্রিপশন চেক করে সাব-টাইটেল ঠিক করা --}}
                                            <div class="small text-muted">
                                                @if(\Illuminate\Support\Str::contains($order->description, 'Hosting Order'))
                                                    Linked / Existing Domain
                                                @else
                                                    Domain Registration (1 Year)
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end"><span class="badge bg-primary bg-opacity-10 text-primary">Domain</span></td>
                                        <td class="pe-4 text-end fw-bold">
                                            @php
                                                // ডোমেইন প্রাইস বের করা (মোট টাকা থেকে হোস্টিং বাদ দিয়ে)
                                                $hostingPrice = $hostingPlan ? $hostingPlan->price_yearly : 0;
                                                $domainPrice = $order->total_amount - $hostingPrice;
                                            @endphp
                                            
                                            @if($domainPrice <= 0)
                                                <span class="text-success">ফ্রি / অন্তর্ভুক্ত</span>
                                            @else
                                                ৳ {{ number_format($domainPrice, 2) }}
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- ৪. পেমেন্ট সামারি (টোটাল, পেইড, ডিউ) --}}
                        <div class="row justify-content-end">
                            <div class="col-md-6 col-lg-5">
                                <div class="bg-light p-3 rounded-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">সাব-টোটাল</span>
                                        <span class="fw-bold">৳ {{ number_format($order->total_amount, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2 border-bottom pb-2">
                                        <span class="text-muted">ডিসকাউন্ট</span>
                                        <span class="fw-bold">৳ 0.00</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="h6 mb-0 text-dark">সর্বমোট</span>
                                        <span class="h6 mb-0 text-dark">৳ {{ number_format($order->total_amount, 2) }}</span>
                                    </div>
                                    
                                    {{-- পেইড এবং ডিউ হাইলাইট --}}
                                    <div class="d-flex justify-content-between align-items-center mb-1 mt-2">
                                        <span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> জমা (Paid)</span>
                                        <span class="text-success fw-bold">৳ {{ number_format($order->paid_amount, 2) }}</span>
                                    </div>

                                    @if($order->due_amount > 0)
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="text-danger fw-bold"><i class="fas fa-exclamation-circle me-1"></i> বকেয়া (Due)</span>
                                        <span class="text-danger fw-bold">৳ {{ number_format($order->due_amount, 2) }}</span>
                                    </div>
                                    @else
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="text-muted small">বকেয়া (Due)</span>
                                        <span class="text-muted small">৳ 0.00</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- ৫. ফুটার বাটনস --}}
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-5 pt-3 border-top no-print">
                            <div class="mb-3 mb-md-0">
                                <a href="{{ route('frontend.invoice.live', $order->order_number) }}?token={{ $order->hash_token }}" target="_blank" class="text-decoration-none fw-bold text-primary small">
                                    <i class="fas fa-external-link-alt me-1"></i> অনলাইন কপি দেখুন
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('user.dashboard') }}" class="btn btn-light border fw-bold px-4 rounded-pill">
                                    ড্যাশবোর্ড
                                </a>
                                <button onclick="window.print()" class="btn btn-dark fw-bold px-4 rounded-pill">
                                    <i class="fas fa-print me-2"></i> প্রিন্ট ইনভয়েস
                                </button>
                            </div>
                        </div>

                        @if($orderStatus == 'Cancelled')
                        <div class="mt-3 text-center no-print">
                            <a href="{{ route('front.domain.search') }}" class="btn btn-outline-danger w-100 rounded-pill">পেমেন্ট ফেইল? আবার চেষ্টা করুন</a>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .bg-gradient-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .bg-gradient-warning { background: linear-gradient(135deg, #fce38a 0%, #f38181 100%); }
    .bg-gradient-danger { background: linear-gradient(135deg, #cb2d3e 0%, #ef473a 100%); }
    
    /* হেডারে হালকা শ্যাডো ইফেক্ট */
    .header-decoration {
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
        transform: rotate(30deg);
    }
    
    .rounded-4 { border-radius: 1rem !important; }
    
    /* টেবিল ডিজাইন */
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.02);
    }
    
    /* প্রিন্ট করার সময় বাটন হাইড করা */
    @media print {
        .no-print, .navbar, footer { display: none !important; }
        body { background: #fff !important; }
        .invoice-card { box-shadow: none !important; border: 1px solid #ddd !important; }
        .bg-gradient-success, .bg-gradient-warning, .bg-gradient-danger { 
            background: #fff !important; 
            color: #000 !important; 
            border-bottom: 1px solid #ddd; 
        }
        .text-white { color: #000 !important; }
    }
</style>
@endsection