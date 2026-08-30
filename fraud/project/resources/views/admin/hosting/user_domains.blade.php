@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading"><i class="fas fa-server"></i> {{ __('Customer Domains') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li>{{ __('Hosting') }}</li>
                    <li>{{ __('Domains') }}</li>
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
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="card shadow-sm border-0 rounded-lg">
                        {{-- হেডার ও সার্চ অপশন --}}
                        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
                            <h5 class="m-0 font-weight-bold text-dark">{{ __('Domain List') }}</h5>
                            
                            {{-- সার্চ ফর্ম --}}
                            <form action="{{ route('admin.user.domains') }}" method="GET" class="form-inline mt-2 mt-md-0">
                                <div class="input-group search-box">
                                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-0 bg-light" placeholder="Domain, Name or Email..." style="height: 40px; border-radius: 20px 0 0 20px; padding-left: 20px;">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit" style="border-radius: 0 20px 20px 0; padding: 0 20px;"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="width: 100%;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="pl-4">{{ __('Client') }}</th>
                                            <th>{{ __('Domain Name') }}</th>
                                            <th>{{ __('Registration') }}</th>
                                            <th>{{ __('Expiry') }}</th>
                                            <th class="text-center">{{ __('Status Control') }}</th>
                                            <th class="text-right pr-4">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($domains as $domain)
                                        <tr>
                                            <td class="pl-4 align-middle">
                                                <div class="font-weight-bold text-dark">{{ $domain->user->name ?? 'Guest' }}</div>
                                                <small class="text-muted">{{ $domain->user->email ?? 'N/A' }}</small>
                                            </td>

                                            <td class="align-middle">
                                                <a href="http://{{ $domain->domain }}" target="_blank" class="text-primary font-weight-bold">{{ $domain->domain }}</a>
                                                <div class="small text-muted">৳ {{ $domain->recurring_amount }}</div>
                                            </td>

                                            <td class="align-middle">{{ \Carbon\Carbon::parse($domain->registration_date)->format('d M, Y') }}</td>
                                            
                                            <td class="align-middle">
                                                <span class="{{ \Carbon\Carbon::parse($domain->next_due_date)->isPast() ? 'text-danger font-weight-bold' : 'text-success' }}">
                                                    {{ \Carbon\Carbon::parse($domain->next_due_date)->format('d M, Y') }}
                                                </span>
                                            </td>

                                            {{-- স্ট্যাটাস কন্ট্রোল - সরাসরি সিলেক্ট বক্স (নো ড্রপডাউন ঝামেলা) --}}
                                            <td class="text-center align-middle">
                                                <form action="{{ route('admin.user.domains.update', $domain->id) }}" method="POST" class="d-inline-block">
                                                    @csrf
                                                    <select name="status" onchange="this.form.submit()" class="form-control form-control-sm custom-select-status {{ $domain->status == 'Active' ? 'text-success border-success' : ($domain->status == 'Pending' ? 'text-warning border-warning' : 'text-danger border-danger') }}" style="width: 120px; font-weight: bold; border-radius: 20px;">
                                                        <option value="Active" {{ $domain->status == 'Active' ? 'selected' : '' }}>Active</option>
                                                        <option value="Pending" {{ $domain->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                        <option value="Suspended" {{ $domain->status == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                                        <option value="Expired" {{ $domain->status == 'Expired' ? 'selected' : '' }}>Expired</option>
                                                    </select>
                                                </form>
                                            </td>

                                            {{-- অ্যাকশন বাটনসমূহ --}}
                                            <td class="text-right pr-4 align-middle">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#nsModal{{ $domain->id }}" title="Nameservers"><i class="fas fa-server"></i></button>
                                                    <a href="https://who.is/whois/{{ $domain->domain }}" target="_blank" class="btn btn-sm btn-outline-info" title="WHOIS"><i class="fas fa-search"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#deleteModal{{ $domain->id }}" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- নেমসার্ভার আপডেট মডাল --}}
                                        <div class="modal fade" id="nsModal{{ $domain->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow-lg">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title font-weight-bold" style="color:#fff">Nameservers: {{ $domain->domain }}</h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <form action="{{ route('admin.user.domains.update', $domain->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body p-4 bg-light">
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3"><label class="small font-weight-bold">NS 1</label><input type="text" name="ns1" class="form-control" value="{{ $domain->ns1 }}"></div>
                                                                <div class="col-md-6 mb-3"><label class="small font-weight-bold">NS 2</label><input type="text" name="ns2" class="form-control" value="{{ $domain->ns2 }}"></div>
                                                                <div class="col-md-6 mb-3"><label class="small font-weight-bold">NS 3</label><input type="text" name="ns3" class="form-control" value="{{ $domain->ns3 }}"></div>
                                                                <div class="col-md-6 mb-3"><label class="small font-weight-bold">NS 4</label><input type="text" name="ns4" class="form-control" value="{{ $domain->ns4 }}"></div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Changes</button></div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- ডিলিট মডাল --}}
                                        <div class="modal fade" id="deleteModal{{ $domain->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow-lg">
                                                    <div class="modal-body p-5 text-center">
                                                        <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                                                        <h4 class="font-weight-bold">Delete Domain?</h4>
                                                        <p class="text-muted">Domain: <strong>{{ $domain->domain }}</strong><br>Are you sure? This action is permanent.</p>
                                                        <div class="d-flex justify-content-center mt-4">
                                                            <button type="button" class="btn btn-light border mr-3 px-4" data-dismiss="modal">Cancel</button>
                                                            <a href="{{ route('admin.user.domains.delete', $domain->id) }}" class="btn btn-danger px-4">Yes, Delete</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @empty
                                        <tr><td colspan="6" class="text-center py-5"><h6 class="text-muted">No domains found.</h6></td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- পেজিনেশন --}}
                            @if($domains->hasPages())
                                <div class="p-3 border-top d-flex justify-content-end bg-light">
                                    {{ $domains->appends(['search' => request('search')])->links('pagination::bootstrap-4') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card { border-radius: 12px; overflow: hidden; }
    .custom-select-status { height: 35px; cursor: pointer; border: 1px solid #ddd; padding: 5px 10px; font-size: 13px; }
    .custom-select-status:focus { box-shadow: none; }
    .avatar-sm { width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; }
    .avatar-title { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 14px; }
    .bg-soft-primary { background-color: rgba(78, 115, 223, 0.1) !important; }
</style>
@endsection