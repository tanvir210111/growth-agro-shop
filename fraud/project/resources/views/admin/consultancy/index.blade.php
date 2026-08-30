@extends('layouts.admin')

@section('content')
<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __('Free Consultancy Requests') }}</h4>
                <ul class="links">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="javascript:;">{{ __('Consultancy') }}</a></li>
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
                                    <th>{{ __('Phone') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $row)
                                <tr>
                                    <td>{{ $row->name }}</td>
                                    <td>{{ $row->phone }}</td>
                                    <td>
                                        @if($row->status == 0)
                                            <span class="badge badge-warning" style="background: #ffc107; color: #000; padding: 5px 10px; border-radius: 4px;">Pending</span>
                                        @else
                                            <span class="badge badge-success" style="background: #28a745; color: #fff; padding: 5px 10px; border-radius: 4px;">Completed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-list" style="display: flex; gap: 5px;">
                                            {{-- ভিউ বাটন (এই বাটনে ক্লিক করলে মডেল ওপেন হবে) --}}
                                            <a href="javascript:;" class="btn btn-info btn-sm text-white" 
                                               data-toggle="modal" data-target="#consultancyModal{{ $row->id }}" 
                                               style="background: #17a2b8; border: none; border-radius: 3px;" title="View Details">
                                                <i class="fas fa-eye"></i> View
                                            </a>

                                            @if($row->status == 0)
                                            <a href="{{ route('admin.consultancy.status', [$row->id, 1]) }}" class="btn btn-success btn-sm" title="Mark as Completed">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            @else
                                            <a href="{{ route('admin.consultancy.status', [$row->id, 0]) }}" class="btn btn-warning btn-sm text-white" title="Mark as Pending">
                                                <i class="fas fa-undo"></i>
                                            </a>
                                            @endif
                                            
                                            <a href="{{ route('admin.consultancy.delete', $row->id) }}" onclick="return confirm('আপনি কি নিশ্চিত যে এটি ডিলিট করতে চান?')" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="consultancyModal{{ $row->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content" style="border-radius: 15px; border: none;">
                                            <div class="modal-header" style="background: #ff6600; color: #fff; border-radius: 15px 15px 0 0;">
                                                <h5 class="modal-title fw-bold"><i class="fas fa-info-circle me-2"></i> রিকোয়েস্ট ডিটেইলস</h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="fw-bold text-muted small text-uppercase">গ্রাহকের নাম:</label>
                                                    <p class="h6 fw-bold" style="color: #1a1a1a;">{{ $row->name }}</p>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="fw-bold text-muted small text-uppercase">ফোন নাম্বার:</label>
                                                        <p class="fw-bold">{{ $row->phone }}</p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="fw-bold text-muted small text-uppercase">ইমেইল:</label>
                                                        <p>{{ $row->email ?? 'N/A' }}</p>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="fw-bold text-muted small text-uppercase">আবেদনের সময়:</label>
                                                    <p class="small">{{ $row->created_at ? $row->created_at->format('d M, Y - h:i A') : 'N/A' }}</p>
                                                </div>
                                                <hr>
                                                <div class="mb-0">
                                                    <label class="fw-bold text-muted small text-uppercase">বার্তা / মেসেজ:</label>
                                                    <div class="p-3 bg-light mt-2" style="border-radius: 10px; border-left: 4px solid #ff6600; font-style: italic;">
                                                        {{ $row->message ?? 'কোনো মেসেজ প্রদান করা হয়নি।' }}
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
                                    <td colspan="4" class="text-center py-5 text-muted">কোনো কনসালটেন্সি রিকোয়েস্ট পাওয়া যায়নি।</td>
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
    /* টেবিল এবং বাটনের ফিনিশিং */
    .mr-table table th { vertical-align: middle; }
    .mr-table table td { vertical-align: middle; }
    .action-list .btn { display: inline-flex; align-items: center; justify-content: center; }
    .modal-backdrop { opacity: 0.5 !important; }
</style>
@endsection