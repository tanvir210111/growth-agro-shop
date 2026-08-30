@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">থিম ম্যানেজমেন্ট</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">ড্যাশবোর্ড</a></li>
                <li class="breadcrumb-item active" aria-current="page">থিমসমূহ</li>
            </ol>
        </nav>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list-ul me-2"></i> সকল থিমের তালিকা</h6>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fas fa-plus-circle me-1"></i> নতুন থিম যোগ করুন
            </a>
        </div>
        <div class="card-body">
            <div id="alert-container">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
            
            <div class="table-responsive">
                <table id="geniustable" class="table table-hover border-bottom w-100" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-top-0">ইমেজ</th>
                            <th class="border-top-0">নাম</th>
                            <th class="border-top-0">মূল্য</th>
                            <th class="border-top-0 text-center">অ্যাকশন</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> কনফার্ম ডিলিট</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3 text-danger">
                    <i class="fas fa-trash-alt fa-3x"></i>
                </div>
                <h5 class="font-weight-bold">আপনি কি নিশ্চিত?</h5>
                <p class="text-muted">আপনি এই থিমটি ডিলিট করতে যাচ্ছেন। এটি একবার ডিলিট করলে আর ফিরে পাওয়া যাবে না!</p>
            </div>
            <div class="modal-footer bg-light border-0 justify-content-center">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">বাতিল</button>
                <form action="" method="POST" id="delete-form" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">হ্যাঁ, ডিলিট করুন</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
    /* কাস্টম ডিজাইন এক্সট্রা স্টাইল */
    #geniustable thead th {
        color: #4e73df;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        padding: 15px;
    }
    #geniustable tbody td {
        vertical-align: middle;
        padding: 12px 15px;
    }
    .theme-thumb {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: 0.3s;
    }
    .theme-thumb:hover {
        transform: scale(1.1);
    }
    .price-badge {
        background-color: #f8f9fc;
        color: #1cc88a;
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: bold;
    }
    .btn-action {
        width: 32px;
        height: 32px;
        line-height: 32px;
        padding: 0;
        border-radius: 50%;
        margin: 0 3px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.2rem 0.5rem;
    }
    .spinner-border-sm-custom {
        width: 1rem;
        height: 1rem;
        border-width: 0.15em;
    }
</style>

<script type="text/javascript">
    $(document).ready(function() {
        // DataTable Initialization
        var table = $('#geniustable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            ajax: {
                url: "{{ route('admin.products.datatables') }}",
                type: 'GET',
                error: function (xhr, error, thrown) {
                    Swal.fire({
                        icon: 'error',
                        title: 'এরর!',
                        text: 'ডাটা লোড হতে সমস্যা হচ্ছে। রাউট চেক করুন।'
                    });
                }
            },
            columns: [
                { data: 'photo', name: 'photo', className: 'text-center' },
                { data: 'name', name: 'name', className: 'font-weight-bold text-dark' },
                { data: 'price', name: 'price', className: 'text-center' },
                { data: 'action', searchable: false, orderable: false, className: 'text-center' }
            ],
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
                search: "_INPUT_",
                searchPlaceholder: "এখানে খুঁজুন...",
                lengthMenu: "দেখান _MENU_ টি",
                info: "মোট _TOTAL_ টি থিমের মধ্যে _START_ থেকে _END_ পর্যন্ত দেখানো হচ্ছে",
                paginate: {
                    first: "প্রথম",
                    last: "শেষ",
                    next: '<i class="fas fa-chevron-right"></i>',
                    previous: '<i class="fas fa-chevron-left"></i>'
                }
            }
        });

        // ডিলিট মোডাল ওপেন করার লজিক
        $('#confirm-delete').on('show.bs.modal', function(e) {
            var href = $(e.relatedTarget).data('href');
            $(this).find('#delete-form').attr('action', href);
        });
    });
</script>
@endsection