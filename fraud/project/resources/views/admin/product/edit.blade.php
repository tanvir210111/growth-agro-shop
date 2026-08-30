@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">এডিট থিম: {{ $data->name }}</h6>
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

            <form action="{{ route('admin.products.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT') {{-- আপডেটের জন্য এটি জরুরি --}}
                
                <div class="row">
                    {{-- থিমের নাম --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">থিমের নাম <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $data->name }}" required>
                    </div>

                    {{-- থিমের মূল্য --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">মূল্য (৳) <span class="text-danger">*</span></label>
                        <input type="text" name="price" class="form-control" value="{{ $data->price }}" required>
                    </div>

                    {{-- সংক্ষিপ্ত বিবরণ --}}
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">সংক্ষিপ্ত বিবরণ (Short Description)</label>
                        <textarea name="description" class="form-control" rows="2">{{ $data->description }}</textarea>
                    </div>

                    {{-- বিস্তারিত বিবরণ (মেইন এডিটর) --}}
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">বিস্তারিত বিবরণ (Full Details)</label>
                        {{-- আগের ডাটা দেখানোর জন্য টেক্সট এরিয়ার ভেতরে ভেরিয়েবল রাখা হয়েছে --}}
                        <textarea name="details" id="details" class="form-control" rows="8">{{ $data->details }}</textarea>
                    </div>

                    {{-- ডেমো ও পারচেস লিঙ্ক --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">লাইভ ডেমো লিঙ্ক</label>
                        <input type="url" name="demo_link" class="form-control" value="{{ $data->demo_link }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">পারচেস লিঙ্ক (কিনুন)</label>
                        <input type="url" name="purchase_link" class="form-control" value="{{ $data->purchase_link }}">
                    </div>

                    {{-- থিম ইমেজ --}}
                    <div class="col-md-12 mb-4">
                        <label class="form-label fw-bold">ছবি পরিবর্তন (ফাঁকা রাখলে আগেরটি থাকবে)</label>
                        <input type="file" name="photo" class="form-control mb-2">
                        
                        {{-- বর্তমান ছবি দেখানো --}}
                        @if($data->photo)
                            <div class="p-2 border d-inline-block rounded bg-light">
                                <span class="d-block mb-1 text-muted small">বর্তমান ছবি:</span>
                                <img src="{{ asset('assets/images/products/'.$data->photo) }}" alt="Current Image" width="150" class="rounded">
                            </div>
                        @endif
                    </div>
                    
                    
                    
                    <div class="col-md-12 mt-4">
    <h5 class="font-weight-bold text-primary">SEO সেটিংস (অপশনাল)</h5>
    <hr>
</div>

<div class="col-md-6 mb-3">
    <label class="form-label fw-bold">মেটা টাইটেল (Meta Title)</label>
    <input type="text" name="meta_title" class="form-control" value="{{ $data->meta_title }}" placeholder="ফাঁকা রাখলে অটোমেটিক জেনারেট হবে">
</div>

<div class="col-md-6 mb-3">
    <label class="form-label fw-bold">মেটা কিওয়ার্ড (Meta Keywords)</label>
    <input type="text" name="meta_keyword" class="form-control" value="{{ $data->meta_keyword }}" placeholder="keyword1, keyword2, keyword3">
</div>

<div class="col-md-12 mb-3">
    <label class="form-label fw-bold">মেটা ডেসক্রিপশন (Meta Description)</label>
    <textarea name="meta_description" class="form-control" rows="3" placeholder="ফাঁকা রাখলে বিস্তারিত বিবরণ থেকে অটোমেটিক জেনারেট হবে">{{ $data->meta_description }}</textarea>
</div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success px-5 py-2 fw-bold shadow-sm">আপডেট করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- CKEditor 4 Full Package (কালার ও ফন্ট ফিচারসহ) --}}
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>

<script>
    $(document).ready(function() {
        CKEDITOR.replace('details', {
            height: 400,
            // এডিটর টুলবার কনফিগারেশন
            toolbar: [
                { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
                { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
                { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ] },
                { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
                { name: 'links', items: [ 'Link', 'Unlink' ] },
                { name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'SpecialChar' ] },
                { name: 'tools', items: [ 'Maximize', 'ShowBlocks', 'Source' ] }
            ],
            versionCheck: false
        });
    });
</script>

<style>
    /* ওয়ার্নিং মেসেজ হাইড করা */
    .cke_notification_warning { display: none !important; }
    .cke_button__about { display: none !important; }
</style>
@endsection