@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading"><i class="fas fa-box-open"></i> {{ __('Hosting Plans') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li>{{ __('Hosting Management') }}</li>
                    <li>{{ __('Plans') }}</li>
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
                            <h5 class="m-0 font-weight-bold text-primary">{{ __('Package List') }}</h5>
                            <button class="add-btn btn-sm" data-toggle="modal" data-target="#addPlanModal">
                                <i class="fas fa-plus"></i> {{ __('Create New Package') }}
                            </button>
                        </div>
                        
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>{{ __('Package Name') }}</th>
                                            <th>{{ __('Category') }}</th>
                                            <th>{{ __('Free Domain') }}</th>
                                            <th>{{ __('Price (Yearly)') }}</th>
                                            <th>{{ __('Server') }}</th>
                                            <th class="text-center">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($plans as $plan)
                                        
                                        {{-- [FIX START] ডাটা টাইপ হ্যান্ডলিং --}}
                                        @php
                                            // ডাটাবেস থেকে আসা ডাটা চেক করা হচ্ছে
                                            $rawExtensions = $plan->free_domain_extensions;
                                            $allowedIds = [];

                                            if (is_array($rawExtensions)) {
                                                // যদি ইতিমধ্যে অ্যারে হয়
                                                $allowedIds = $rawExtensions;
                                            } elseif (is_string($rawExtensions)) {
                                                // যদি স্ট্রিং (JSON) হয়
                                                $decoded = json_decode($rawExtensions, true);
                                                if (is_array($decoded)) {
                                                    $allowedIds = $decoded;
                                                }
                                            }
                                        @endphp
                                        {{-- [FIX END] --}}

                                        <tr>
                                            <td>
                                                <div class="font-weight-bold text-primary">{{ $plan->name }}</div>
                                                <small class="text-muted"><i class="fas fa-cube"></i> WHM: {{ $plan->whm_package_name }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-info px-2 py-1">{{ $plan->category }}</span>
                                            </td>
                                            <td>
                                                @if($plan->free_domain)
                                                    @php
                                                        // $extensions ভেরিয়েবল কন্ট্রোলার থেকে আসছে
                                                        $names = $extensions->whereIn('id', $allowedIds)->pluck('extension')->implode(', ');
                                                    @endphp
                                                    
                                                    <span class="badge badge-success" data-toggle="tooltip" title="Allowed: {{ $names }}">
                                                        <i class="fas fa-check"></i> Yes
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary"><i class="fas fa-times"></i> No</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="font-weight-bold text-dark">৳ {{ $plan->price_yearly }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-light border">{{ $plan->server->name ?? 'N/A' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="action-list">
                                                    {{-- Edit Button --}}
                                                    <a href="javascript:;" data-toggle="modal" data-target="#editPlan{{ $plan->id }}" class="btn btn-warning btn-sm shadow-sm mr-1" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    {{-- Delete Button --}}
                                                    <a href="javascript:;" data-toggle="modal" data-target="#deleteModal{{$plan->id}}" class="btn btn-danger btn-sm shadow-sm" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Edit Modal --}}
                                        <div class="modal fade" id="editPlan{{ $plan->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-warning text-white">
                                                        <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>{{ __('Edit Package') }}: {{ $plan->name }}</h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ route('admin.hosting.plans.update', $plan->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body p-4 text-left">
                                                            <div class="row">
                                                                <div class="col-md-6 form-group">
                                                                    <label class="font-weight-bold">প্যাকেজের নাম</label>
                                                                    <input type="text" name="name" class="form-control" value="{{ $plan->name }}" required>
                                                                </div>
                                                                <div class="col-md-6 form-group">
                                                                    <label class="font-weight-bold">WHM প্যাকেজ নাম</label>
                                                                    <input type="text" name="whm_package_name" class="form-control" value="{{ $plan->whm_package_name }}" readonly>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6 form-group">
                                                                    <label class="font-weight-bold">সার্ভার</label>
                                                                    <select name="server_id" class="form-control">
                                                                        @foreach($servers as $srv)
                                                                            <option value="{{ $srv->id }}" {{ $srv->id == $plan->server_id ? 'selected' : '' }}>{{ $srv->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6 form-group">
                                                                    <label class="font-weight-bold">ক্যাটাগরি</label>
                                                                    <select name="category" class="form-control">
                                                                        <option value="Shared" {{ $plan->category == 'Shared' ? 'selected' : '' }}>Shared Hosting</option>
                                                                        <option value="Reseller" {{ $plan->category == 'Reseller' ? 'selected' : '' }}>Reseller Hosting</option>
                                                                        <option value="VPS" {{ $plan->category == 'VPS' ? 'selected' : '' }}>VPS Hosting</option>
                                                                        <option value="Dedicated" {{ $plan->category == 'Dedicated' ? 'selected' : '' }}>Dedicated Server</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="card bg-light border p-3 my-3">
                                                                <div class="custom-control custom-checkbox mb-2">
                                                                    <input type="checkbox" class="custom-control-input free-domain-check" id="editFreeDomain{{ $plan->id }}" name="free_domain" value="1" {{ $plan->free_domain ? 'checked' : '' }} data-target="#editExtSection{{$plan->id}}">
                                                                    <label class="custom-control-label font-weight-bold text-success" for="editFreeDomain{{ $plan->id }}">
                                                                        <i class="fas fa-gift mr-1"></i> এই প্যাকেজের সাথে ফ্রি ডোমেইন অফার আছে?
                                                                    </label>
                                                                </div>

                                                                <div id="editExtSection{{$plan->id}}" style="{{ $plan->free_domain ? '' : 'display:none;' }}">
                                                                    <label class="font-weight-bold small">কোন এক্সটেনশনগুলো ফ্রি? (Ctrl চেপে একাধিক সিলেক্ট করুন)</label>
                                                                    <select name="free_domain_extensions[]" class="form-control" multiple style="height: 100px;">
                                                                        @foreach($extensions as $ext)
                                                                            {{-- [FIXED] এখানে $allowedIds ব্যবহার করা হচ্ছে যা উপরে প্রসেস করা হয়েছে --}}
                                                                            <option value="{{ $ext->id }}" {{ in_array($ext->id, $allowedIds) ? 'selected' : '' }}>
                                                                                {{ $ext->extension }} ({{ $ext->registration_price }} ৳)
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-6 form-group">
                                                                    <label class="font-weight-bold">মাসিক দাম</label>
                                                                    <div class="input-group">
                                                                        <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                                                        <input type="number" name="price_monthly" class="form-control" value="{{ $plan->price_monthly }}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-6 form-group">
                                                                    <label class="font-weight-bold">বাৎসরিক দাম <span class="text-danger">*</span></label>
                                                                    <div class="input-group">
                                                                        <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                                                        <input type="number" name="price_yearly" class="form-control" value="{{ $plan->price_yearly }}" required>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="font-weight-bold">বিবরণ</label>
                                                                <textarea name="description" class="form-control" rows="3">{{ $plan->description }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary shadow-sm px-4">Update Package</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Delete Modal --}}
                                        <div class="modal fade" id="deleteModal{{$plan->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header border-0">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    </div>
                                                    <div class="modal-body text-center pb-5">
                                                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                                        <h4 class="mb-3">Are you sure?</h4>
                                                        <p class="text-muted">You are about to delete <strong>{{ $plan->name }}</strong>.</p>
                                                    </div>
                                                    <div class="modal-footer justify-content-center border-0">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <a href="{{ route('admin.hosting.plans.delete', $plan->id) }}" class="btn btn-danger">Confirm Delete</a>
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

{{-- Add Plan Modal --}}
<div class="modal fade" id="addPlanModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle mr-2"></i>{{ __('Add New Package') }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.hosting.plans.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">প্যাকেজের নাম <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Starter Plan" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">WHM প্যাকেজ নাম <span class="text-danger">*</span></label>
                            <input type="text" name="whm_package_name" class="form-control" placeholder="e.g. myuser_starter" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">কোন সার্ভারের প্যাকেজ? <span class="text-danger">*</span></label>
                            <select name="server_id" class="form-control">
                                @foreach($servers as $server)
                                    <option value="{{ $server->id }}">{{ $server->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold">প্যাকেজ ক্যাটাগরি <span class="text-danger">*</span></label>
                            <select name="category" class="form-control">
                                <option value="Shared">Shared Hosting</option>
                                <option value="Reseller">Reseller Hosting</option>
                                <option value="VPS">VPS Hosting</option>
                                <option value="Dedicated">Dedicated Server</option>
                            </select>
                        </div>
                    </div>

                    <div class="card bg-light border p-3 my-3">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input free-domain-check" id="addFreeDomain" name="free_domain" value="1" data-target="#addExtSection">
                            <label class="custom-control-label font-weight-bold text-success" for="addFreeDomain">
                                <i class="fas fa-gift mr-1"></i> এই প্যাকেজের সাথে ফ্রি ডোমেইন অফার আছে?
                            </label>
                        </div>

                        <div id="addExtSection" style="display: none;">
                            <label class="font-weight-bold small">কোন এক্সটেনশনগুলো ফ্রি? (Ctrl চেপে একাধিক সিলেক্ট করুন)</label>
                            <select name="free_domain_extensions[]" class="form-control" multiple style="height: 100px;">
                                @foreach($extensions as $ext)
                                    <option value="{{ $ext->id }}">{{ $ext->extension }} ({{ $ext->registration_price }} ৳)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 form-group">
                            <label class="font-weight-bold">মাসিক দাম</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                <input type="number" name="price_monthly" class="form-control" placeholder="0">
                            </div>
                        </div>
                        <div class="col-6 form-group">
                            <label class="font-weight-bold">বাৎসরিক দাম <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                <input type="number" name="price_yearly" class="form-control" placeholder="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">বিবরণ</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="প্যাকেজের বিস্তারিত বিবরণ লিখুন..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary shadow-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary shadow-sm px-4">Save Package</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .card { border-radius: 10px; }
    .add-btn { background: #2d3274; color: #fff; border-radius: 50px; padding: 7px 20px; transition: 0.3s; border: none; cursor: pointer; }
    .add-btn:hover { background: #3e4491; }
    .badge { font-size: 13px; padding: 6px 10px; font-weight: 500; border-radius: 4px; }
    .table thead th { border-top: none; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; vertical-align: middle; }
    .table tbody td { vertical-align: middle; }
    .modal-content { border-radius: 12px; overflow: hidden; }
    .input-group-text { background-color: #f8f9fa; border-color: #ced4da; font-weight: bold; }
    /* Checkbox Styling */
    .custom-checkbox .custom-control-input:checked ~ .custom-control-label::before { background-color: #28a745; border-color: #28a745; }
</style>

<script>
    // Simple Script to Toggle Free Domain Extension Section
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.free-domain-check');
        
        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const targetId = this.getAttribute('data-target');
                const targetDiv = document.querySelector(targetId);
                
                if (this.checked) {
                    targetDiv.style.display = 'block';
                } else {
                    targetDiv.style.display = 'none';
                }
            });
        });
    });
</script>

@endsection