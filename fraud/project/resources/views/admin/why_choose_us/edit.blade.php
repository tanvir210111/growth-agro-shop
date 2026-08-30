@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Edit Feature') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.why-choose-us.index') }}">{{ __('Why Choose Us') }}</a></li>
                    <li><a href="javascript:;">{{ __('Edit Feature') }}</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="add-product-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="product-description">
                    <div class="body-area">
                        
                        {{-- এরর মেসেজ দেখানোর জন্য --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.why-choose-us.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            {{-- আপডেট করার সময় POST মেথড ব্যবহার করলেও কন্ট্রোলারের জন্য এটি জরুরি নয়, তবে রাউটে POST থাকলে এখানেও তাই হবে --}}

                            <div class="row mb-4">
                                <div class="col-lg-4">
                                    <div class="left-area">
                                        <h4 class="heading">{{ __('Feature Title') }} *</h4>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="title" value="{{ $data->title }}" required>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-4">
                                    <div class="left-area">
                                        <h4 class="heading">{{ __('Icon Class') }} *</h4>
                                        <p class="sub-heading">{{ __('(e.g. fas fa-users)') }}</p>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="icon" value="{{ $data->icon }}" required>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-4">
                                    <div class="left-area">
                                        <h4 class="heading">{{ __('Description') }} *</h4>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <textarea class="input-field" name="description" required>{{ $data->description }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-4">
                                    <div class="left-area">
                                        <h4 class="heading">{{ __('Current Featured Image') }}</h4>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <div class="img-upload">
                                        <div id="image-preview" class="img-preview mb-3" style="background: #f1f1f1; border: 1px dashed #ddd; width: 200px; height: 150px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                            @if($data->image)
                                                <img src="{{ url('assets/images/why-choose-us/'.$data->image) }}" style="width:100%; height:100%; object-fit:cover;">
                                            @else
                                                <span class="text-muted">{{ __('No Image Found') }}</span>
                                            @endif
                                        </div>
                                        <input type="file" name="image" class="form-control" id="image-input">
                                        <small class="text-info">{{ __('নতুন ছবি আপলোড করতে চাইলে ফাইল সিলেক্ট করুন, অন্যথায় খালি রাখুন।') }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-7 text-center">
                                    <button class="addProductSubmit-btn" type="submit">{{ __('Update Feature') }}</button>
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
    // নতুন ইমেজ সিলেক্ট করলে সাথে সাথে প্রিভিউ দেখানোর জন্য
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
    .input-field { width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 4px; }
    .addProductSubmit-btn { background: #2d3274; color: #fff; padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; }
    .heading { font-size: 16px; font-weight: 600; color: #333; }
    .sub-heading { font-size: 12px; color: #888; }
</style>
@endsection