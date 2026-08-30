@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="add-product-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="product-description">
                    <div class="body-area">
                        <h4 class="mb-4">{{ __('Add New Service') }}</h4>
                        <form action="{{ route('admin.service.store') }}" method="POST">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-lg-4 text-right"><h4 class="heading">{{ __('Title') }} *</h4></div>
                                <div class="col-lg-7"><input type="text" class="input-field" name="title" placeholder="যেমন: ওয়েব ডেভেলপমেন্ট" required></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Icon Class') }} *</h4>
                                    <small><a href="https://fontawesome.com/v5/search?m=free" target="_blank">Get Icons Here</a></small>
                                </div>
                                <div class="col-lg-7"><input type="text" class="input-field" name="icon" placeholder="যেমন: fas fa-globe" required></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-lg-4 text-right"><h4 class="heading">{{ __('Description') }} *</h4></div>
                                <div class="col-lg-7"><textarea class="input-field" name="description" placeholder="সার্ভিস সম্পর্কে লিখুন..." required></textarea></div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-7"><button class="addProductSubmit-btn" type="submit">{{ __('Create Service') }}</button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection