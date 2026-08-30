@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Why Choose Us') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.why-choose-us.index') }}">{{ __('Why Choose Us') }}</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="product-area">
        <div class="row">
            <div class="col-lg-12">
                <div class="mr-table allproduct">
                    
                    <div class="header-with-btn d-flex justify-content-between align-items-center mb-4 px-3">
                        <h5 class="sub-title m-0">{{ __('Manage Section') }}</h5>
                        <a class="add-btn" href="{{ route('admin.why-choose-us.create') }}">
                            <i class="fas fa-plus"></i> {{ __('Add New Feature') }}
                        </a>
                    </div>

                    {{-- সাকসেস মেসেজ --}}
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
                                    <th width="15%">{{ __('Image') }}</th>
                                    <th width="10%">{{ __('Icon') }}</th>
                                    <th width="20%">{{ __('Title') }}</th>
                                    <th width="35%">{{ __('Description') }}</th>
                                    <th width="20%" class="text-right">{{ __('Options') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($features as $feature)
                                <tr>
                                    <td class="align-middle">
                                        <div class="img-container">
                                            @if($feature->image)
                                                {{-- public ফোল্ডার না থাকায় url() ব্যবহার করা হয়েছে --}}
                                                <img src="{{ url('assets/images/why-choose-us/'.$feature->image) }}" alt="Feature Image">
                                            @else
                                                <img src="{{ url('assets/images/noimage.png') }}" alt="No Image">
                                            @endif
                                        </div>
                                    </td>

                                    <td class="align-middle text-center">
                                        <div class="icon-box">
                                            <i class="{{ $feature->icon }} text-primary"></i>
                                        </div>
                                    </td>

                                    <td class="align-middle font-weight-bold">
                                        {{ $feature->title }}
                                    </td>

                                    <td class="align-middle text-muted">
                                        {{ Str::limit($feature->description, 80) }}
                                    </td>

                                    <td class="align-middle text-right">
                                        <div class="action-btns">
                                            {{-- এডিট বাটন --}}
                                            <a href="{{ route('admin.why-choose-us.edit', $feature->id) }}" class="btn-action edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            {{-- ডিলিট বাটন --}}
                                            <a href="javascript:;" data-toggle="modal" data-target="#confirm-delete-{{ $feature->id }}" class="btn-action delete" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>

                                        {{-- Delete Modal --}}
                                        <div class="modal fade" id="confirm-delete-{{ $feature->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="text-danger mb-3"><i class="fas fa-exclamation-triangle fa-2x"></i></div>
                                                        <h6>{{ __('Are you sure?') }}</h6>
                                                        <p><small>{{ __('This action cannot be undone.') }}</small></p>
                                                        <div class="d-flex justify-content-center mt-3" style="gap: 10px;">
                                                            <button type="button" class="btn btn-light btn-sm px-3" data-dismiss="modal">{{ __('No') }}</button>
                                                            <form action="{{ route('admin.why-choose-us.destroy', $feature->id) }}" method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm px-3">{{ __('Delete') }}</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted p-5">{{ __('No Data Found') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* টেবিল এবং রো ডিজাইন */
    .modern-table {
        border-collapse: separate;
        border-spacing: 0 10px;
    }
    .modern-table thead th {
        border: none;
        color: #777;
        font-weight: 600;
        background: #f9f9f9;
        padding: 15px;
    }
    .modern-table tbody tr {
        background: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    .modern-table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .modern-table tbody td {
        padding: 15px;
        border: none;
    }

    /* ইমেজ বক্স */
    .img-container {
        width: 70px;
        height: 50px;
        overflow: hidden;
        border: 1px solid #eee;
        border-radius: 4px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* আইকন বক্স */
    .icon-box {
        background: #f0f7ff;
        width: 35px;
        height: 35px;
        line-height: 35px;
        border-radius: 50%;
        display: inline-block;
        font-size: 16px;
    }

    /* অ্যাকশন বাটন ডিজাইন */
    .action-btns {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }
    .btn-action {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: 0.3s;
        text-decoration: none;
    }
    .btn-action.edit {
        background: #e8f0fe;
        color: #1a73e8;
    }
    .btn-action.edit:hover {
        background: #1a73e8;
        color: #fff;
    }
    .btn-action.delete {
        background: #fde8e8;
        color: #d93025;
    }
    .btn-action.delete:hover {
        background: #d93025;
        color: #fff;
    }

    /* অ্যাড বাটন */
    .header-with-btn .add-btn {
        background: #2d3274;
        color: #fff;
        padding: 8px 18px;
        border-radius: 4px;
        font-size: 14px;
        text-decoration: none;
        transition: 0.3s;
    }
    .header-with-btn .add-btn:hover {
        background: #1e2251;
    }
</style>
@endsection