@extends('layouts.front')

@section('contents')
<section class="consultation-section py-5" style="background: #f8f9fa;">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                    <div class="card-header text-white text-center py-4" style="background: #ff6600;">
                        <h3 class="fw-bold mb-0">ফ্রি কনসালটেন্সি বুক করুন</h3>
                        <p class="small mb-0 opacity-75">আমাদের এক্সপার্ট টিম আপনাকে কল করবে</p>
                    </div>
                    <div class="card-body p-4 p-lg-5">
                        
                        @if(Session::has('success'))
                            <div class="alert alert-success border-0 shadow-sm mb-4">
                                {{ Session::get('success') }}
                            </div>
                        @endif

                        <form action="{{ route('frontend.consultancy.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">আপনার নাম *</label>
                                <input type="text" name="name" class="form-control p-3 border-0 bg-light" placeholder="পুরো নাম লিখুন" required style="border-radius: 10px;">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">ফোন নাম্বার *</label>
                                    <input type="text" name="phone" class="form-control p-3 border-0 bg-light" placeholder="আপনার ফোন" required style="border-radius: 10px;">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">ইমেইল</label>
                                    <input type="email" name="email" class="form-control p-3 border-0 bg-light" placeholder="আপনার ইমেইল" style="border-radius: 10px;">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">আপনার বার্তা (ঐচ্ছিক)</label>
                                <textarea name="message" class="form-control p-3 border-0 bg-light" rows="4" placeholder="আপনার প্রয়োজনীয় সেবা সম্পর্কে লিখুন..." style="border-radius: 10px;"></textarea>
                            </div>

                            <button type="submit" class="btn btn-lg w-100 text-white fw-bold py-3 shadow" style="background: #ff6600; border-radius: 12px; transition: 0.3s;">
                                সাবমিট করুন <i class="fas fa-paper-plane ms-2"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .form-control:focus {
        background: #fff !important;
        box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
        border: 1px solid #ff6600 !important;
    }
    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(255, 102, 0, 0.2) !important;
        background: #e65c00 !important;
    }
</style>
@endsection