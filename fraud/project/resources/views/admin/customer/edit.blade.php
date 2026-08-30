@extends('layouts.admin')

@section('content')
<div class="content-area" style="background-color: #f8f9fc; padding: 30px;">
    <div class="mr-breadcrumb mb-4">
        <h4 class="heading font-weight-bold" style="color: #2d3274;">
            <i class="fas fa-user-edit mr-2"></i>{{ __('Edit Customer') }}
        </h4>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-body p-5">
            <form action="{{ route('admin.customer.update', $customer->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold" style="color: #2d3274;">{{ __('Customer Name') }}</label>
                        <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required style="border-radius: 10px;">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold" style="color: #2d3274;">{{ __('Phone Number') }}</label>
                        <input type="text" name="phone" class="form-control" value="{{ $customer->phone }}" required style="border-radius: 10px;">
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="font-weight-bold" style="color: #2d3274;">{{ __('Address') }}</label>
                    <textarea name="address" class="form-control" rows="3" style="border-radius: 10px;">{{ $customer->address }}</textarea>
                </div>

                <div class="text-right">
                    <a href="{{ route('admin.customer.index') }}" class="btn btn-light px-4 mr-2" style="border-radius: 10px;">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary px-5" style="background-color: #2d3274; border: none; border-radius: 10px;">
                        {{ __('Update Customer') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection