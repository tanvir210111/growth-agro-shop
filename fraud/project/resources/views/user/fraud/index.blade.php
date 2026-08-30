@extends('layouts.front')

@section('meta')
    <title>Fraud Checker - {{ $gs->title }}</title>
@endsection

@section('contents')
<section class="page-hero">
    <div class="container">
        <h1>Courier <span>Fraud Checker</span></h1>
        <p>কাস্টমারের ডেলিভারি হিস্ট্রি চেক করুন — Steadfast, Pathao, RedX, Paperfly, Carrybee</p>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="info-card p-4 p-md-5">
                    @if(Session::has('error'))
                        <div class="alert alert-danger">{{ Session::get('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form action="{{ route('user.fraud.check') }}" method="POST">
                        @csrf
                        <label class="mb-2 fw-bold">মোবাইল নাম্বার</label>
                        <input type="text" name="phone" class="form-control mb-3" value="{{ old('phone') }}" placeholder="017XXXXXXXX" maxlength="11" required>
                        <button type="submit" class="btn-cd btn-cd-primary w-100">Check Now</button>
                    </form>

                    <div class="mt-3 d-flex justify-content-between small">
                        <a href="{{ route('user.fraud.logs') }}" style="color:var(--accent)">আমার হিস্ট্রি</a>
                        <span class="text-muted">
                            @if(($user->fraud_check_daily_limit ?? 0) > 0)
                                Today: {{ (int)$user->today_check_count }} / {{ $user->fraud_check_daily_limit }}
                            @endif
                        </span>
                    </div>
                </div>

                @if($recent->count())
                <div class="mt-4">
                    <h5 class="mb-3">Recent Checks</h5>
                    @foreach($recent as $row)
                        <div class="info-card p-3 mb-2 d-flex justify-content-between">
                            <code>{{ $row->phone }}</code>
                            <span>Success {{ $row->success_ratio }}% · Cancel {{ $row->cancel_ratio }}%</span>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
