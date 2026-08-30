@extends('layouts.front')

@section('meta')
    <title>{{ $category }} Hosting - {{ $gs->title }}</title>
    <meta name="description" content="সাশ্রয়ী মূল্যে সেরা {{ $category }} হোস্টিং প্যাকেজ বেছে নিন।">
@endsection

@section('contents')

    {{-- ১. হিরো সেকশন --}}
    <section class="page-hero">
        <div class="container">
            <h1>আমাদের <span>{{ $category }}</span> প্যাকেজসমূহ</h1>
            <p class="lead opacity-75">আপনার ব্যবসার জন্য সঠিক {{ $category }} সলিউশন বেছে নিন।</p>
        </div>
    </section>

    {{-- ২. হোস্টিং প্যাকেজ গ্রিড --}}
    <section class="py-5 bg-light position-relative" style="margin-top: -50px;">
        <div class="container">
            <div class="row g-4 justify-content-center align-items-stretch">
                
                @forelse($plans as $plan)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card pricing-card h-100 border-0 shadow-lg text-center position-relative {{ $loop->iteration == 2 ? 'featured-card' : '' }}" style="border-radius: 25px; transition: 0.4s; background: #fff;">
                        
                        @if($loop->iteration == 2)
                            <div class="popular-badge">RECOMMENDED</div>
                        @endif

                        <div class="card-body p-4 p-md-5 d-flex flex-column">
                            <h3 class="fw-bold mb-3 text-dark">{{ $plan->name }}</h3>
                            
                            <div class="price-box mb-4 py-3" style="background: rgba(255, 102, 0, 0.05); border-radius: 15px;">
                                <h2 class="display-4 fw-bold mb-0" style="color: #ff6600;">
                                    <span class="fs-3">৳</span>{{ number_format($plan->price_yearly, 0) }}
                                </h2>
                                <p class="text-muted mb-0 small text-uppercase fw-bold">প্রতি বছর</p>
                            </div>

                            <div class="hosting-features mb-5 text-start mx-auto w-100" style="max-width: 280px; min-height: 200px;">
                                {!! $plan->description !!}
                            </div>

                            <div class="mt-auto">
                                {{-- বাটনে এখন সরাসরি লিংক না দিয়ে OnClick ইভেন্ট দেওয়া হলো --}}
                                <button onclick="openDomainModal({{ $plan->id }}, '{{ $plan->name }}')" 
                                   class="btn btn-lg w-100 py-3 fw-bold {{ $loop->iteration == 2 ? 'text-white shadow orange-active-btn' : 'shadow-sm blue-outline-btn' }}">
                                    {{ $loop->iteration == 2 ? 'এখনই কিনুন' : 'অর্ডার করুন' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">দুঃখিত, কোনো প্যাকেজ পাওয়া যায়নি।</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ৩. ডোমেইন সিলেকশন পপআপ মডেল (NEW ADDITION) --}}
    <div class="modal fade" id="domainOptionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title fw-bold">ডোমেইন অপশন নির্বাচন করুন</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="selectedPlanId">
                    
                    {{-- অপশন ১: নতুন ডোমেইন --}}
                    <div class="d-grid gap-3">
                        <button onclick="goToNewDomain()" class="btn btn-outline-primary py-3 text-start px-4 rounded-3 border-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-search fa-2x me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-0">নতুন ডোমেইন কিনুন</h6>
                                    <small class="text-muted">আমাদের থেকে নতুন ডোমেইন রেজিস্টার করুন</small>
                                </div>
                            </div>
                        </button>

                        <div class="text-center text-muted position-relative my-2">
                            <span class="bg-white px-3 position-relative z-index-1">অথবা</span>
                            <hr class="position-absolute w-100 top-50 start-0 z-index-0 m-0">
                        </div>

                        {{-- অপশন ২: নিজের ডোমেইন --}}
                        <div class="card bg-light border-0 p-3">
                            <label class="fw-bold mb-2 small text-uppercase text-muted">আমার নিজের ডোমেইন আছে</label>
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-white"><i class="fas fa-globe"></i></span>
                                <input type="text" id="ownDomainInput" class="form-control border-0 shadow-none" placeholder="example.com">
                                <button onclick="useOwnDomain()" class="btn btn-dark px-4">ব্যবহার করুন</button>
                            </div>
                            <small class="text-danger mt-2" id="domainError" style="display:none;">দয়া করে ডোমেইন নাম লিখুন</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- জাভাস্ক্রিপ্ট --}}
    <script>
        function openDomainModal(planId, planName) {
            document.getElementById('selectedPlanId').value = planId;
            var myModal = new bootstrap.Modal(document.getElementById('domainOptionModal'));
            myModal.show();
        }

        function goToNewDomain() {
            let planId = document.getElementById('selectedPlanId').value;
            // আগের ফ্লো: ডোমেইন সার্চ পেজে নিয়ে যাবে
            window.location.href = "{{ route('front.domain.search') }}?plan_id=" + planId;
        }

        function useOwnDomain() {
            let planId = document.getElementById('selectedPlanId').value;
            let domain = document.getElementById('ownDomainInput').value.trim();
            
            if(!domain) {
                document.getElementById('domainError').style.display = 'block';
                return;
            }

            // নতুন ফ্লো: ডোমেইন সার্চ বাদ দিয়ে সরাসরি কনফিগারেশন পেজে (type=existing সহ)
            window.location.href = "{{ route('front.domain.config') }}?plan_id=" + planId + "&domain=" + domain + "&type=existing";
        }
    </script>

    <style>
        .pricing-card { overflow: hidden; height: 100%; border: 1px solid #eee !important; }
        .hosting-features ul { list-style: none; padding: 0; margin: 0; }
        .hosting-features ul li { margin-bottom: 12px; display: flex; align-items: start; font-size: 15px; color: #6c757d; }
        .hosting-features ul li::before { content: "\f058"; font-family: "Font Awesome 5 Free"; font-weight: 900; color: #28a745; margin-right: 15px; font-size: 18px; }
        .featured-card { border: 2.5px solid #ff6600 !important; transform: scale(1.05); z-index: 10; background-color: #fffaf7 !important; }
        .popular-badge { position: absolute; top: 20px; right: -10px; background: #ff6600; color: #fff; padding: 5px 15px; font-size: 12px; font-weight: bold; border-radius: 5px 0 0 5px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .orange-active-btn { background: #ff6600; border-radius: 50px; border: none; color: #fff; }
        .orange-active-btn:hover { background: #e65c00; color: #fff; transform: translateY(-3px); }
        .blue-outline-btn { border: 1.5px solid #007bff; color: #007bff; border-radius: 50px; background: transparent; transition: 0.3s; }
        .blue-outline-btn:hover { background: #007bff; color: #fff; }
    </style>

@endsection