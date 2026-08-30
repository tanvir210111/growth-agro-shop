@extends('layouts.admin')

@section('styles')
<style>
    /* মডার্ন টেবিল ও ব্যাজ ডিজাইন */
    .table thead th { background: #f8f9fa; border-bottom: 2px solid #dee2e6; color: #334155; font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
    .table tbody td { vertical-align: middle; padding: 15px; border-top: 1px solid #f1f5f9; font-size: 14px; }
    .avatar-circle { width: 38px; height: 38px; border-radius: 50%; background: #4e73df; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 12px; }
    .badge-pill { padding: 6px 12px; border-radius: 50px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
    .status-active { background: #dcfce7; color: #15803d; }
    .status-pending { background: #fef9c3; color: #854d0e; }
    .status-suspended { background: #fee2e2; color: #b91c1c; }
    .status-terminated { background: #334155; color: #fff; }
    .form-label-sm { font-weight: 700; font-size: 12px; color: #4a5568; margin-bottom: 5px; display: block; }
</style>
@endsection

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading"><i class="fas fa-server"></i> {{ __('Hosting Management') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li>{{ __('Manage Hosting Services') }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="product-area">
        <div class="row">
            <div class="col-lg-12">
                <div class="mr-table allproduct">
                    
                    {{-- সাকসেস মেসেজ --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
                            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    {{-- এরর মেসেজ (লগইন না হলে এটি আপনাকে কারণ জানাবে) --}}
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4">
                            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    <div class="card border-0 shadow-sm rounded-lg">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="m-0 font-weight-bold text-dark">{{ __('All Customer Hostings') }}</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="hosting-table" class="table table-hover mb-0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Customer') }}</th>
                                            <th>{{ __('Domain & Package') }}</th>
                                            <th>{{ __('Server & IP') }}</th>
                                            <th>{{ __('Pricing') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th class="text-right">{{ __('Options') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($hostings as $hosting)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle">{{ strtoupper(substr($hosting->user->name ?? 'U', 0, 1)) }}</div>
                                                    <div>
                                                        <div class="font-weight-bold text-dark">{{ $hosting->user->name ?? 'Guest' }}</div>
                                                        <small class="text-muted">{{ $hosting->user->email ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="font-weight-bold text-primary">{{ $hosting->domain }}</div>
                                                <div class="small text-muted"><i class="fas fa-box"></i> {{ $hosting->plan->name ?? 'N/A' }}</div>
                                            </td>
                                            <td>
                                                <span class="badge badge-light border text-dark font-weight-bold">{{ $hosting->ip_address ?? '0.0.0.0' }}</span>
                                                <div class="small text-muted mt-1">{{ $hosting->server->name ?? 'No Server' }}</div>
                                            </td>
                                            <td>
                                                <div class="font-weight-bold">৳ {{ number_format($hosting->amount, 2) }}</div>
                                                <small class="text-muted text-capitalize">{{ $hosting->billing_cycle }}</small>
                                            </td>
                                            <td>
                                                <span class="badge-pill status-{{ strtolower($hosting->status) }}">{{ $hosting->status }}</span>
                                            </td>
                                            <td class="text-right">
                                                <button type="button" class="btn btn-primary btn-sm px-3 rounded-pill shadow-sm" 
                                                        data-toggle="modal" data-target="#manageModal{{$hosting->id}}">
                                                    <i class="fas fa-tools mr-1"></i> Manage
                                                </button>
                                            </td>
                                        </tr>

                                        {{-- ==================== পূর্ণাঙ্গ কনফিগারেশন মডাল ==================== --}}
                                        <div class="modal fade" id="manageModal{{$hosting->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                                <div class="modal-content border-0 shadow-lg rounded-lg">
                                                    <div class="modal-header bg-dark text-white">
                                                        <h6 class="modal-title font-weight-bold">Manage Hosting: {{ $hosting->domain }}</h6>
                                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    
                                                    <div class="modal-body p-4 text-left">
                                                        <form action="{{ route('admin.user.hosting.status', $hosting->id) }}" method="POST">
                                                            @csrf
                                                            <div class="row">
                                                                {{-- ডোমেইন নাম --}}
                                                                <div class="col-md-12 mb-3">
                                                                    <label class="form-label-sm">Domain Name:</label>
                                                                    <input type="text" name="domain" class="form-control" value="{{ $hosting->domain }}" placeholder="example.com" required>
                                                                </div>

                                                                {{-- প্যাকেজ সিলেকশন --}}
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label-sm">Select Package:</label>
                                                                    <select name="plan_id" class="form-control" required>
                                                                        @foreach($plans as $plan)
                                                                            <option value="{{ $plan->id }}" {{ $hosting->plan_id == $plan->id ? 'selected' : '' }}>
                                                                                {{ $plan->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                {{-- সার্ভার সিলেকশন --}}
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label-sm">Assign Server:</label>
                                                                    <select name="server_id" class="form-control" required>
                                                                        @foreach($servers as $server)
                                                                            <option value="{{ $server->id }}" {{ $hosting->server_id == $server->id ? 'selected' : '' }}>
                                                                                {{ $server->name }} ({{ $server->hostname }})
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                {{-- সার্ভার আইপি --}}
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label-sm">Server IP Address:</label>
                                                                    <input type="text" name="ip_address" class="form-control" value="{{ $hosting->ip_address }}" placeholder="e.g. 192.168.1.1">
                                                                </div>

                                                                {{-- সিপ্যানেল ইউজারনেম --}}
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label-sm">cPanel Username:</label>
                                                                    <input type="text" name="username" class="form-control" value="{{ $hosting->username }}" placeholder="cPanel Username">
                                                                </div>
                                                                
                                                                {{-- সিপ্যানেল পাসওয়ার্ড --}}
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label-sm">cPanel Password:</label>
                                                                    <input type="text" name="password" class="form-control" value="{{ $hosting->password }}" placeholder="Password">
                                                                </div>

                                                                {{-- স্ট্যাটাস --}}
                                                                <div class="col-md-6 mb-4">
                                                                    <label class="form-label-sm">Account Status:</label>
                                                                    <select name="status" class="form-control">
                                                                        <option value="Active" {{ $hosting->status == 'Active' ? 'selected' : '' }}>Active</option>
                                                                        <option value="Pending" {{ $hosting->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                                        <option value="Suspended" {{ $hosting->status == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                                                        <option value="Terminated" {{ $hosting->status == 'Terminated' ? 'selected' : '' }}>Terminated</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            
                                                            <button type="submit" class="btn btn-primary btn-block py-2 font-weight-bold shadow-sm rounded-pill">
                                                                <i class="fas fa-save mr-1"></i> Update Configuration
                                                            </button>
                                                        </form>

                                                        <hr class="my-4">

                                                        {{-- সিপ্যানেল অটো লগইন বাটন (SSO) --}}
                                                        @if($hosting->username && $hosting->server)
                                                        <div class="text-center mb-3">
                                                            <p class="small text-muted mb-2">পাসওয়ার্ড ছাড়াই সরাসরি সিপ্যানেলে প্রবেশ করুন:</p>
                                                            <a href="{{ route('admin.user.hosting.cp_login', $hosting->id) }}" target="_blank" class="btn btn-info btn-block text-white font-weight-bold shadow-sm rounded-pill py-2">
                                                                <i class="fas fa-external-link-alt mr-2"></i> One-Click cPanel Login
                                                            </a>
                                                        </div>
                                                        @else
                                                        <div class="alert alert-warning small py-2">
                                                            <i class="fas fa-exclamation-triangle mr-1"></i> লগইন বাটন সক্রিয় করতে ইউজারনেম ও সার্ভার আগে সেভ করুন।
                                                        </div>
                                                        @endif

                                                        <div class="text-center mt-3">
                                                            <a href="{{ route('admin.user.hosting.delete', $hosting->id) }}" 
                                                               onclick="return confirm('সাবধান: এই হোস্টিং অ্যাকাউন্ট চিরতরে মুছে যাবে!')"
                                                               class="text-danger small font-weight-bold">Terminate Account Permanently</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- মডাল শেষ --}}

                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection