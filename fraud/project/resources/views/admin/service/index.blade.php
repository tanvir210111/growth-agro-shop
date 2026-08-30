@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Our Services') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="javascript:;">{{ __('Manage Services') }}</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="product-area">
        <div class="row">
            <div class="col-lg-12">
                <div class="mr-table allproduct">
                    <div class="header-with-btn d-flex justify-content-between align-items-center mb-4 px-3">
                        <h5 class="sub-title m-0">{{ __('Services List') }}</h5>
                        <a class="add-btn" href="{{ route('admin.service.create') }}">
                            <i class="fas fa-plus"></i> {{ __('Add New Service') }}
                        </a>
                    </div>
                    @if(Session::has('success'))
                        <div class="alert alert-success mx-3">{{ Session::get('success') }}</div>
                    @endif
                    <div class="table-responsive-sm px-3">
                        <table class="table modern-table" width="100%">
                            <thead>
                                <tr>
                                    <th>{{ __('Icon') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th class="text-right">{{ __('Options') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($services as $service)
                                <tr>
                                    <td><div class="icon-circle"><i class="{{ $service->icon }}"></i></div></td>
                                    <td class="font-weight-bold">{{ $service->title }}</td>
                                    <td>{{ Str::limit($service->description, 50) }}</td>
                                    <td class="text-right">
                                        <div class="action-btns">
                                            <a href="{{ route('admin.service.edit', $service->id) }}" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('admin.service.destroy', $service->id) }}" method="POST" style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-action delete" onclick="return confirm('Are you sure?')"><i class="fas fa-trash-alt"></i></button>
                                            </form>
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
    .icon-circle { width: 40px; height: 40px; background: #e7f1ff; color: #007bff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .modern-table tbody tr { background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .btn-action { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px; border:none; cursor:pointer; }
    .edit { background: #e8f0fe; color: #1a73e8; margin-right: 5px; }
    .delete { background: #fde8e8; color: #d93025; }
</style>
@endsection