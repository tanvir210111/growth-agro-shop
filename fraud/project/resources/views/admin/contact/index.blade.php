@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Contact Messages') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="javascript:;">{{ __('Contact Messages') }}</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="product-area">
        <div class="row">
            <div class="col-lg-12">
                <div class="mr-table allproduct">
                    
                    {{-- সাকসেস মেসেজ এলার্ট --}}
                    @if(Session::has('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" style="background: #28a745; color: #fff; border: none;">
                            <strong>সফল!</strong> {{ Session::get('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover" style="width:100%">
                            <thead>
                                <tr style="background: #ff6600; color: #fff;">
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Subject') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $row)
                                <tr>
                                    <td>{{ $row->name }}</td>
                                    <td>{{ $row->email }}</td>
                                    <td>{{ $row->subject ?? 'No Subject' }}</td>
                                    <td>{{ $row->created_at ? $row->created_at->format('d M, Y') : 'N/A' }}</td>
                                    <td>
                                        <div class="action-list" style="display: flex; gap: 5px;">
                                            {{-- ভিউ বাটন --}}
                                            <a href="javascript:;" class="btn btn-info btn-sm text-white" 
                                               data-toggle="modal" data-target="#contactModal{{ $row->id }}" 
                                               style="background: #17a2b8; border: none; border-radius: 3px;" title="View Message">
                                                <i class="fas fa-eye"></i> View
                                            </a>

                                            {{-- ডিলিট বাটন --}}
                                            <a href="{{ route('admin.contact.delete', $row->id) }}" 
                                               onclick="return confirm('আপনি কি নিশ্চিত যে এই মেসেজটি ডিলিট করতে চান?')" 
                                               class="btn btn-danger btn-sm" style="border-radius: 3px;">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="contactModal{{ $row->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content" style="border-radius: 15px; border: none;">
                                            <div class="modal-header" style="background: #ff6600; color: #fff; border-radius: 15px 15px 0 0;">
                                                <h5 class="modal-title fw-bold"><i class="fas fa-envelope-open-text me-2"></i> মেসেজ বিস্তারিত</h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="fw-bold text-muted small text-uppercase">প্রেরকের নাম:</label>
                                                    <p class="h6 fw-bold" style="color: #1a1a1a;">{{ $row->name }}</p>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-12">
                                                        <label class="fw-bold text-muted small text-uppercase">ইমেইল ঠিকানা:</label>
                                                        <p class="fw-bold text-primary">{{ $row->email }}</p>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="fw-bold text-muted small text-uppercase">বিষয় (Subject):</label>
                                                    <p>{{ $row->subject ?? 'N/A' }}</p>
                                                </div>
                                                <hr>
                                                <div class="mb-0">
                                                    <label class="fw-bold text-muted small text-uppercase">মূল মেসেজ:</label>
                                                    <div class="p-3 bg-light mt-2" style="border-radius: 10px; border-left: 4px solid #ff6600; white-space: pre-wrap;">
                                                        {{ $row->message }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer" style="border: none;">
                                                <button type="button" class="btn btn-secondary px-4 shadow-sm" data-dismiss="modal" style="border-radius: 8px;">বন্ধ করুন</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">কোনো কন্টাক্ট মেসেজ পাওয়া যায়নি।</td>
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
    .mr-table table th { vertical-align: middle; }
    .mr-table table td { vertical-align: middle; }
    .modal-backdrop { opacity: 0.5 !important; }
    .action-list .btn { display: inline-flex; align-items: center; justify-content: center; }
</style>
@endsection