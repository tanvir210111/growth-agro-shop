@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Courier Accounts') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.fraud.index') }}">Fraud Checker</a></li>
                    <li><a href="javascript:;">Courier Accounts</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="product-area">
        @if(Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ Session::get('success') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif
        @include('includes.admin.form-error')

        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle"></i>
            এখান থেকে Pathao, SteadFast, RedX, PaperFly, CarryBee অ্যাকাউন্টের <strong>User ID</strong> ও <strong>Password</strong> পরিবর্তন করতে পারবেন।
            পাসওয়ার্ড ফিল্ড খালি রাখলে আগের পাসওয়ার্ড অপরিবর্তিত থাকবে।
        </div>

        <form action="{{ route('admin.fraud.couriers.update') }}" method="POST">
            @csrf
            <div class="row">
                @foreach($couriers as $slug => $courier)
                    <div class="col-lg-6 mb-4">
                        <div class="mr-table allproduct p-4 h-100">
                            <h5 class="mb-3">
                                <i class="{{ $courier['icon'] }} text-primary mr-1"></i>
                                {{ $courier['label'] }}
                            </h5>

                            @foreach($courier['fields'] as $field)
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold">{{ $field['label'] }}</label>
                                    @if($field['type'] === 'password')
                                        <div class="input-group">
                                            <input
                                                type="password"
                                                name="{{ $field['key'] }}"
                                                id="{{ $field['key'] }}"
                                                class="form-control input-field"
                                                value=""
                                                placeholder="নতুন পাসওয়ার্ড (খালি = আগেরটা রাখুন)"
                                                autocomplete="new-password"
                                            >
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="{{ $field['key'] }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @if(!empty($values[$field['key']]))
                                            <small class="text-muted">বর্তমান পাসওয়ার্ড সেট আছে (••••••••)</small>
                                        @else
                                            <small class="text-danger">এখনো পাসওয়ার্ড সেট করা নেই</small>
                                        @endif
                                    @else
                                        <input
                                            type="text"
                                            name="{{ $field['key'] }}"
                                            class="form-control input-field"
                                            value="{{ old($field['key'], $values[$field['key']] ?? '') }}"
                                            placeholder="{{ $field['label'] }}"
                                            autocomplete="off"
                                        >
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-right mb-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save"></i> সব অ্যাকাউন্ট সেভ করুন
                </button>
                <a href="{{ route('admin.fraud.index') }}" class="btn btn-light ml-2">ফিরে যান</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.toggle-pass').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(this.getAttribute('data-target'));
        if (!input) return;
        var icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            if (icon) icon.className = 'fas fa-eye';
        }
    });
});
</script>
@endsection
