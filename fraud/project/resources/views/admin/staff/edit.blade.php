@extends('layouts.load') 

@section('styles')
<style>
    /* মডালের ভেতরের মেইন কন্টেইনার স্টাইল */
    .edit-modal-wrapper {
        background: #fff;
        padding: 10px 20px 30px;
        border-radius: 8px;
    }

    /* হেডার ডিজাইন */
    .modal-header-custom {
        text-align: center;
        margin-bottom: 30px;
        position: relative;
    }
    .modal-header-custom::after {
        content: '';
        display: block;
        width: 50px;
        height: 3px;
        background: #4e73df;
        margin: 10px auto 0;
        border-radius: 2px;
    }

    /* প্রোফাইল ইমেজ ডিজাইন */
    .edit-profile-img-box {
        width: 120px;
        height: 120px;
        margin: 0 auto 15px;
        position: relative;
    }
    .edit-profile-img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #f8f9fc;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .edit-camera-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: #4e73df;
        color: #fff;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 2px solid #fff;
        transition: 0.3s;
    }
    .edit-camera-btn:hover {
        background: #224abe;
        transform: scale(1.1);
    }

    /* ইনপুট ফিল্ড ডিজাইন */
    .form-label-custom {
        font-size: 12px;
        font-weight: 700;
        color: #5a5c69;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    .input-group-text {
        background-color: #f1f3f9;
        border: 1px solid #d1d3e2;
        color: #4e73df;
    }
    .form-control-custom {
        height: 42px;
        border: 1px solid #d1d3e2;
        font-size: 14px;
    }
    .form-control-custom:focus {
        box-shadow: none;
        border-color: #4e73df;
    }

    /* SMS সেটিংস বক্স */
    .sms-config-box {
        background: #f8f9fc;
        border: 1px dashed #4e73df;
        border-radius: 10px;
        padding: 20px;
        margin-top: 25px;
    }
    .sms-header {
        font-size: 14px;
        font-weight: 700;
        color: #2e59d9;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }
</style>
@endsection

@section('content')

<div class="edit-modal-wrapper">
    
    <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
    
    @include('includes.admin.form-error')
    @include('includes.admin.form-success')

    <form id="geniusformdata" action="{{ route('admin.staff.update', $data->id) }}" method="POST" enctype="multipart/form-data">
        {{csrf_field()}}

        <div class="modal-header-custom">
            <div class="edit-profile-img-box">
                <div id="image-preview" class="edit-profile-img" style="background: url({{ $data->photo ? asset('assets/images/admin/'.$data->photo) : asset('assets/images/noimage.png') }}) no-repeat center center; background-size: cover;"></div>
                <label for="image-upload" class="edit-camera-btn">
                    <i class="fas fa-camera" style="font-size: 14px;"></i>
                </label>
                <input type="file" name="photo" id="image-upload" class="d-none">
            </div>
            <h4 class="font-weight-bold text-dark mb-0">{{ $data->name }}</h4>
            <span class="badge badge-primary px-3 mt-1">{{ $data->designation }}</span>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label-custom">{{ __('Full Name') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>
                    <input type="text" class="form-control form-control-custom" name="name" value="{{ $data->name }}" required>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label-custom">{{ __('Designation') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                    </div>
                    <input type="text" class="form-control form-control-custom" name="designation" value="{{ $data->designation }}" required>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label-custom">{{ __('Email') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>
                    <input type="email" class="form-control form-control-custom" name="email" value="{{ $data->email }}" required>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label-custom">{{ __('Phone') }}</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                    </div>
                    <input type="text" class="form-control form-control-custom" name="phone" value="{{ $data->phone }}" required>
                </div>
            </div>

            <div class="col-md-12 mb-3">
                <label class="form-label-custom">{{ __('Password') }} <small class="text-muted text-lowercase">(leave empty to keep current)</small></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    </div>
                    <input type="password" class="form-control form-control-custom" name="password" placeholder="Enter new password">
                </div>
            </div>
        </div>

        <div class="sms-config-box">
            <div class="sms-header">
                <i class="fas fa-wallet mr-2"></i> {{ __('Balance & SMS Rates') }}
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="form-label-custom text-success">{{ __('Balance') }}</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-success text-white border-0">৳</span>
                        </div>
                        <input type="number" step="0.01" class="form-control form-control-custom font-weight-bold" name="wallet_balance" value="{{ $data->wallet_balance }}">
                    </div>
                </div>

                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="form-label-custom">{{ __('Non-Masking') }}</label>
                    <div class="input-group">
                        <input type="number" step="0.01" class="form-control form-control-custom" name="non_masking_rate" value="{{ $data->non_masking_rate }}">
                        <div class="input-group-append">
                            <span class="input-group-text" style="font-size: 10px;">TK</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label-custom">{{ __('Masking') }}</label>
                    <div class="input-group">
                        <input type="number" step="0.01" class="form-control form-control-custom" name="masking_rate" value="{{ $data->masking_rate }}">
                        <div class="input-group-append">
                            <span class="input-group-text" style="font-size: 10px;">TK</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary px-5 py-2 addProductSubmit-btn" style="border-radius: 30px; font-weight: 600;">
                    <i class="fas fa-check-circle mr-2"></i> {{ __('Update Profile') }}
                </button>
            </div>
        </div>

    </form>
</div>

@endsection