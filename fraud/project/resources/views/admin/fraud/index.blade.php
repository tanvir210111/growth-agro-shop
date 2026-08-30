@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Fraud Checker') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="javascript:;">Fraud Checker</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="product-area">
        <div class="row">
            <div class="col-lg-6">
                <div class="mr-table allproduct p-4">
                    <h5 class="mb-3">কাস্টমার মোবাইল নাম্বার চেক করুন</h5>
                    <p class="text-muted small mb-4">Steadfast, Pathao, RedX, Paperfly, Carrybee — সব কুরিয়ার একসাথে।</p>

                    @if(Session::has('error'))
                        <div class="alert alert-danger">{{ Session::get('error') }}</div>
                    @endif
                    @include('includes.admin.form-error')

                    <form action="{{ route('admin.fraud.check') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label>মোবাইল নাম্বার *</label>
                            <input type="text" name="phone" class="form-control input-field" value="{{ old('phone') }}" placeholder="017XXXXXXXX" maxlength="11" required>
                        </div>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-search"></i> Check Now
                        </button>
                        <a href="{{ route('admin.fraud.logs') }}" class="btn btn-light ml-2">History</a>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="mr-table allproduct p-4">
                    <h5 class="mb-3">Recent Checks</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Phone</th>
                                    <th>Success %</th>
                                    <th>Cancel %</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent as $row)
                                <tr>
                                    <td><code>{{ $row->phone }}</code></td>
                                    <td>{{ $row->success_ratio }}%</td>
                                    <td>{{ $row->cancel_ratio }}%</td>
                                    <td class="small">{{ $row->created_at->diffForHumans() }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-muted text-center">কোনো চেক নেই</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
