@extends('layouts.admin')

@section('content')
@php
    $couriers = ['steadfast','pathao','redx','paperfly','carrybee'];
    $agg = $report['aggregate'] ?? [];
@endphp
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">Fraud Report — {{ $phone }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.fraud.index') }}">Fraud Checker</a></li>
                    <li><a href="javascript:;">Result</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="product-area px-3">
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="p-3 bg-white border rounded text-center">
                    <div class="text-muted small">Total Deliveries</div>
                    <h3 class="mb-0">{{ $agg['total_deliveries'] ?? 0 }}</h3>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="p-3 bg-white border rounded text-center">
                    <div class="text-muted small">Success</div>
                    <h3 class="mb-0 text-success">{{ $agg['total_success'] ?? 0 }}</h3>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="p-3 bg-white border rounded text-center">
                    <div class="text-muted small">Cancel</div>
                    <h3 class="mb-0 text-danger">{{ $agg['total_cancel'] ?? 0 }}</h3>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="p-3 bg-white border rounded text-center">
                    <div class="text-muted small">Success Ratio</div>
                    <h3 class="mb-0" style="color:#ff6600;">{{ $agg['success_ratio'] ?? 0 }}%</h3>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($couriers as $courier)
                @php $data = $report[$courier] ?? ['success'=>0,'cancel'=>0,'total'=>0,'success_ratio'=>0]; @endphp
                <div class="col-md-4 col-lg mb-3">
                    <div class="p-3 bg-white border rounded h-100">
                        <h6 class="text-uppercase fw-bold mb-3">{{ $courier }}</h6>
                        <p class="mb-1 small">Success: <strong>{{ $data['success'] ?? 0 }}</strong></p>
                        <p class="mb-1 small">Cancel: <strong>{{ $data['cancel'] ?? 0 }}</strong></p>
                        <p class="mb-1 small">Total: <strong>{{ $data['total'] ?? 0 }}</strong></p>
                        <p class="mb-0 small">Ratio: <strong>{{ $data['success_ratio'] ?? 0 }}%</strong></p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">
            <a href="{{ route('admin.fraud.index') }}" class="btn btn-primary">আবার চেক করুন</a>
            <a href="{{ route('admin.fraud.logs') }}" class="btn btn-light">History</a>
        </div>
    </div>
</div>
@endsection
