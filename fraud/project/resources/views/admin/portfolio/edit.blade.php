@extends('layouts.admin')
@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <h4 class="heading">{{ __('Edit Project') }}</h4>
    </div>
    <div class="add-product-content bg-white p-5 shadow-sm" style="border-radius: 8px;">
        @include('includes.admin.form-both')
        <form action="{{ route('admin.portfolio.update', $data->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row mb-4">
                <div class="col-lg-4 text-right"><h4>Project Title *</h4></div>
                <div class="col-lg-7"><input type="text" class="form-control" name="title" value="{{ $data->title }}" required></div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-4 text-right"><h4>Category *</h4></div>
                <div class="col-lg-7">
                    <select class="form-control" name="category_id" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $data->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-4 text-right"><h4>Current Photo</h4></div>
                <div class="col-lg-7">
                    <img src="{{ asset('assets/images/portfolio/'.$data->photo) }}" width="150" class="border shadow-sm">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-4 text-right"><h4>Update Photo</h4></div>
                <div class="col-lg-7"><input type="file" name="photo" class="form-control"></div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-4 text-right"><h4>Project URL</h4></div>
                <div class="col-lg-7"><input type="text" class="form-control" name="url" value="{{ $data->url }}"></div>
            </div>

            <div class="row">
                <div class="col-lg-4"></div>
                <div class="col-lg-7">
                    <button class="btn btn-primary w-100" type="submit" style="background:#2d3274; border:none;">UPDATE PROJECT</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection