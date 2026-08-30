@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <h4 class="heading">{{ __('Edit Pricing Plan') }}</h4>
    </div>

    <div class="add-product-content bg-white p-5 shadow-sm" style="border-radius: 8px;">
        @include('includes.admin.form-both') 
        
        <form action="{{ route('admin.pricing.update', $data->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row mb-4">
                <div class="col-lg-4 text-right"><h4>Plan Title *</h4></div>
                <div class="col-lg-7"><input type="text" class="form-control" name="title" value="{{ $data->title }}" required></div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-4 text-right"><h4>Price *</h4></div>
                <div class="col-lg-7"><input type="text" class="form-control" name="price" value="{{ $data->price }}" required></div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-4 text-right"><h4>Features *</h4></div>
                <div class="col-lg-7">
                    <textarea class="form-control" name="features" rows="6" required>{{ $data->features }}</textarea>
                    <small class="text-primary">ফিচারগুলো কমা (,) দিয়ে লিখুন।</small>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-4 text-right"><h4>Order Link</h4></div>
                <div class="col-lg-7"><input type="url" class="form-control" name="order_link" value="{{ $data->order_link }}" placeholder="https://"></div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-4"></div>
                <div class="col-lg-7">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" {{ $data->is_featured == 1 ? 'checked' : '' }}>
                        <label class="custom-control-label font-weight-bold" for="is_featured">Highlight as Best Value?</label>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-lg-4"></div>
                <div class="col-lg-7">
                    <button class="btn btn-primary w-100 py-3" type="submit" style="background: #2d3274; border: none; font-weight: bold;">
                        {{ __('UPDATE PLAN') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection