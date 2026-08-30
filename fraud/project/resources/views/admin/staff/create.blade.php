@extends('layouts.admin')

@section('styles')
<style>
    /* Custom Modern Styles */
    .user-create-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    
    /* Profile Image Upload Style */
    .profile-upload-wrapper {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto 30px;
    }
    .profile-upload-preview {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background-size: cover;
        background-position: center;
        border: 5px solid #f8f9fc;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .profile-upload-btn {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: #4e73df;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        border: 3px solid white;
    }
    .profile-upload-btn:hover {
        background: #2e59d9;
        transform: scale(1.1);
    }

    /* Form Inputs */
    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #5a5c69;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    .custom-input {
        border-radius: 8px;
        border: 1px solid #d1d3e2;
        padding: 12px 15px;
        height: auto;
        font-size: 0.95rem;
        transition: all 0.2s;
    }
    .custom-input:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    /* SMS Section Box */
    .sms-settings-box {
        background: #f8f9fc;
        border-radius: 12px;
        padding: 25px;
        border: 1px dashed #4e73df;
        margin-top: 20px;
    }
    .sms-title {
        color: #4e73df;
        font-weight: 700;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    .sms-title i {
        margin-right: 10px;
        font-size: 1.2rem;
    }
</style>
@endsection

@section('content')

<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('Create New User') }}</h1>
        <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm"></i> {{ __('Back') }}
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card user-create-card">
                <div class="card-body p-5">
                    
                    <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                    @include('includes.admin.form-error')
                    @include('includes.admin.form-success')

                    <form id="geniusformdata" action="{{ route('admin.staff.store') }}" method="POST" enctype="multipart/form-data">
                        {{csrf_field()}}

                        <div class="profile-upload-wrapper">
                            <div id="image-preview" class="profile-upload-preview" style="background-image: url({{ asset('assets/images/noimage.png') }});"></div>
                            <label for="image-upload" class="profile-upload-btn" title="Upload Photo">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" name="photo" id="image-upload" class="d-none">
                        </div>
                        <div class="text-center mb-5">
                            <h5 class="font-weight-bold text-dark">{{ __('Upload Profile Picture') }}</h5>
                            <small class="text-muted">{{ __('Supported files: jpeg, png, jpg') }}</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-4">
                                <label class="form-label">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control custom-input" name="name" placeholder="Enter Full Name" required>
                            </div>

                            <div class="col-md-6 form-group mb-4">
                                <label class="form-label">{{ __('Designation') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control custom-input" name="designation" placeholder="e.g. Sales Manager" required>
                            </div>

                            <div class="col-md-6 form-group mb-4">
                                <label class="form-label">{{ __('Email Address') }} <span class="text-danger">*</span></label>
                                <input type="email" class="form-control custom-input" name="email" placeholder="name@example.com" required>
                            </div>

                            <div class="col-md-6 form-group mb-4">
                                <label class="form-label">{{ __('Phone Number') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control custom-input" name="phone" placeholder="017xxxxxxxx" required>
                            </div>

                            <div class="col-md-12 form-group mb-4">
                                <label class="form-label">{{ __('Password') }} <span class="text-danger">*</span></label>
                                <input type="password" class="form-control custom-input" name="password" placeholder="Set a strong password" required>
                            </div>
                        </div>

                        <div class="sms-settings-box">
                            <div class="sms-title">
                                <i class="fas fa-wallet"></i> {{ __('SMS & Balance Configuration') }}
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label class="form-label text-dark">{{ __('Opening Balance') }}</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;">৳</span>
                                        </div>
                                        <input type="number" step="0.01" class="form-control custom-input border-left-0" name="wallet_balance" placeholder="0.00" value="0.00" style="border-radius: 0 8px 8px 0;">
                                    </div>
                                </div>

                                <div class="col-md-4 form-group">
                                    <label class="form-label text-dark">{{ __('Non-Masking Rate') }}</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control custom-input" name="non_masking_rate" placeholder="0.50" value="0.50">
                                        <div class="input-group-append">
                                            <span class="input-group-text" style="border-radius: 0 8px 8px 0;">TK</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 form-group">
                                    <label class="form-label text-dark">{{ __('Masking Rate') }}</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control custom-input" name="masking_rate" placeholder="0.80" value="0.80">
                                        <div class="input-group-append">
                                            <span class="input-group-text" style="border-radius: 0 8px 8px 0;">TK</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-5">
                            <button type="submit" class="btn btn-primary btn-lg px-5 addProductSubmit-btn" style="border-radius: 30px;">
                                <i class="fas fa-check-circle mr-2"></i> {{ __('Create User Account') }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection