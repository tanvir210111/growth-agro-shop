@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Edit About Us Section') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="javascript:;">{{ __('Home Page Settings') }}</a></li>
                    <li><a href="{{ route('admin.about.edit') }}">{{ __('Edit About Section') }}</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="add-product-content1">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="product-description card-shadow">
                    <div class="body-area">
                        
                        {{-- মেসেজ নোটিফিকেশন --}}
                        <div class="mb-4">
                            @if(Session::has('success'))
                                <div class="alert alert-success alert-dismissible fade show custom-alert" role="alert">
                                    <i class="fas fa-check-circle mr-2"></i> <strong>Success!</strong> {{ Session::get('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger custom-alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li><i class="fas fa-exclamation-triangle mr-2"></i> {{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <form action="{{ route('admin.about.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            {{-- টাইটেল সেকশন --}}
                            <div class="row mb-4">
                                <div class="col-lg-3">
                                    <div class="left-area">
                                        <h4 class="heading font-weight-bold">{{ __('Title') }} <span class="text-danger">*</span></h4>
                                        <p class="sub-heading text-muted small">মূল বড় টেক্সটটি এখানে লিখুন</p>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" class="input-field modern-input" name="title" placeholder="{{ __('Enter Title') }}" value="{{ $data->title ?? '' }}" required>
                                </div>
                            </div>

                            {{-- সাবটাইটেল সেsection --}}
                            <div class="row mb-4">
                                <div class="col-lg-3">
                                    <div class="left-area">
                                        <h4 class="heading font-weight-bold">{{ __('Subtitle') }} <span class="text-danger">*</span></h4>
                                        <p class="sub-heading text-muted small">বিস্তারিত বিবরণ এখানে লিখুন</p>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <textarea class="input-field modern-input" name="subtitle" placeholder="{{ __('Enter Subtitle') }}" required rows="5">{{ $data->subtitle ?? '' }}</textarea>
                                </div>
                            </div>

                            {{-- ইমেজ আপলোড সেকশন --}}
                            <div class="row mb-5">
                                <div class="col-lg-3">
                                    <div class="left-area">
                                        <h4 class="heading font-weight-bold">{{ __('Current Image') }} <span class="text-danger">*</span></h4>
                                        <p class="sub-heading text-muted small">প্রোফাইল বা ফিচার ইমেজ</p>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="img-upload-wrapper">
                                        <div id="image-preview" class="img-preview shadow-sm mb-3" 
                                             style="background: url({{ !empty($data->image) ? asset('assets/images/about/'.$data->image) : asset('assets/images/noimage.png') }});">
                                        </div>
                                        <div class="custom-file-upload">
                                            <input type="file" name="image" id="image-upload" class="d-none">
                                            <label for="image-upload" class="upload-label-btn">
                                                <i class="fas fa-cloud-upload-alt mr-2"></i> {{ __('Choose New Image') }}
                                            </label>
                                        </div>
                                        <small class="text-info"><i class="fas fa-info-circle"></i> প্রস্তাবিত সাইজ: ৮০০ x ৬০০ পিক্সেল</small>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            {{-- বাটন সেকশন --}}
                            <div class="row mt-4">
                                <div class="col-lg-3"></div>
                                <div class="col-lg-8">
                                    <button class="addProductSubmit-btn modern-submit-btn" type="submit">
                                        <i class="fas fa-save mr-2"></i> {{ __('Update Content') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- সুন্দর ডিজাইনের জন্য CSS --}}
<style>
    .card-shadow { box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-radius: 12px; padding: 30px; background: #fff; }
    .modern-input { border: 1px solid #e1e5eb !important; border-radius: 8px !important; padding: 12px 15px !important; transition: all 0.3s; }
    .modern-input:focus { border-color: #2d3274 !important; box-shadow: 0 0 0 3px rgba(45, 50, 116, 0.1); }
    
    .img-preview { width: 100%; max-width: 300px; height: 200px; border-radius: 10px; border: 2px dashed #ddd; background-position: center !important; background-size: cover !important; }
    
    .upload-label-btn { background: #f8f9fa; border: 1px solid #ced4da; padding: 10px 20px; border-radius: 6px; cursor: pointer; transition: 0.3s; color: #495057; display: inline-block; }
    .upload-label-btn:hover { background: #e2e6ea; border-color: #dae0e5; }
    
    .modern-submit-btn { background: #2d3274 !important; color: #fff !important; width: auto !important; padding: 12px 35px !important; border-radius: 8px !important; font-size: 16px; font-weight: 600; border: none; transition: 0.3s; }
    .modern-submit-btn:hover { background: #1f2352 !important; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    
    .custom-alert { border-radius: 8px; border: none; }
</style>

@endsection

{{-- ইমেজ প্রিভিউ করার জন্য স্ক্রিপ্ট --}}
@section('scripts')
<script>
    $("#image-upload").on('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').css('background-image', 'url(' + e.target.result + ')');
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection