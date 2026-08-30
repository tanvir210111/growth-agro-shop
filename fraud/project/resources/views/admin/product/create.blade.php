@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">নতুন থিম যোগ করুন</h6>
            <a href="{{ route('admin.products.index') }}" class="btn btn-dark btn-sm">তালিকায় ফিরে যান</a>
        </div>
        <div class="card-body">
            {{-- এরর মেসেজ হ্যান্ডলিং --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    {{-- থিমের নাম --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">থিমের নাম <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="যেমন: ই-কমার্স প্রো থিম" required>
                    </div>

                    {{-- থিমের মূল্য --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">মূল্য (৳) <span class="text-danger">*</span></label>
                        <input type="text" name="price" class="form-control" placeholder="৫০০০" required>
                    </div>

                    {{-- সংক্ষিপ্ত বিবরণ --}}
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">সংক্ষিপ্ত বিবরণ (Short Description)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="থিমের মূল ফিচারগুলো এক লাইনে লিখুন..."></textarea>
                    </div>

                    {{-- বিস্তারিত বিবরণ (মেইন এডিটর) --}}
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">বিস্তারিত বিবরণ (Full Details)</label>
                        {{-- লক্ষ্য করুন: এখানে id="details" দেওয়া আছে যা স্ক্রিপ্টে ধরা হবে --}}
                        <textarea name="details" id="details" class="form-control" rows="10"></textarea>
                    </div>

                    {{-- ডেমো ও পারচেস লিঙ্ক --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">লাইভ ডেমো লিঙ্ক</label>
                        <input type="url" name="demo_link" class="form-control" placeholder="https://demo-link.com">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">পারচেস লিঙ্ক (কিনুন)</label>
                        <input type="url" name="purchase_link" class="form-control" placeholder="হোয়াটসঅ্যাপ বা পেমেন্ট লিঙ্ক">
                    </div>

                    {{-- থিম ইমেজ --}}
                    <div class="col-md-12 mb-4">
                        <label class="form-label fw-bold">থিম প্রিভিউ ইমেজ (Photo) <span class="text-danger">*</span></label>
                        <input type="file" name="photo" class="form-control" required>
                        <small class="text-muted">প্রোডাক্টের ভালো মানের একটি ছবি আপলোড করুন।</small>
                    </div>
                    
                    <div class="col-md-12 mt-4">
    <h5 class="font-weight-bold text-primary">SEO সেটিংস (অপশনাল)</h5>
    <hr>
</div>

<div class="col-md-6 mb-3">
    <label class="form-label fw-bold">মেটা টাইটেল (Meta Title)</label>
    <input type="text" name="meta_title" class="form-control" placeholder="ফাঁকা রাখলে অটোমেটিক জেনারেট হবে">
</div>

<div class="col-md-6 mb-3">
    <label class="form-label fw-bold">মেটা কিওয়ার্ড (Meta Keywords)</label>
    <input type="text" name="meta_keyword" class="form-control" placeholder="keyword1, keyword2, keyword3">
</div>

<div class="col-md-12 mb-3">
    <label class="form-label fw-bold">মেটা ডেসক্রিপশন (Meta Description)</label>
    <textarea name="meta_description" class="form-control" rows="3" placeholder="ফাঁকা রাখলে বিস্তারিত বিবরণ থেকে অটোমেটিক জেনারেট হবে"></textarea>
</div>
                    
                    
                </div>
                
                
                

                <div class="text-center">
                    <button type="submit" class="btn btn-success px-5 py-2 fw-bold shadow-sm">প্রোডাক্ট সেভ করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- CKEditor 4 Full Package (সব কালার এবং ফন্ট ফিচারসহ) --}}
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>

<script>
    // পেজ লোড হওয়ার পর এডিটর চালু হবে
    $(document).ready(function() {
        CKEDITOR.replace('details', {
            height: 400,
            // আপনি যা যা চেয়েছেন সব অপশন এখানে কনফিগার করা হলো
            toolbar: [
                { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] }, // ফন্ট স্টাইল ও সাইজ
                { name: 'colors', items: [ 'TextColor', 'BGColor' ] }, // টেক্সট কালার ও ব্যাকগ্রাউন্ড
                { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ] },
                { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
                { name: 'links', items: [ 'Link', 'Unlink' ] },
                { name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'SpecialChar' ] },
                { name: 'tools', items: [ 'Maximize', 'ShowBlocks', 'Source' ] }
            ],
            // ওয়ার্নিং মেসেজ বন্ধ রাখার জন্য (যদিও এই ভার্সনে ওয়ার্নিং আসার কথা না)
            versionCheck: false
        });
    });
</script>

<style>
    /* ওয়ার্নিং বা প্রমোশনাল মেসেজ হাইড করা */
    .cke_notification_warning { display: none !important; }
    .cke_button__about { display: none !important; }
</style>
@endsection