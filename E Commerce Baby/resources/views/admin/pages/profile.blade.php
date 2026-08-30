@extends('admin.layouts.master')

@section('title', 'Admin Profile')

@section('content')
<div class="content-header">
    <div>
        <h1><i class="fa fa-user-shield" style="color:var(--admin-primary); margin-right:8px;"></i> Admin Profile & Password</h1>
        <p style="font-size:12px; color:var(--admin-text-muted); margin-top:2px;">Update your login credentials, name, and profile photo</p>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a> / <span>Profile</span>
    </div>
</div>

<div style="max-width:650px;">
    <div class="box">
        <div class="box-header">
            <h3 class="box-title"><i class="fa fa-user-edit" style="color:var(--admin-primary); margin-right:6px;"></i> Edit Profile Information</h3>
        </div>
        <div class="box-body">
            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control-custom" value="{{ old('name', $admin->name) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address (Login Username) *</label>
                    <input type="email" name="email" class="form-control-custom" value="{{ old('email', $admin->email) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Profile Avatar Image</label>
                    <input type="file" name="avatar" class="form-control-custom" accept="image/*">
                </div>

                <hr style="border:0; border-top:1px solid #f3f4f6; margin:20px 0;">

                <h3 class="box-title" style="margin-bottom:15px;"><i class="fa fa-lock" style="color:var(--admin-primary); margin-right:6px;"></i> Change Password</h3>

                <div class="form-group">
                    <label class="form-label">New Password (leave empty to keep current password)</label>
                    <input type="password" name="password" class="form-control-custom" placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control-custom" placeholder="••••••••">
                </div>

                <button type="submit" class="btn-admin btn-admin-primary" style="width:100%; justify-content:center; padding:12px; font-size:14px;">
                    <i class="fa fa-save"></i> Update Profile & Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
