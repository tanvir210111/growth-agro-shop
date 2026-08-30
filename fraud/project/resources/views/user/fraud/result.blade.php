@extends('layouts.front')

@section('meta')
    <title>Fraud Result - {{ $phone }}</title>
@endsection

@section('contents')
@php
    $couriers = ['steadfast','pathao','redx','paperfly','carrybee'];
    $agg = $report['aggregate'] ?? [];
@endphp
<section class="page-hero">
    <div class="container">
        <h1>Report — <span>{{ $phone }}</span></h1>
        <p>Success Ratio: {{ $agg['success_ratio'] ?? 0 }}% · Cancel Ratio: {{ $agg['cancel_ratio'] ?? 0 }}%</p>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3"><div class="info-card p-3 text-center"><div class="small text-muted">Total</div><h3>{{ $agg['total_deliveries'] ?? 0 }}</h3></div></div>
            <div class="col-6 col-md-3"><div class="info-card p-3 text-center"><div class="small text-muted">Success</div><h3 style="color:#2ecc71">{{ $agg['total_success'] ?? 0 }}</h3></div></div>
            <div class="col-6 col-md-3"><div class="info-card p-3 text-center"><div class="small text-muted">Cancel</div><h3 style="color:#e74c3c">{{ $agg['total_cancel'] ?? 0 }}</h3></div></div>
            <div class="col-6 col-md-3"><div class="info-card p-3 text-center"><div class="small text-muted">Success %</div><h3 style="color:var(--accent)">{{ $agg['success_ratio'] ?? 0 }}%</h3></div></div>
        </div>

        <div class="row g-3">
            @foreach($couriers as $courier)
                @php $data = $report[$courier] ?? ['success'=>0,'cancel'=>0,'total'=>0,'success_ratio'=>0]; @endphp
                <div class="col-md-4 col-lg">
                    <div class="info-card p-3 h-100">
                        <h6 class="text-uppercase mb-2">{{ $courier }}</h6>
                        <div class="small text-muted">Success: {{ $data['success'] ?? 0 }}</div>
                        <div class="small text-muted">Cancel: {{ $data['cancel'] ?? 0 }}</div>
                        <div class="small text-muted">Total: {{ $data['total'] ?? 0 }}</div>
                        <div class="small">Ratio: <strong>{{ $data['success_ratio'] ?? 0 }}%</strong></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('user.fraud.index') }}" class="btn-cd btn-cd-primary">আবার চেক</a>
            <a href="{{ route('user.fraud.logs') }}" class="btn-cd btn-cd-ghost">হিস্ট্রি</a>
        </div>
    </div>
</section>
@endsection
