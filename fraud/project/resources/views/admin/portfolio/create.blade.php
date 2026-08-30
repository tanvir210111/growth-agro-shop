@extends('layouts.admin')
@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <h4 class="heading">{{ __('Add Project') }}</h4>
    </div>
    <div class="add-product-content bg-white p-5 shadow-sm" style="border-radius: 8px;">
        <form action="{{ route('admin.portfolio.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row mb-4">
                <div class="col-lg-4 text-right"><h4>{{ __('Project Title') }} *</h4></div>
                <div class="col-lg-7"><input type="text" class="form-control" name="title" required></div>
            </div>
            <div class="row mb-4">
                <div class="col-lg-4 text-right"><h4>{{ __('Category') }} *</h4></div>
                <div class="col-lg-7">
                    <select class="form-control" name="category_id" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-lg-4 text-right"><h4>{{ __('Project Photo') }} *</h4></div>
                <div class="col-lg-7"><input type="file" name="photo" class="form-control" required></div>
            </div>
            <div class="row mb-4">
                <div class="col-lg-4 text-right"><h4>{{ __('Project URL') }}</h4></div>
                <div class="col-lg-7"><input type="url" name="url" class="form-control" placeholder="https://"></div>
            </div>
            <div class="row mt-4">
                <div class="col-lg-4"></div>
                <div class="col-lg-7"><button class="btn btn-primary w-100 py-3" type="submit" style="background: #2d3274; border: none; font-weight: bold;">SAVE PROJECT</button></div>
            </div>
        </form>
    </div>
</div>
@endsection