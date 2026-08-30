@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">Add New Brand</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.brand.index') }}">Brands</a></li>
                    <li><a href="{{ route('admin.brand.create') }}">Add New</a></li>
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
                        
                        <form action="{{ route('admin.brand.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="left-area">
                                        <h4 class="heading">Brand Name *</h4>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="name" placeholder="Enter Brand Name" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="left-area">
                                        <h4 class="heading">Brand Logo *</h4>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <input type="file" class="input-field" name="photo" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="left-area">
                                        <h4 class="heading">Website URL (Optional)</h4>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="url" placeholder="https://example.com">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-7">
                                    <button class="addProductSubmit-btn" type="submit">Save Brand</button>
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