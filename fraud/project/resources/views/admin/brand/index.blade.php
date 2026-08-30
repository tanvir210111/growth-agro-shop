@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Brands') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.brand.index') }}">{{ __('Brands') }}</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="product-area">
        <div class="row">
            <div class="col-lg-12">
                <div class="mr-table allproduct">
                    
                    <div class="header-with-btn d-flex justify-content-between align-items-center mb-4 px-3">
                        <h5 class="sub-title m-0">{{ __('Manage Brands') }}</h5>
                        <a class="add-btn" href="{{ route('admin.brand.create') }}">
                            <i class="fas fa-plus"></i> {{ __('Add New Brand') }}
                        </a>
                    </div>

                    @if(Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show mx-3" role="alert">
                        {{ Session::get('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    <div class="table-responsive-sm px-3">
                        <table id="geniustable" class="table modern-table" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ __('Brand Name') }}</th>
                                    <th>{{ __('Logo') }}</th>
                                    <th>{{ __('URL') }}</th>
                                    <th class="text-right">{{ __('Options') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($brands as $brand)
                                <tr>
                                    <td class="align-middle font-weight-bold">{{ $brand->name }}</td>
                                    <td class="align-middle">
                                        <div class="brand-img-box">
                                            <img src="{{ asset('assets/images/brands/'.$brand->photo) }}" alt="Logo">
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        @if($brand->url)
                                            <a href="{{ $brand->url }}" target="_blank" class="text-primary small text-truncate d-inline-block" style="max-width: 150px;">
                                                <i class="fas fa-link mr-1"></i> Visit Site
                                            </a>
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-right">
                                        <div class="action-btns">
                                            <a href="{{ route('admin.brand.edit', $brand->id) }}" class="btn-action edit" title="Edit"> 
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            
                                            <a href="javascript:;" data-toggle="modal" data-target="#confirm-delete-{{ $brand->id }}" class="btn-action delete" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>

                                        <div class="modal fade" id="confirm-delete-{{ $brand->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="text-danger mb-3"><i class="fas fa-exclamation-triangle fa-2x"></i></div>
                                                        <h6>{{ __('Are you sure?') }}</h6>
                                                        <div class="d-flex justify-content-center mt-3" style="gap: 10px;">
                                                            <button type="button" class="btn btn-light btn-sm px-3" data-dismiss="modal">No</button>
                                                            <form action="{{ route('admin.brand.destroy', $brand->id) }}" method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm px-3">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern & Clean CSS */
.modern-table { border-collapse: separate; border-spacing: 0 8px; }
.modern-table thead th { border: none; color: #555; font-size: 13px; text-transform: uppercase; background: #f9f9f9; padding: 12px; }
.modern-table tbody tr { background: #fff; transition: transform 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
.modern-table tbody tr:hover { background: #fdfdfd; }
.modern-table tbody td { border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 12px; }
.modern-table tbody td:first-child { border-left: 1px solid #eee; border-radius: 5px 0 0 5px; }
.modern-table tbody td:last-child { border-right: 1px solid #eee; border-radius: 0 5px 5px 0; }

.brand-img-box { background: #fff; padding: 5px; border: 1px solid #f0f0f0; border-radius: 4px; display: inline-block; }
.brand-img-box img { height: 35px; width: auto; object-fit: contain; }

.action-btns { display: flex; justify-content: flex-end; gap: 6px; }
.btn-action { 
    width: 32px; height: 32px; border-radius: 50%; 
    display: flex; align-items: center; justify-content: center; 
    font-size: 12px; text-decoration: none !important; transition: 0.3s;
}
.btn-action.edit { background: #e8f0fe; color: #1a73e8; }
.btn-action.edit:hover { background: #1a73e8; color: #fff; }
.btn-action.delete { background: #fde8e8; color: #d93025; border: none; cursor: pointer; }
.btn-action.delete:hover { background: #d93025; color: #fff; }

.header-with-btn .add-btn { background: #2d3274; color: #fff; padding: 8px 15px; border-radius: 4px; font-size: 13px; }
</style>
@endsection