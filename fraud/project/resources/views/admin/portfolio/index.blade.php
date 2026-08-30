@extends('layouts.admin')
@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <h4 class="heading">{{ __('Portfolio') }}</h4>
    </div>
    <div class="product-area">
        <div class="row">
            <div class="col-lg-12">
                <div class="mr-table allproduct p-4 bg-white shadow-sm">
                    <div class="d-flex justify-content-between mb-4">
                        <h5 class="sub-title">{{ __('Project List') }}</h5>
                        <a class="add-btn" href="{{ route('admin.portfolio.create') }}" style="background:#2d3274;color:#fff;padding:8px 15px;border-radius:4px;">+ Add New Project</a>
                    </div>
                    @include('includes.admin.form-both')
                    <table class="table modern-table" width="100%">
                        <thead>
                            <tr class="text-muted small">
                                <th>{{ __('PHOTO') }}</th>
                                <th>{{ __('TITLE') }}</th>
                                <th>{{ __('CATEGORY') }}</th>
                                <th class="text-right">{{ __('OPTIONS') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($portfolios as $data)
                            <tr>
                                <td><img src="{{ asset('assets/images/portfolio/'.$data->photo) }}" width="60" style="border-radius:4px;"></td>
                                <td class="font-weight-bold">{{ $data->title }}</td>
                                <td><span class="badge badge-info">{{ $data->category->name }}</span></td>
                                <td class="text-right">
                                    <div class="d-flex justify-content-end" style="gap:10px;">
                                        <a href="{{ route('admin.portfolio.edit', $data->id) }}" class="btn btn-primary btn-sm rounded-circle"><i class="fas fa-edit"></i></a>
                                        
                                        <form action="{{ route('admin.portfolio.destroy', $data->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm rounded-circle" onclick="return confirm('Are you sure?')"><i class="fas fa-trash-alt"></i></button>
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
@endsection