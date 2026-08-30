@extends('layouts.front')

@section('meta')
    <title>Fraud Check History - {{ $gs->title }}</title>
@endsection

@section('contents')
<section class="page-hero">
    <div class="container">
        <h1>Check <span>History</span></h1>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="table-responsive info-card p-3">
            <table class="table table-dark table-borderless mb-0" style="--bs-table-bg:transparent;color:#fff;">
                <thead>
                    <tr>
                        <th>Phone</th>
                        <th>Total</th>
                        <th>Success %</th>
                        <th>Cancel %</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $row)
                    <tr>
                        <td><code>{{ $row->phone }}</code></td>
                        <td>{{ $row->aggregate_total }}</td>
                        <td>{{ $row->success_ratio }}%</td>
                        <td>{{ $row->cancel_ratio }}%</td>
                        <td class="small">{{ $row->created_at }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">কোনো হিস্ট্রি নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $logs->links() }}</div>
        <a href="{{ route('user.fraud.index') }}" class="btn-cd btn-cd-primary mt-3">নতুন চেক</a>
    </div>
</section>
@endsection
