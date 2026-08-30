@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Add New Feature') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.why-choose-us.index') }}">{{ __('Why Choose Us') }}</a></li>
                    <li><a href="{{ route('admin.why-choose-us.create') }}">{{ __('Add New') }}</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="add-product-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="product-description">
                    <div class="body-area">
                        {{-- সাকসেস বা এরর মেসেজ দেখানোর জন্য --}}
                        @if(Session::has('success'))
                            <div class="alert alert-success">
                                {{ Session::get('success') }}
                            </div>
                        @endif

                        {{-- ইমেজ আপলোডের জন্য enctype অবশ্যই লাগবে --}}
                        <form action="{{ route('admin.why-choose-us.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="row mb-4">
                                <div class="col-lg-4">
                                    <div class="left-area">
                                        <h4 class="heading">{{ __('Feature Title') }} *</h4>
                                        <p class="sub-heading">{{ __('(In Any Language)') }}</p>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="title" placeholder="{{ __('যেমন: অভিজ্ঞ টিম') }}" required>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-4">
                                    <div class="left-area">
                                        <h4 class="heading">{{ __('Icon Class') }} *</h4>
                                        <p class="sub-heading">{{ __('(FontAwesome Class, e.g. fas fa-users)') }}</p>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="icon" placeholder="{{ __('fas fa-users') }}" required>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-4">
                                    <div class="left-area">
                                        <h4 class="heading">{{ __('Description') }} *</h4>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <textarea class="input-field" name="description" placeholder="{{ __('ছোট বর্ণনা লিখুন') }}" required></textarea>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-4">
                                    <div class="left-area">
                                        <h4 class="heading">{{ __('Feature Image') }} *</h4>
                                        <p class="sub-heading">{{ __('(বাম পাশের বড় ছবির জন্য)') }}</p>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <div class="img-upload">
                                        <div id="image-preview" class="img-preview" style="background: #f1f1f1; border: 1px dashed #ddd; width: 200px; height: 150px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                                            <span class="text-muted">{{ __('No Image Selected') }}</span>
                                        </div>
                                        <input type="file" name="image" class="form-control" id="image-input" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-7 text-center">
                                    <button class="addProductSubmit-btn" type="submit">{{ __('Create Feature') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // ইমেজ সিলেক্ট করলে সাথে সাথে প্রিভিউ দেখানোর জন্য ছোট জাভাস্ক্রিপ্ট
    document.getElementById('image-input').onchange = function (evt) {
        var tgt = evt.target || window.event.srcElement,
            files = tgt.files;
        if (FileReader && files && files.length) {
            var fr = new FileReader();
            fr.onload = function () {
                document.getElementById('image-preview').innerHTML = '<img src="' + fr.result + '" style="width:100%; height:100%; object-fit:cover;">';
            }
            fr.readAsDataURL(files[0]);
        }
    }
</script>

<style>
/* আপনার এডমিন প্যানেলের ইনপুট ফিল্ডের স্টাইল */
.input-field {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fff;
}
.addProductSubmit-btn {
    background: #2d3274;
    color: #fff;
    padding: 12px 30px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}
.heading { font-size: 16px; font-weight: 600; color: #333; }
.sub-heading { font-size: 12px; color: #888; }
</style>
@endsection