@extends('admin.layouts.master')

@section('title', 'Delivery & Store Settings')

@section('content')
<div class="content-header">
    <div>
        <h1><i class="fa fa-cogs" style="color:var(--admin-primary); margin-right:8px;"></i> Delivery Charges & Store Settings</h1>
        <p style="font-size:12px; color:var(--admin-text-muted); margin-top:2px;">Configure Bangladesh delivery rates, store branding, helpline, and free delivery thresholds</p>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a> / <span>Settings</span>
    </div>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
        <!-- Left: Delivery Charge Configurations -->
        <div class="box" style="border-top-color:#00a65a;">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-truck" style="color:#00a65a; margin-right:6px;"></i> Delivery Rates & Thresholds</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label class="form-label">Inside Dhaka Delivery Fee (৳) *</label>
                    <input type="number" name="delivery_inside_dhaka" class="form-control-custom" value="{{ old('delivery_inside_dhaka', $settings['delivery_inside_dhaka']) }}" required>
                    <small style="color:#6b7280;">Applied automatically when customer selects 'Inside Dhaka' during checkout.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Outside Dhaka Delivery Fee (৳) *</label>
                    <input type="number" name="delivery_outside_dhaka" class="form-control-custom" value="{{ old('delivery_outside_dhaka', $settings['delivery_outside_dhaka']) }}" required>
                    <small style="color:#6b7280;">Applied automatically when customer selects 'Outside Dhaka' during checkout.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Free Delivery Minimum Order (৳) *</label>
                    <input type="number" name="free_delivery_threshold" class="form-control-custom" value="{{ old('free_delivery_threshold', $settings['free_delivery_threshold']) }}" required>
                    <small style="color:#6b7280;">Orders equal to or exceeding this amount receive 100% Free Shipping.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Currency Symbol</label>
                    <input type="text" name="currency_symbol" class="form-control-custom" value="{{ old('currency_symbol', $settings['currency_symbol']) }}">
                </div>
            </div>
        </div>

        <!-- Right: Store Profile & Helpline -->
        <div class="box" style="border-top-color:#3c8dbc;">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-store" style="color:var(--admin-primary); margin-right:6px;"></i> Store Information & Branding</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label class="form-label">Store Brand Name *</label>
                    <input type="text" name="store_name" class="form-control-custom" value="{{ old('store_name', $settings['store_name']) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Customer Support Phone / Helpline *</label>
                    <input type="text" name="store_phone" class="form-control-custom" value="{{ old('store_phone', $settings['store_phone']) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Official Support Email</label>
                    <input type="email" name="store_email" class="form-control-custom" value="{{ old('store_email', $settings['store_email']) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Physical Store / Office Address</label>
                    <textarea name="store_address" rows="2" class="form-control-custom">{{ old('store_address', $settings['store_address']) }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Change Store Logo</label>
                    <input type="file" name="logo" class="form-control-custom" accept="image/*">
                    <div style="margin-top:8px;">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height:48px; object-fit:contain; border:1px solid #e5e7eb; padding:4px 8px; border-radius:4px;">
                    </div>
                </div>
            </div>

            <div class="box-footer">
                <button type="submit" class="btn-admin btn-admin-primary" style="width:100%; justify-content:center; padding:12px; font-size:14px;">
                    <i class="fa fa-save"></i> Save All Settings
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
