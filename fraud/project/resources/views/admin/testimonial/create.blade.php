@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Add New Testimonial') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.testimonial.index') }}">{{ __('Testimonials') }}</a></li>
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
                        
                        <form action="{{ route('admin.testimonial.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Client Name') }} *</h4>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="name" placeholder="{{ __('Enter Client Name') }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Designation') }} *</h4>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="designation" placeholder="{{ __('Ex: SEO Specialist') }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Rating (1-5)') }} *</h4>
                                </div>
                                <div class="col-lg-7">
                                    <select class="input-field" name="rating" required>
                                        <option value="5">5 Stars</option>
                                        <option value="4">4 Stars</option>
                                        <option value="3">3 Stars</option>
                                        <option value="2">2 Stars</option>
                                        <option value="1">1 Star</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Client Photo') }} *</h4>
                                </div>
                                <div class="col-lg-7">
                                    <input type="file" class="input-field" name="photo" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Client Message') }} *</h4>
                                </div>
                                <div class="col-lg-7">
                                    <textarea class="input-field" name="message" placeholder="{{ __('Enter Message') }}" required></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-7 text-center">
                                    <button class="addProductSubmit-btn" type="submit">{{ __('Save Testimonial') }}</button>
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