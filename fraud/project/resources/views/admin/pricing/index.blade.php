@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <h4 class="heading">{{ __('Pricing Plans') }}</h4>
    </div>

    <div class="product-area">
        <div class="row">
            <div class="col-lg-4">
                <div class="add-product-content shadow-sm bg-white p-4">
                    <h5 class="sub-title mb-4 font-weight-bold" style="color: #2d3274;">{{ __('Add New Plan') }}</h5>
                    @include('includes.admin.form-both') 
                    
                    <form action="{{ route('admin.pricing.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="mb-2 font-weight-bold">Plan Title *</label>
                            <input type="text" class="form-control" name="title" placeholder="Ex: স্টার্টার" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="mb-2 font-weight-bold">Price *</label>
                            <input type="text" class="form-control" name="price" placeholder="Ex: ৩,৫০০" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="mb-2 font-weight-bold">Features (Comma Separated) *</label>
                            <textarea class="form-control" name="features" rows="4" placeholder="৫ পেজের ল্যান্ডিং পেজ, মোবাইল ফ্রেন্ডলি ডিজাইন" required></textarea>
                            <small class="text-primary">ফিচারগুলো কমা (,) দিয়ে আলাদা করুন।</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="mb-2 font-weight-bold">Order Link (URL)</label>
                            <input type="url" class="form-control" name="order_link" placeholder="https://example.com/order">
                        </div>

                        <div class="custom-control custom-checkbox mb-4">
                            <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1">
                            <label class="custom-control-label font-weight-bold" for="is_featured">Highlight as Best Value?</label>
                        </div>

                        <button class="btn btn-primary w-100" type="submit" style="background: #2d3274; border: none; font-weight: bold;">
                            {{ __('CREATE PLAN') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="mr-table allproduct p-4 bg-white shadow-sm">
                    <h5 class="sub-title mb-4 font-weight-bold" style="color: #2d3274;">{{ __('All Plans') }}</h5>
                    <table class="table modern-table w-100">
                        <thead>
                            <tr class="text-muted small">
                                <th>TITLE</th>
                                <th>PRICE</th>
                                <th>FEATURED</th>
                                <th class="text-right">OPTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plans as $plan)
                            <tr class="border-bottom">
                                <td class="py-3 font-weight-bold">{{ $plan->title }}</td>
                                <td class="py-3">{{ $plan->currency }}{{ $plan->price }}</td>
                                <td class="py-3">
                                    {!! $plan->is_featured ? '<span class="badge badge-success">BEST VALUE</span>' : '<span class="badge badge-secondary">Normal</span>' !!}
                                </td>
                                <td class="py-3 text-right">
                                    <div class="action-list d-flex justify-content-end" style="gap:10px;">
                                        <a href="{{ route('admin.pricing.edit', $plan->id) }}" class="btn btn-primary btn-sm rounded-circle"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('admin.pricing.destroy', $plan->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm rounded-circle" onclick="return confirm('Delete Plan?')"><i class="fas fa-trash-alt"></i></button>
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