@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Add New Team Member') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.team.index') }}">{{ __('Team') }}</a></li>
                    <li><a href="javascript:;">{{ __('Add Member') }}</a></li>
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
                        
                        <form action="{{ route('admin.team.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-lg-4 text-right"><h4 class="heading">{{ __('Member Name') }} *</h4></div>
                                <div class="col-lg-7"><input type="text" class="input-field" name="name" placeholder="Enter Full Name" required></div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right"><h4 class="heading">{{ __('Designation') }} *</h4></div>
                                <div class="col-lg-7"><input type="text" class="input-field" name="designation" placeholder="Ex: CEO, Manager" required></div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right"><h4 class="heading">{{ __('Profile Photo') }} *</h4></div>
                                <div class="col-lg-7"><input type="file" class="input-field" name="photo" required></div>
                            </div>

                            <hr>
                            <h5 class="text-center mb-4 text-muted">{{ __('Social Media Links (Optional)') }}</h5>

                            <div class="row">
                                <div class="col-lg-4 text-right"><h4 class="heading">Facebook</h4></div>
                                <div class="col-lg-7"><input type="url" class="input-field" name="facebook" placeholder="https://facebook.com/username"></div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right"><h4 class="heading">Twitter</h4></div>
                                <div class="col-lg-7"><input type="url" class="input-field" name="twitter" placeholder="https://twitter.com/username"></div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right"><h4 class="heading">LinkedIn</h4></div>
                                <div class="col-lg-7"><input type="url" class="input-field" name="linkedin" placeholder="https://linkedin.com/in/username"></div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-7 text-center">
                                    <button class="addProductSubmit-btn" type="submit">{{ __('Create Member') }}</button>
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