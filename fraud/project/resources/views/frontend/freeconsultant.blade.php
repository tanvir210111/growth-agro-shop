@extends('layouts.front')

@section('meta')
    <title>{{ __('Free Consultant') }} - {{ $gs->title }}</title>
    <meta property="og:title" content="{{ __('Free Consultant') }}" />
@endsection

@section('contents')

    <section class="page-hero">
        <div class="container">
            <h1>ফ্রি <span>কনসালটেন্সি</span> বুক করুন</h1>
            <p class="lead opacity-75">আপনার ব্যবসার ডিজিটাল যাত্রা শুরু করতে আমাদের অভিজ্ঞ টিমের পরামর্শ নিন।</p>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container py-4">
            <div class="row g-5 justify-content-center">
                
                <div class="col-lg-5">
                    <div class="pe-lg-4">
                        <h2 class="fw-bold mb-4" style="color: #1a1a1a;">আমরা আপনাকে কীভাবে সাহায্য করতে পারি?</h2>
                        <p class="text-muted mb-4">আমাদের বিশেষজ্ঞরা আপনার ব্যবসার বর্তমান অবস্থা বিশ্লেষণ করে সঠিক ডিজিটাল সলিউশন প্রদান করবে।</p>
                        
                        <div class="d-flex mb-4">
                            <div class="icon-circle me-3 flex-shrink-0" style="width: 50px; height: 50px; background: rgba(255, 102, 0, 0.1); color: #ff6600; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">দ্রুত প্রবৃদ্ধি</h5>
                                <p class="small text-muted mb-0">সঠিক টেকনোলজি ব্যবহার করে আপনার ব্যবসার সেলস বৃদ্ধি করুন।</p>
                            </div>
                        </div>

                        <div class="d-flex mb-4">
                            <div class="icon-circle me-3 flex-shrink-0" style="width: 50px; height: 50px; background: rgba(255, 102, 0, 0.1); color: #ff6600; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">মার্কেটিং স্ট্র্যাটেজি</h5>
                                <p class="small text-muted mb-0">টার্গেটেড কাস্টমারের কাছে পৌঁছানোর সঠিক পরিকল্পনা।</p>
                            </div>
                        </div>

                        <div class="d-flex">
                            <div class="icon-circle me-3 flex-shrink-0" style="width: 50px; height: 50px; background: rgba(255, 102, 0, 0.1); color: #ff6600; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">২৪/৭ সাপোর্ট</h5>
                                <p class="small text-muted mb-0">যেকোনো টেকনিক্যাল প্রয়োজনে আমরা আছি আপনার পাশে।</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-lg p-4 p-md-5" style="border-radius: 20px;">
                        
                        @if(Session::has('success'))
                            <div class="alert alert-success border-0 shadow-sm mb-4" style="background: #28a745; color: #fff;">
                                <i class="fas fa-check-circle me-2"></i> {{ Session::get('success') }}
                            </div>
                        @endif

                        <form action="{{ route('frontend.consultancy.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">আপনার নাম *</label>
                                <input type="text" name="name" class="form-control custom-input" placeholder="পুরো নাম লিখুন" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">ফোন নাম্বার *</label>
                                    <input type="text" name="phone" class="form-control custom-input" placeholder="আপনার ফোন" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">ইমেইল (ঐচ্ছিক)</label>
                                    <input type="email" name="email" class="form-control custom-input" placeholder="আপনার ইমেইল">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">মেসেজ (আপনার চাহিদা সম্পর্কে লিখুন)</label>
                                <textarea name="message" class="form-control custom-input" rows="4" placeholder="যেমন: ওয়েবসাইট তৈরি বা মার্কেটিং..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-lg w-100 text-white fw-bold py-3 orange-btn shadow">
                                সাবমিট করুন <i class="fas fa-paper-plane ms-2"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <style>
        .custom-input {
            background: #f8f9fa;
            border: 2px solid #f8f9fa;
            padding: 12px 15px;
            border-radius: 10px;
            transition: 0.3s;
        }
        .custom-input:focus {
            background: #fff;
            border-color: #ff6600;
            box-shadow: 0 0 10px rgba(255, 102, 0, 0.1);
        }
        .orange-btn {
            background: #ff6600;
            border: none;
            border-radius: 10px;
            transition: 0.3s;
        }
        .orange-btn:hover {
            background: #e65c00;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 102, 0, 0.2);
        }
        .icon-circle i {
            transition: 0.3s;
        }
        .d-flex:hover .icon-circle i {
            transform: scale(1.2);
        }
    </style>

@endsection