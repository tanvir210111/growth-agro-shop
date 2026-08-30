@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <h4 class="heading">{{ __('Edit Team Member') }}</h4>
    </div>

    <div class="add-product-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="product-description">
                    <div class="body-area">
                        @include('includes.admin.form-error') 

                        <form action="{{ route('admin.team.update', $team->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-lg-4 text-right"><h4 class="heading">{{ __('Name') }} *</h4></div>
                                <div class="col-lg-7"><input type="text" class="input-field" name="name" value="{{ $team->name }}" required></div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right"><h4 class="heading">{{ __('Designation') }} *</h4></div>
                                <div class="col-lg-7"><input type="text" class="input-field" name="designation" value="{{ $team->designation }}" required></div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right"><h4 class="heading">{{ __('Current Photo') }}</h4></div>
                                <div class="col-lg-7">
                                    <img src="{{ asset('assets/images/team/'.$team->photo) }}" style="width: 100px; border-radius: 5px; margin-bottom: 10px;">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right"><h4 class="heading">{{ __('Change Photo') }}</h4></div>
                                <div class="col-lg-7"><input type="file" class="input-field" name="photo"></div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right"><h4 class="heading">Facebook</h4></div>
                                <div class="col-lg-7"><input type="url" class="input-field" name="facebook" value="{{ $team->facebook }}"></div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4 text-right"><h4 class="heading">LinkedIn</h4></div>
                                <div class="col-lg-7"><input type="url" class="input-field" name="linkedin" value="{{ $team->linkedin }}"></div>
                            </div>

                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-7 text-center">
                                    <button class="addProductSubmit-btn" type="submit">{{ __('Update Member') }}</button>
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