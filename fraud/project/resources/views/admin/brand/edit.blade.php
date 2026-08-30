@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">Edit Brand</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.brand.index') }}">Brands</a></li>
                    <li><a href="javascript:;">Edit</a></li>
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

                        <form action="{{ route('admin.brand.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="left-area">
                                        <h4 class="heading">Brand Name *</h4>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="name" value="{{ $brand->name }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="left-area">
                                        <h4 class="heading">Current Logo</h4>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <img src="{{ asset('assets/images/brands/'.$brand->photo) }}" alt="Logo" style="max-width: 150px; margin-bottom: 10px;">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="left-area">
                                        <h4 class="heading">Change Logo</h4>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <input type="file" class="input-field" name="photo">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="left-area">
                                        <h4 class="heading">Website URL</h4>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="url" value="{{ $brand->url }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-7">
                                    <button class="addProductSubmit-btn" type="submit">Update Brand</button>
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