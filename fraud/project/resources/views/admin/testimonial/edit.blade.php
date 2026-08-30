@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <h4 class="heading">{{ __('Edit Testimonial') }}</h4>
    </div>

    <div class="add-product-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="product-description">
                    <div class="body-area">
                        @include('includes.admin.form-error') 

                        <form action="{{ route('admin.testimonial.update', $testimonial->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Name') }} *</h4>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="name" value="{{ $testimonial->name }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Designation') }} *</h4>
                                </div>
                                <div class="col-lg-7">
                                    <input type="text" class="input-field" name="designation" value="{{ $testimonial->designation }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Rating') }} *</h4>
                                </div>
                                <div class="col-lg-7">
                                    <select class="input-field" name="rating" required>
                                        @for($i=5; $i>=1; $i--)
                                            <option value="{{ $i }}" {{ $testimonial->rating == $i ? 'selected' : '' }}>{{ $i }} Stars</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Current Photo') }}</h4>
                                </div>
                                <div class="col-lg-7">
                                    <img src="{{ asset('assets/images/testimonials/'.$testimonial->photo) }}" style="width: 80px; border-radius: 5px; margin-bottom: 10px;">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Change Photo') }}</h4>
                                </div>
                                <div class="col-lg-7">
                                    <input type="file" class="input-field" name="photo">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right">
                                    <h4 class="heading">{{ __('Message') }} *</h4>
                                </div>
                                <div class="col-lg-7">
                                    <textarea class="input-field" name="message" required>{{ $testimonial->message }}</textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-7 text-center">
                                    <button class="addProductSubmit-btn" type="submit">{{ __('Update Testimonial') }}</button>
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