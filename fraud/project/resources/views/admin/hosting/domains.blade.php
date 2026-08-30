@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading"><i class="fas fa-globe"></i> {{ __('Domain Pricing') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li>{{ __('Hosting Management') }}</li>
                    <li>{{ __('Domain Pricing') }}</li>
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
                            <h5 class="m-0 font-weight-bold text-primary">{{ __('Extension List') }}</h5>
                            <button class="add-btn btn-sm" data-toggle="modal" data-target="#addDomainModal">
                                <i class="fas fa-plus"></i> {{ __('Add New Extension') }}
                            </button>
                        </div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>{{ __('Extension') }}</th>
                                            <th>{{ __('Registration') }}</th>
                                            <th>{{ __('Renewal') }}</th>
                                            <th>{{ __('Transfer') }}</th>
                                            <th class="text-center">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($extensions as $ext)
                                        <tr>
                                            <td class="font-weight-bold text-primary">{{ $ext->extension }}</td>
                                            <td>
                                                <span class="badge badge-light border">৳ {{ $ext->registration_price }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-light border">৳ {{ $ext->renewal_price }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-light border">৳ {{ $ext->transfer_price }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="action-list">
                                                    {{-- Edit Button --}}
                                                    <a href="javascript:;" data-toggle="modal" data-target="#editDomain{{ $ext->id }}" class="btn btn-warning btn-sm shadow-sm mr-1" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    {{-- Delete Button --}}
                                                    <a href="javascript:;" data-toggle="modal" data-target="#deleteModal{{$ext->id}}" class="btn btn-danger btn-sm shadow-sm" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="editDomain{{ $ext->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-warning text-white">
                                                        <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>{{ __('Edit Domain Pricing') }}</h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ route('admin.hosting.domains.update', $ext->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body p-4 text-left">
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold">{{ __('Extension') }}</label>
                                                                <input type="text" name="extension" class="form-control" value="{{ $ext->extension }}" required>
                                                            </div>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group mb-3">
                                                                        <label class="font-weight-bold">{{ __('Registration') }}</label>
                                                                        <div class="input-group">
                                                                            <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                                                            <input type="number" name="registration_price" class="form-control" value="{{ $ext->registration_price }}" required>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group mb-3">
                                                                        <label class="font-weight-bold">{{ __('Renewal') }}</label>
                                                                        <div class="input-group">
                                                                            <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                                                            <input type="number" name="renewal_price" class="form-control" value="{{ $ext->renewal_price }}" required>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group mb-3">
                                                                        <label class="font-weight-bold">{{ __('Transfer') }}</label>
                                                                        <div class="input-group">
                                                                            <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                                                            <input type="number" name="transfer_price" class="form-control" value="{{ $ext->transfer_price }}" required>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">{{ __('Close') }}</button>
                                                            <button type="submit" class="btn btn-primary shadow-sm px-4">{{ __('Update') }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="deleteModal{{$ext->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header border-0">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body text-center pb-5">
                                                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                                        <h4 class="mb-3">Are you sure?</h4>
                                                        <p class="text-muted">You are about to delete <strong>{{ $ext->extension }}</strong> pricing.</p>
                                                    </div>
                                                    <div class="modal-footer justify-content-center border-0">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <a href="{{ route('admin.hosting.domains.delete', $ext->id) }}" class="btn btn-danger">Confirm Delete</a>
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

<div class="modal fade" id="addDomainModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>{{ __('Add New Extension') }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.hosting.domains.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">{{ __('Extension') }} <span class="text-danger">*</span></label>
                        <input type="text" name="extension" class="form-control" placeholder="e.g. .com" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">{{ __('Registration') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                    <input type="number" name="registration_price" class="form-control" placeholder="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">{{ __('Renewal') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                    <input type="number" name="renewal_price" class="form-control" placeholder="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold">{{ __('Transfer') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                    <input type="number" name="transfer_price" class="form-control" placeholder="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary shadow-sm px-4">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .card { border-radius: 10px; }
    .add-btn { background: #2d3274; color: #fff; border-radius: 50px; padding: 7px 20px; transition: 0.3s; border: none; cursor: pointer; }
    .add-btn:hover { background: #3e4491; }
    .badge { font-size: 14px; padding: 8px 12px; font-weight: 500; }
    .table thead th { border-top: none; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; vertical-align: middle; }
    .table tbody td { vertical-align: middle; }
    .modal-content { border-radius: 12px; overflow: hidden; }
    .input-group-text { background-color: #f8f9fa; border-color: #ced4da; font-weight: bold; }
</style>

@endsection