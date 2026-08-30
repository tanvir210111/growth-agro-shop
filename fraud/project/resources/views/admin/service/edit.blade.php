@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="add-product-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="product-description">
                    <div class="body-area">
                        <h4 class="mb-4">{{ __('Edit Service') }}</h4>
                        <form action="{{ route('admin.service.update', $service->id) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="row mb-3">
                                <div class="col-lg-4 text-right"><h4 class="heading">{{ __('Title') }} *</h4></div>
                                <div class="col-lg-7"><input type="text" class="input-field" name="title" value="{{ $service->title }}" required></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-lg-4 text-right"><h4 class="heading">{{ __('Icon Class') }} *</h4></div>
                                <div class="col-lg-7"><input type="text" class="input-field" name="icon" value="{{ $service->icon }}" required></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-lg-4 text-right"><h4 class="heading">{{ __('Description') }} *</h4></div>
                                <div class="col-lg-7"><textarea class="input-field" name="description" required>{{ $service->description }}</textarea></div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-7"><button class="addProductSubmit-btn" type="submit">{{ __('Update Service') }}</button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .input-field { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px; }
    .addProductSubmit-btn { background: #2d3274; color: #fff; padding: 10px 25px; border: none; border-radius: 4px; cursor: pointer; }
</style>
@endsection