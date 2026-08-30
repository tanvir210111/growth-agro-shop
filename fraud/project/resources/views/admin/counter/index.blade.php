@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Counter Statistics') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.counter.index') }}">{{ __('Counters') }}</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="product-area">
        <div class="row">
            <div class="col-lg-12">
                <div class="mr-table allproduct">
                    
                    <div class="header-with-btn d-flex justify-content-between align-items-center mb-4 px-3">
                        <h5 class="sub-title m-0">{{ __('Manage Statistics') }}</h5>
                        <a class="add-btn" href="{{ route('admin.counter.create') }}">
                            <i class="fas fa-plus"></i> {{ __('Add New Counter') }}
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
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Count Value') }}</th>
                                    <th class="text-right">{{ __('Options') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($counters as $counter)
                                <tr>
                                    <td class="align-middle">
                                        <span class="font-weight-bold" style="color: #444;">{{ $counter->title }}</span>
                                    </td>

                                    <td class="align-middle">
                                        <span class="badge badge-soft-orange p-2" style="background: #fff0e6; color: #ff6b00; border-radius: 4px; font-weight: 600;">
                                            {{ $counter->count_value }}
                                        </span>
                                    </td>

                                    <td class="align-middle text-right">
                                        <div class="action-btns">
                                            <a href="{{ route('admin.counter.edit', $counter->id) }}" class="btn-action edit" title="Edit"> 
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            
                                            <a href="javascript:;" data-toggle="modal" data-target="#confirm-delete-{{ $counter->id }}" class="btn-action delete" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>

                                        <div class="modal fade" id="confirm-delete-{{ $counter->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="text-danger mb-3"><i class="fas fa-exclamation-triangle fa-2x"></i></div>
                                                        <h6>{{ __('Are you sure?') }}</h6>
                                                        <div class="d-flex justify-content-center mt-3" style="gap: 10px;">
                                                            <button type="button" class="btn btn-light btn-sm px-3" data-dismiss="modal">No</button>
                                                            <form action="{{ route('admin.counter.destroy', $counter->id) }}" method="POST">
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
/* আধুনিক ও ক্লিন সিএসএস */
.modern-table { border-collapse: separate; border-spacing: 0 8px; }
.modern-table thead th { border: none; color: #555; font-size: 13px; text-transform: uppercase; background: #f9f9f9; padding: 12px; }
.modern-table tbody tr { background: #fff; transition: transform 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
.modern-table tbody tr:hover { background: #fdfdfd; }
.modern-table tbody td { border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 15px; }
.modern-table tbody td:first-child { border-left: 1px solid #eee; border-radius: 5px 0 0 5px; }
.modern-table tbody td:last-child { border-right: 1px solid #eee; border-radius: 0 5px 5px 0; }

.action-btns { display: flex; justify-content: flex-end; gap: 6px; }
.btn-action { 
    width: 32px; height: 32px; border-radius: 50%; 
    display: flex; align-items: center; justify-content: center; 
    font-size: 11px; text-decoration: none !important; transition: 0.3s;
}
.btn-action.edit { background: #e8f0fe; color: #1a73e8; }
.btn-action.edit:hover { background: #1a73e8; color: #fff; }
.btn-action.delete { background: #fde8e8; color: #d93025; border: none; cursor: pointer; }
.btn-action.delete:hover { background: #d93025; color: #fff; }

.header-with-btn .add-btn { background: #2d3274; color: #fff; padding: 8px 15px; border-radius: 4px; font-size: 13px; text-decoration: none; }
</style>
@endsection