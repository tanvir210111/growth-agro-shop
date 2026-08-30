@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Team Members') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.team.index') }}">{{ __('Team') }}</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="product-area">
        <div class="row">
            <div class="col-lg-12">
                <div class="mr-table allproduct">
                    
                    <div class="header-with-btn d-flex justify-content-between align-items-center mb-4 px-3">
                        <h5 class="sub-title m-0">{{ __('Manage Members') }}</h5>
                        <a class="add-btn" href="{{ route('admin.team.create') }}">
                            <i class="fas fa-plus"></i> {{ __('Add New Member') }}
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
                                    <th>{{ __('Member') }}</th>
                                    <th>{{ __('Designation') }}</th>
                                    <th>{{ __('Social Links') }}</th>
                                    <th class="text-right">{{ __('Options') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teams as $team)
                                <tr>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <div class="member-img-box mr-3">
                                                <img src="{{ asset('assets/images/team/'.$team->photo) }}" alt="Member">
                                            </div>
                                            <span class="font-weight-bold">{{ $team->name }}</span>
                                        </div>
                                    </td>

                                    <td class="align-middle">
                                        <span class="badge badge-light p-2">{{ $team->designation }}</span>
                                    </td>

                                    <td class="align-middle">
                                        <div class="social-icons-list">
                                            @if($team->facebook)
                                                <a href="{{ $team->facebook }}" target="_blank" class="text-primary mr-2"><i class="fab fa-facebook-f"></i></a>
                                            @endif
                                            @if($team->twitter)
                                                <a href="{{ $team->twitter }}" target="_blank" class="text-info mr-2"><i class="fab fa-twitter"></i></a>
                                            @endif
                                            @if($team->linkedin)
                                                <a href="{{ $team->linkedin }}" target="_blank" class="text-secondary"><i class="fab fa-linkedin-in"></i></a>
                                            @endif
                                            @if(!$team->facebook && !$team->twitter && !$team->linkedin)
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="align-middle text-right">
                                        <div class="action-btns">
                                            <a href="{{ route('admin.team.edit', $team->id) }}" class="btn-action edit" title="Edit"> 
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            
                                            <a href="javascript:;" data-toggle="modal" data-target="#confirm-delete-{{ $team->id }}" class="btn-action delete" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>

                                        <div class="modal fade" id="confirm-delete-{{ $team->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-body text-center p-4">
                                                        <div class="text-danger mb-3"><i class="fas fa-exclamation-triangle fa-2x"></i></div>
                                                        <h6>{{ __('Are you sure?') }}</h6>
                                                        <div class="d-flex justify-content-center mt-3" style="gap: 10px;">
                                                            <button type="button" class="btn btn-light btn-sm px-3" data-dismiss="modal">No</button>
                                                            <form action="{{ route('admin.team.destroy', $team->id) }}" method="POST">
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
/* Modern & Clean CSS (টিম মেম্বারের জন্য অপ্টিমাইজড) */
.modern-table { border-collapse: separate; border-spacing: 0 8px; }
.modern-table thead th { border: none; color: #555; font-size: 13px; text-transform: uppercase; background: #f9f9f9; padding: 12px; }
.modern-table tbody tr { background: #fff; transition: transform 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
.modern-table tbody tr:hover { background: #fdfdfd; }
.modern-table tbody td { border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 12px; }
.modern-table tbody td:first-child { border-left: 1px solid #eee; border-radius: 5px 0 0 5px; }
.modern-table tbody td:last-child { border-right: 1px solid #eee; border-radius: 0 5px 5px 0; }

/* মেম্বার ইমেজের গোল ডিজাইন */
.member-img-box { width: 45px; height: 45px; border: 2px solid #f0f0f0; border-radius: 50%; overflow: hidden; display: inline-block; }
.member-img-box img { width: 100%; height: 100%; object-fit: cover; }

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

.header-with-btn .add-btn { background: #2d3274; color: #fff; padding: 8px 15px; border-radius: 4px; font-size: 13px; text-decoration: none; }
.social-icons-list a { font-size: 14px; transition: 0.2s; }
.social-icons-list a:hover { opacity: 0.7; }
</style>
@endsection