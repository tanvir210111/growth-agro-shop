@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Testimonials') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.testimonial.index') }}">{{ __('Testimonials') }}</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="product-area">
        <div class="row">
            <div class="col-lg-12">
                <div class="mr-table allproduct">
                    
                    <div class="header-with-btn d-flex justify-content-between align-items-center mb-4 px-3">
                        <h5 class="sub-title m-0">{{ __('Manage Testimonials') }}</h5>
                        <a class="add-btn" href="{{ route('admin.testimonial.create') }}">
                            <i class="fas fa-plus"></i> {{ __('Add New Testimonial') }}
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
                                    <th>{{ __('Client') }}</th>
                                    <th>{{ __('Rating') }}</th>
                                    <th>{{ __('Message') }}</th>
                                    <th class="text-right">{{ __('Options') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($testimonials as $testimonial)
                                <tr>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <div class="member-img-box mr-3">
                                                <img src="{{ asset('assets/images/testimonials/'.$testimonial->photo) }}" alt="Photo">
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $testimonial->name }}</div>
                                                <small class="text-muted">{{ $testimonial->designation }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="text-warning">
                                            @for($i=1; $i<=$testimonial->rating; $i++)
                                                <i class="fas fa-star fa-xs"></i>
                                            @endfor
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="text-truncate" style="max-width: 250px;">{{ $testimonial->message }}</div>
                                    </td>
                                    <td class="align-middle text-right">
                                        <div class="action-btns">
                                            <a href="{{ route('admin.testimonial.edit', $testimonial->id) }}" class="btn-action edit" title="Edit"> 
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <a href="javascript:;" data-toggle="modal" data-target="#confirm-delete-{{ $testimonial->id }}" class="btn-action delete" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>

                                        <div class="modal fade" id="confirm-delete-{{ $testimonial->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="text-danger mb-3"><i class="fas fa-exclamation-triangle fa-2x"></i></div>
                                                        <h6>{{ __('Are you sure?') }}</h6>
                                                        <div class="d-flex justify-content-center mt-3" style="gap: 10px;">
                                                            <button type="button" class="btn btn-light btn-sm px-3" data-dismiss="modal">No</button>
                                                            <form action="{{ route('admin.testimonial.destroy', $testimonial->id) }}" method="POST">
                                                                @csrf @method('DELETE')
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
.modern-table { border-collapse: separate; border-spacing: 0 8px; }
.modern-table tbody tr { background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
.member-img-box { width: 45px; height: 45px; border-radius: 50%; overflow: hidden; }
.member-img-box img { width: 100%; height: 100%; object-fit: cover; }
.btn-action { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: 0.3s; }
.btn-action.edit { background: #e8f0fe; color: #1a73e8; }
.btn-action.delete { background: #fde8e8; color: #d93025; border: none; }
</style>
@endsection