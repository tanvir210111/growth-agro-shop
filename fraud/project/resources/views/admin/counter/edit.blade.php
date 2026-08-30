@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <h4 class="heading">{{ __('Edit Counter') }}</h4>
    </div>

    <div class="add-product-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="product-description">
                    <div class="body-area">
                        @include('includes.admin.form-error') 

                        <form action="{{ route('admin.counter.update', $counter->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Title') }} *</h4>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="title" value="{{ $counter->title }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Count Value') }} *</h4>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="count_value" value="{{ $counter->count_value }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-7 text-center">
                                    <button class="addProductSubmit-btn" type="submit">{{ __('Update Counter') }}</button>
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