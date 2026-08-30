@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Add New Counter') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.counter.index') }}">{{ __('Counters') }}</a></li>
                    <li><a href="javascript:;">{{ __('Add New') }}</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="add-product-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="product-description">
                    <div class="body-area">
                        @include('includes.admin.form-error') 
                        
                        <form action="{{ route('admin.counter.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Title') }} *</h4>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="title" placeholder="উদাঃ হ্যাপি ক্লায়েন্ট" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Count Value') }} *</h4>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="count_value" placeholder="উদাঃ ৫০০+" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-7 text-center">
                                    <button class="addProductSubmit-btn" type="submit">{{ __('Save Counter') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection