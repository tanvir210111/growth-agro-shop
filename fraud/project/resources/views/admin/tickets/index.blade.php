@extends('layouts.admin')

@section('styles')
<style>
    .content-wrapper { background: #f4f7fe; min-height: 100vh; padding: 20px; }
    .ticket-main-card { 
        border: none; 
        border-radius: 24px; 
        background: rgba(255, 255, 255, 0.9); 
        backdrop-filter: blur(10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.03);
    }
    .header-gradient {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        padding: 30px;
        border-radius: 20px;
        color: white;
        margin-bottom: -40px;
        position: relative;
        z-index: 1;
        box-shadow: 0 10px 20px rgba(78, 115, 223, 0.2);
    }
    .table-responsive { padding: 50px 20px 20px; }
    #ticket-table { width: 100% !important; border-collapse: separate; border-spacing: 0 12px; }
    #ticket-table tbody tr { background: #fff; transition: 0.3s; }
    #ticket-table tbody tr:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
    #ticket-table tbody td { padding: 20px 15px; vertical-align: middle; border-top: 1px solid #f8f9fa; }
    
    .user-avatar { width: 35px; height: 35px; background: #eef2ff; color: #4e73df; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 700; margin-right: 12px; }
    .glass-badge { padding: 6px 14px; border-radius: 10px; font-weight: 700; font-size: 10px; text-transform: uppercase; display: inline-block; }
    .status-pending { background: #fff5e6; color: #ff9900; }
    .status-replied { background: #e6fcf5; color: #08d19e; }
    
    .btn-action-group { display: flex; gap: 8px; justify-content: flex-end; }
    .btn-glass { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; }
    .btn-glass-view { background: rgba(78, 115, 223, 0.1); color: #4e73df; }
    .btn-glass-delete { background: rgba(231, 74, 59, 0.1); color: #e74a3b; }
</style>
@endsection

@section('content')
<div class="container-fluid py-5 content-wrapper">
    <div class="header-gradient d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1 font-weight-bold text-white">Support Helpdesk</h4>
            <p class="text-white-50 mb-0 small">Monitoring customer inquiries in real-time.</p>
        </div>
        <button class="btn btn-light btn-sm px-4 shadow-sm" style="border-radius: 12px;" onclick="location.reload();">
            <i class="fas fa-sync-alt mr-2 text-primary"></i> Sync Data
        </button>
    </div>

    <div class="card ticket-main-card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table" id="ticket-table">
                    <thead>
                        <tr>
                            <th>Ref. #</th>
                            <th>Customer</th>
                            <th>Ticket Subject</th>
                            <th>Status</th>
                            <th class="text-right">Manage</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- লাইব্রেরিগুলো নিশ্চিত করুন --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // ১. ডাটাটেবিল লোড
        var table = $('#ticket-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.tickets.index') }}",
            columns: [
                { data: 'id', name: 'id', render: function(data) { return `<b>#${data}</b>`; } },
                { 
                    data: 'user_name', 
                    render: function(data) {
                        return `<div class="d-flex align-items-center"><div class="user-avatar">${data ? data.charAt(0) : 'U'}</div><b>${data}</b></div>`;
                    }
                },
                { 
                    data: 'subject', 
                    render: function(data, type, row) {
                        let lastUpdate = row.updated_at ? moment(row.updated_at).fromNow() : 'No update';
                        return `<div>${data}</div><small class="text-muted">${lastUpdate}</small>`;
                    }
                },
                { 
                    data: 'status', 
                    render: function(data) {
                        return `<span class="glass-badge status-${data.toLowerCase()}">${data}</span>`;
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
            ]
        });

        // ২. ডিলিট বাটন লজিক (Event Delegation - এটিই পপআপ আসার গ্যারান্টি)
        $(document).on('click', '.delete-ticket', function(e) {
            e.preventDefault();
            
            // কন্ট্রোলার থেকে আসা data-url রিড করা হচ্ছে
            var url = $(this).attr('data-url'); 
            
            if(!url) {
                alert("Error: Delete URL not found!");
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "টিকেট এবং মেসেজ হিস্ট্রি সব মুছে যাবে!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'হ্যাঁ, ডিলিট করুন!',
                cancelButtonText: 'বাতিল'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function(res) {
                            if(res.status == 'success') {
                                table.ajax.reload(null, false);
                                Swal.fire('Deleted!', res.message, 'success');
                            } else {
                                Swal.fire('Error!', res.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'সার্ভারে কানেক্ট করা যাচ্ছে না।', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection