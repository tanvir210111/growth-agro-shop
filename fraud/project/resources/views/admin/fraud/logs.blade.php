@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">Fraud Check History</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.fraud.index') }}">Fraud Checker</a></li>
                    <li><a href="javascript:;">History</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="product-area">
        <div class="mr-table allproduct px-3">
            @if(Session::has('success'))
                <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif

            <form method="GET" class="mb-3 row">
                <div class="col-md-4">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search phone...">
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary">Search</button>
                    <a href="{{ route('admin.fraud.logs') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr style="background:#ff6600;color:#fff;">
                            <th>ID</th>
                            <th>Phone</th>
                            <th>By</th>
                            <th>Total</th>
                            <th>Success %</th>
                            <th>Cancel %</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $row)
                        <tr>
                            <td>{{ $row->id }}</td>
                            <td><code>{{ $row->phone }}</code></td>
                            <td>{{ $row->checked_by }}</td>
                            <td>{{ $row->aggregate_total }}</td>
                            <td>{{ $row->success_ratio }}%</td>
                            <td>{{ $row->cancel_ratio }}%</td>
                            <td class="small">{{ $row->created_at }}</td>
                            <td>
                                <a href="{{ route('admin.fraud.delete', $row->id) }}" class="btn btn-sm btn-danger"
                                   onclick="return confirm('Delete this log?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
