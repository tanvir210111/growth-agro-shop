@extends('layouts.front')

@section('contents')
<section class="py-5 bg-light min-vh-100">
    <div class="container py-2 py-md-5">
        
        {{-- ১. প্রগ্রেস বার --}}
        <div class="row justify-content-center mb-5">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="position-relative">
                    <div class="progress" style="height: 2px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 50%;"></div>
                    </div>
                    <div class="d-flex justify-content-between position-absolute w-100 top-0 translate-middle-y">
                        <div class="text-center">
                            <div class="step-circle bg-success text-white border-0 shadow-sm"><i class="fas fa-check small"></i></div>
                            <p class="step-label fw-bold d-none d-sm-block">সার্চ</p>
                        </div>
                        <div class="text-center">
                            <div class="step-circle bg-primary text-white border-0 shadow-sm">2</div>
                            <p class="step-label fw-bold d-none d-sm-block">কনফিগারেশন</p>
                        </div>
                        <div class="text-center">
                            <div class="step-circle bg-white border text-muted shadow-sm">3</div>
                            <p class="step-label fw-bold d-none d-sm-block">পেমেন্ট</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- [MAIN LOGIC] এটি নিজের ডোমেইন কিনা তা চেক করা হচ্ছে --}}
        @php
            $isOwnDomain = (isset($isExistingDomain) && $isExistingDomain) || request()->get('type') == 'existing' || request()->get('domain_type') == 'existing';
        @endphp

        {{-- ২. মূল কন্টেন্ট --}}
        <div class="row g-4 align-items-start">
            
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-user-edit me-2 text-primary"></i>গ্রাহক তথ্য ও পেমেন্ট
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('front.domain.order') }}" method="POST" id="orderForm">
                            @csrf
                            <input type="hidden" name="domain" value="{{ $domain }}">
                            
                            {{-- [CRITICAL] যদি নিজের ডোমেইন হয়, তাহলে এই hidden input টি যাবে --}}
                            @if($isOwnDomain)
                                <input type="hidden" name="domain_type" value="existing">
                            @endif

                            {{-- হোস্টিং আইডি থাকলে সেটিও পাঠাবে --}}
                            @if($hostingPlan)
                                <input type="hidden" name="plan_id" value="{{ $hostingPlan->id }}">
                            @endif

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">আপনার পূর্ণ নাম</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light"><i class="fas fa-user text-muted"></i></span>
                                        <input type="text" name="customer_name" class="form-control border-0 bg-light py-2" 
                                               value="{{ old('customer_name', optional($user)->name) }}" required placeholder="আপনার নাম লিখুন">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">ফোন নাম্বার</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light"><i class="fas fa-phone-alt text-muted"></i></span>
                                        <input type="text" name="customer_phone" class="form-control border-0 bg-light py-2" 
                                               value="{{ old('customer_phone', optional($user)->phone) }}" required placeholder="আপনার ফোন নাম্বার">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">আপনার ঠিকানা</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light align-items-start pt-2"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                        <textarea name="customer_address" class="form-control border-0 bg-light py-2" rows="2" 
                                                  required placeholder="আপনার পূর্ণ ঠিকানা লিখুন">{{ old('customer_address', optional($user)->address) }}</textarea>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <h6 class="fw-bold text-dark mb-3">পেমেন্ট মেথড</h6>
                                    <div class="payment-selection-wrapper">
                                        <input type="radio" class="btn-check" name="payment_method" id="uddoktapay" value="UddoktaPay" checked required>
                                        <label class="btn btn-outline-light p-3 w-100 payment-card active-card" for="uddoktapay">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-white rounded p-2 me-3 shadow-sm border border-light">
                                                        <img src="{{ asset('assets/images/uddoktapay.png') }}" alt="UddoktaPay" class="img-fluid" style="width: 120px;">
                                                    </div>
                                                    <div class="text-start">
                                                        <b class="d-block text-dark h6 mb-1">অনলাইন পেমেন্ট (Gateway)</b>
                                                        <span class="text-muted small d-block">বিকাশ, নগদ, রকেট ও কার্ড পেমেন্ট</span>
                                                    </div>
                                                </div>
                                                <div class="check-icon"><i class="fas fa-check-circle text-success fa-2x"></i></div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 d-none d-lg-block text-end">
                                <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill fw-bold shadow-sm py-3">
                                    অর্ডার সম্পন্ন করুন <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ডান পাশ: অর্ডার সামারি --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 sticky-sidebar">
                    <div class="card-header bg-dark text-white py-3 border-0">
                        <h5 class="fw-bold mb-0">অর্ডার সামারি</h5>
                    </div>
                    <div class="card-body p-4">
                        
                        @php
                            // ১. প্রাইস ক্যালকুলেশন লজিক
                            $finalDomainPrice = 0;
                            
                            // যদি নতুন ডোমেইন হয়, তবেই প্রাইস সেট হবে
                            if (!$isOwnDomain && isset($extInfo)) {
                                $finalDomainPrice = $extInfo->registration_price;
                            }
                            
                            $freeLabel = false;

                            // ২. ফ্রি ডোমেইন চেক লজিক (শুধুমাত্র নতুন ডোমেইনের জন্য)
                            if(!$isOwnDomain && $hostingPlan && $hostingPlan->free_domain == 1 && isset($extInfo)) {
                                $rawExtIds = $hostingPlan->free_domain_extensions;
                                $allowedIds = [];

                                if (is_array($rawExtIds)) {
                                    $allowedIds = $rawExtIds;
                                } else {
                                    $decoded = json_decode($rawExtIds, true);
                                    $allowedIds = is_array($decoded) ? $decoded : explode(',', $rawExtIds);
                                }
                                
                                // এক্সটেনশন আইডি ম্যাচ করলে ফ্রি
                                if (in_array((string)$extInfo->id, array_map('strval', array_map('trim', $allowedIds)))) {
                                    $finalDomainPrice = 0;
                                    $freeLabel = true;
                                }
                            }

                            // ৩. মোট হিসাব
                            $grandTotal = $finalDomainPrice;
                            if($hostingPlan) {
                                $grandTotal += $hostingPlan->price_yearly;
                            }
                        @endphp

                        {{-- ডোমেইন ইনফো ডিসপ্লে --}}
                        <div class="text-center mb-4 border-bottom pb-3">
                            
                            {{-- [FIXED] ব্যাজ টাইটেল --}}
                            <span class="badge bg-soft-orange text-primary rounded-pill px-3 py-2 mb-2">
                                {{ $isOwnDomain ? 'হোস্টিং ডোমেইন' : 'নির্বাচিত ডোমেইন' }}
                            </span>

                            <h5 class="fw-bold text-dark mb-0 text-break">{{ $domain }}</h5>
                            
                            <div class="d-flex justify-content-between mt-3 px-2">
                                <span class="text-muted small">
                                    {{-- [FIXED] ডেসক্রিপশন টেক্সট --}}
                                    @if($isOwnDomain)
                                        <span class="text-info fw-bold"><i class="fas fa-check-circle"></i> বিদ্যমান (Existing)</span>
                                    @else
                                        রেজিস্ট্রেশন (১ বছর)
                                    @endif
                                </span>

                                @if($isOwnDomain)
                                    <span class="fw-bold text-success small">৳ ০.০০</span>
                                @elseif($freeLabel)
                                    <span class="fw-bold text-success small">৳ ০.০০ (ফ্রি)</span>
                                @else
                                    <span class="fw-bold text-dark small">৳ {{ number_format($extInfo->registration_price ?? 0, 2) }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- হোস্টিং ইনফো ডিসপ্লে --}}
                        @if($hostingPlan)
                        <div class="text-center mb-4 border-bottom pb-3">
                            <span class="badge bg-light text-success rounded-pill px-3 py-2 mb-2 border">নির্বাচিত হোস্টিং</span>
                            <h5 class="fw-bold text-dark mb-1">{{ $hostingPlan->name }}</h5>
                            <small class="text-muted d-block mb-3">ক্যাটাগরি: {{ $hostingPlan->category }}</small>
                            <div class="d-flex justify-content-between px-2">
                                <span class="text-muted small">হোস্টিং ফি (১ বছর)</span>
                                <span class="fw-bold text-dark small">৳ {{ number_format($hostingPlan->price_yearly, 2) }}</span>
                            </div>
                        </div>
                        @endif

                        {{-- সর্বমোট হিসাব --}}
                        <div class="d-flex justify-content-between align-items-center mb-4 mt-2 px-2">
                            <h5 class="fw-bold text-dark mb-0">সর্বমোট:</h5>
                            <h3 class="fw-bold text-primary mb-0">৳ {{ number_format($grandTotal, 2) }}</h3>
                        </div>

                        {{-- এলার্ট মেসেজ --}}
                        @if($freeLabel && !$isOwnDomain)
                        <div class="alert alert-success border-0 rounded-3 mb-4 small py-2 text-center">
                            <i class="fas fa-gift me-1"></i> অভিনন্দন! এই প্যাকেজের সাথে আপনার ডোমেইনটি একদম ফ্রি।
                        </div>
                        @endif

                        <div class="alert alert-info border-0 rounded-3 mb-4 small py-3">
                            <i class="fas fa-shield-alt me-1"></i> পেমেন্ট সফল হলে আপনার সার্ভিসগুলো অটোমেটিক/ম্যানুয়াল প্রসেস করা হবে।
                        </div>

                        <div class="d-lg-none">
                            <button type="submit" form="orderForm" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow-sm py-3 mt-2">
                                অর্ডার ও পেমেন্ট করুন <i class="fas fa-chevron-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
    :root { --primary-color: #ff6600; --primary-hover: #e65c00; }
    .text-primary { color: var(--primary-color) !important; }
    .bg-soft-orange { background-color: rgba(255, 102, 0, 0.1); }
    .btn-primary { background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; color: #fff !important; transition: 0.3s all ease; }
    .btn-primary:hover { background-color: var(--primary-hover) !important; transform: translateY(-3px); }
    .step-circle { width: 32px; height: 32px; line-height: 32px; border-radius: 50%; display: inline-block; font-weight: bold; background: white; z-index: 2; position: relative; }
    .payment-card { cursor: pointer; transition: 0.2s all ease; border: 2px solid #f0f0f0 !important; border-radius: 16px !important; background-color: #fff; }
    .active-card { border-color: var(--primary-color) !important; background-color: #fffaf7 !important; }
    @media (min-width: 992px) { .sticky-sidebar { position: sticky; top: 100px; z-index: 10; } }
</style>
@endsection