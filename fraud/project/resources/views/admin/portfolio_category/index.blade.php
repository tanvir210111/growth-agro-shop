@extends('layouts.admin')
@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <h4 class="heading">{{ __('Portfolio Categories') }}</h4>
    </div>
    <div class="product-area">
        <div class="row">
            <div class="col-lg-4">
                <div class="add-product-content shadow-sm bg-white p-4">
                    <h5 class="sub-title font-weight-bold" style="color: #2d3274;">{{ __('Add New Category') }}</h5>
                    @include('includes.admin.form-both') 
                    <form action="{{ route('admin.portfolio-category.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="mb-2 font-weight-bold">{{ __('Category Name *') }}</label>
                            <input type="text" class="input-field form-control" name="name" placeholder="Ex: Web Design" required>
                        </div>
                        <button class="addProductSubmit-btn w-100" type="submit" style="background: #2d3274; color: #fff; padding: 10px; border: none; border-radius: 4px;">{{ __('Create Category') }}</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="mr-table allproduct p-4 bg-white shadow-sm" style="border-radius: 4px;">
                    <h5 class="sub-title font-weight-bold" style="color: #2d3274;">{{ __('All Categories') }}</h5>
                    <table class="table modern-table" width="100%">
                        <thead>
                            <tr class="text-uppercase small text-muted" style="background: #f9f9f9;">
                                <th>NAME</th>
                                <th>SLUG</th>
                                <th class="text-right">OPTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $cat)
                            <tr class="border-bottom">
                                <td class="py-3 font-weight-bold">{{ $cat->name }}</td>
                                <td class="py-3"><code>{{ $cat->slug }}</code></td>
                                <td class="py-3 text-right">
                                    <form action="{{ route('admin.portfolio-category.destroy', $cat->id) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm rounded-circle shadow-sm" onclick="return confirm('Delete Category?')"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <img src="https://cdn-icons-png.flaticon.com/512/1055/1055823.png" style="width: 60px; opacity: 0.2;">
                                    <p class="mt-2">{{ __('No Categories Found in Database.') }}</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection