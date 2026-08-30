@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading"><i class="fas fa-server"></i> {{ __('WHM Servers') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li>{{ __('Hosting Management') }}</li>
                    <li>{{ __('Servers') }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="product-area">
        <div class="row">
            <div class="col-lg-12">
                <div class="mr-table allproduct">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <h5 class="m-0 font-weight-bold text-primary">{{ __('Server List') }}</h5>
                            <button class="add-btn btn-sm" data-toggle="modal" data-target="#addServerModal">
                                <i class="fas fa-plus"></i> {{ __('Add New Server') }}
                            </button>
                        </div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>{{ __('Server Name') }}</th>
                                            <th>{{ __('Hostname / IP') }}</th>
                                            <th>{{ __('Username') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($servers as $server)
                                        <tr>
                                            <td class="font-weight-bold">{{ $server->name }}</td>
                                            <td><code>{{ $server->hostname }}</code></td>
                                            <td><span class="badge badge-secondary">{{ $server->username }}</span></td>
                                            <td>
                                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> Online</span>
                                            </td>
                                            <td>
                                                <div class="action-list d-flex">
                                                    {{-- Login Button --}}
                                                    <a href="{{ route('admin.hosting.servers.login', $server->id) }}" target="_blank" class="btn btn-info btn-sm mr-2 shadow-sm" title="Login to WHM">
                                                        <i class="fas fa-sign-in-alt"></i> {{ __('Login') }}
                                                    </a>
                                                    
                                                    {{-- Edit Button --}}
                                                    <button class="btn btn-warning btn-sm mr-2 shadow-sm" data-toggle="modal" data-target="#editModal{{$server->id}}" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    {{-- Delete Button --}}
                                                    <a href="javascript:;" data-toggle="modal" data-target="#deleteModal{{$server->id}}" class="btn btn-danger btn-sm shadow-sm" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="editModal{{$server->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-warning text-dark">
                                                        <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>{{ __('Edit Server') }}: {{ $server->name }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ route('admin.hosting.servers.update', $server->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body p-4">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group mb-3">
                                                                        <label class="font-weight-bold">{{ __('Server Name') }} <span class="text-danger">*</span></label>
                                                                        <input type="text" name="name" class="form-control" value="{{ $server->name }}" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group mb-3">
                                                                        <label class="font-weight-bold">{{ __('Hostname / IP') }} <span class="text-danger">*</span></label>
                                                                        <input type="text" name="hostname" class="form-control" value="{{ $server->hostname }}" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold">{{ __('Username') }} <span class="text-danger">*</span></label>
                                                                <input type="text" name="username" class="form-control" value="{{ $server->username }}" required>
                                                            </div>
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold">{{ __('API Token / Access Hash') }} <span class="text-danger">*</span></label>
                                                                <textarea name="access_hash" class="form-control" rows="5" required>{{ $server->access_hash }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">{{ __('Cancel') }}</button>
                                                            <button type="submit" class="btn btn-primary shadow-sm px-4">{{ __('Update Changes') }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="deleteModal{{$server->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header border-0">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body text-center pb-5">
                                                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                                        <h4 class="mb-3">Are you sure?</h4>
                                                        <p class="text-muted">You are about to delete this server. This action cannot be undone.</p>
                                                    </div>
                                                    <div class="modal-footer justify-content-center border-0">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <a href="{{ route('admin.hosting.servers.delete', $server->id) }}" class="btn btn-danger">Confirm Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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

<div class="modal fade" id="addServerModal" tabindex="-1" role="dialog" aria-labelledby="addServerModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="exampleModalLongTitle"><i class="fas fa-plus-circle mr-2"></i>{{ __('Add New WHM Server') }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.hosting.servers.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">{{ __('Server Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. USA Cloud Server 1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">{{ __('Hostname / IP Address') }} <span class="text-danger">*</span></label>
                                <input type="text" name="hostname" class="form-control" placeholder="e.g. 192.168.1.1 or s1.yourserver.com" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">{{ __('WHM Root/Reseller Username') }} <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" placeholder="Enter WHM username" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold">{{ __('API Token / Access Hash') }} <span class="text-danger">*</span></label>
                        <textarea name="access_hash" class="form-control" rows="5" placeholder="Paste your WHM API token here..." required></textarea>
                        <div class="alert alert-info mt-2 py-2 px-3 border-0" style="font-size: 13px;">
                            <i class="fas fa-info-circle mr-1"></i> <strong>How to get this?</strong> WHM > Development > Manage API Tokens
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary shadow-sm px-4">{{ __('Save Server') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .card { border-radius: 10px; }
    .add-btn { background: #2d3274; color: #fff; border-radius: 50px; padding: 7px 20px; transition: 0.3s; border: none; cursor: pointer; }
    .add-btn:hover { background: #3e4491; }
    .badge { padding: 6px 12px; border-radius: 4px; font-weight: 500; }
    code { background: #f4f4f4; padding: 2px 6px; color: #e83e8c; border-radius: 4px; }
    .table thead th { border-top: none; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }
    .modal-content { border-radius: 12px; overflow: hidden; }
    .action-list .btn { border-radius: 5px; }
</style>

@endsection