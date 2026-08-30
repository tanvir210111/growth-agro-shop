@extends('layouts.admin')

@section('content')
<style>
    :root {
        --primary-dark: #2d3274;
        --soft-bg: #f8f9fc;
        --border-color: #e3e6f0;
    }

    .content-area { background-color: var(--soft-bg); padding: 30px; }

    /* প্রিমিয়াম কার্ড ডিজাইন */
    .balance-card {
        border: none;
        border-radius: 15px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .balance-card:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
    }

    .balance-card h6, .balance-card h3, .balance-card i { color: #ffffff !important; }

    .bg-gradient-success { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); }
    .bg-gradient-danger { background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%); }
    .bg-gradient-primary { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); }

    .btn-action {
        border-radius: 10px; padding: 12px 25px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s; border: none;
    }
    .btn-action:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }

    /* আধুনিক টেবিল স্টাইল */
    .modern-table thead th {
        background-color: #f8f9fc; border-top: none; color: var(--primary-dark);
        text-transform: uppercase; font-size: 11px; letter-spacing: 1px; padding: 15px;
    }
    .modern-table tbody td { padding: 18px 15px; vertical-align: middle; font-size: 14px; border-bottom: 1px solid var(--border-color); }

    .badge-soft { padding: 6px 12px; border-radius: 50px; font-weight: 700; font-size: 10px; text-transform: uppercase; }
    .badge-income { background-color: rgba(40, 167, 69, 0.1); color: #28a745; }
    .badge-expense { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
    .badge-withdraw { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }

    .modal-content { border: none; border-radius: 20px; overflow: hidden; }
    .modal-header { border-bottom: none; color: white !important; }
    .modal-footer { border-top: none; padding: 20px; }
    .form-control { border-radius: 10px; padding: 15px; border: 1px solid #ddd; font-size: 16px; }
</style>

<div class="content-area">
    <div class="mr-breadcrumb mb-4">
        <h4 class="heading font-weight-bold" style="color: var(--primary-dark);">
            <i class="fas fa-chart-line mr-2"></i>{{ __('Financial Statement & Fund Management') }}
        </h4>
    </div>

    <div class="product-area">
        
        {{-- তারিখ ফিল্টার সেকশন --}}
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm p-4" style="border-radius: 15px;">
                    <form action="{{ route('admin.report.fund') }}" method="GET" class="row align-items-end">
                        <div class="col-md-4">
                            <label class="font-weight-bold mb-2 text-dark"><i class="fas fa-calendar-alt mr-1"></i> তারিখ সিলেক্ট করুন</label>
                            <input type="date" name="date" class="form-control" value="{{ request('date') ?? date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-action w-100" style="background-color: var(--primary-dark);">
                                <i class="fas fa-filter"></i> ফিল্টার
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.report.fund') }}" class="btn btn-light btn-action w-100" style="border: 1px solid #ddd;">
                                <i class="fas fa-undo"></i> রিসেট
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ব্যালেন্স কার্ড সমূহ - ফিল্টার করা তারিখের ডেটা দেখাবে --}}
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="balance-card p-4 bg-gradient-success shadow">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small font-weight-bold mb-2">Income ({{ request('date') ? date('d M', strtotime(request('date'))) : 'Today' }})</h6>
                            <h3 class="font-weight-bold mb-0">৳ {{ number_format($total_income, 2) }}</h3>
                        </div>
                        <i class="fas fa-arrow-down fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="balance-card p-4 bg-gradient-danger shadow">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small font-weight-bold mb-2">Expense ({{ request('date') ? date('d M', strtotime(request('date'))) : 'Today' }})</h6>
                            <h3 class="font-weight-bold mb-0">৳ {{ number_format($total_expense, 2) }}</h3>
                        </div>
                        <i class="fas fa-arrow-up fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="balance-card p-4 bg-gradient-primary shadow">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase small font-weight-bold mb-2">Total Fund Balance</h6>
                            <h3 class="font-weight-bold mb-0">৳ {{ number_format($current_fund, 2) }}</h3>
                        </div>
                        <i class="fas fa-wallet fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- অ্যাকশন বাটন সমূহ --}}
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="bg-white p-3 rounded shadow-sm d-flex flex-wrap" style="gap: 15px;">
                    <button class="btn btn-success btn-action" data-toggle="modal" data-target="#addMoneyModal">
                        <i class="fas fa-plus-circle mr-1"></i> টাকা জমা করুন
                    </button>
                    <button class="btn btn-danger btn-action" data-toggle="modal" data-target="#withdrawModal">
                        <i class="fas fa-minus-circle mr-1"></i> টাকা উত্তোলন করুন
                    </button>
                </div>
            </div>
        </div>

        {{-- ট্রানজ্যাকশন লেজার টেবিল --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <h5 class="font-weight-bold mb-4" style="color: var(--primary-dark);">
                            <i class="fas fa-list-ul mr-2"></i>{{ __('Ledger (Datewise Transactions)') }}
                        </h5>
                        @include('includes.admin.form-both')
                        <div class="table-responsive">
                            <table class="table modern-table w-100">
                                <thead>
                                    <tr>
                                        <th>TYPE</th>
                                        <th>AMOUNT</th>
                                        <th>REFERENCE</th>
                                        <th>NOTE</th>
                                        <th>DATE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $trans)
                                    <tr>
                                        <td>
                                            <span class="badge-soft {{ $trans->type == 'income' ? 'badge-income' : ($trans->type == 'expense' ? 'badge-expense' : 'badge-withdraw') }}">
                                                {{ strtoupper($trans->type) }}
                                            </span>
                                        </td>
                                        <td class="font-weight-bold text-dark">৳ {{ number_format($trans->amount, 2) }}</td>
                                        <td><span class="text-muted small">#{{ $trans->reference_id }}</span></td>
                                        <td>{{ $trans->note }}</td>
                                        <td class="text-muted small"><i class="far fa-clock mr-1"></i> {{ $trans->created_at->format('d M, Y h:i A') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
						{{-- প্যাজিনেশন বাটন এখানে যোগ করুন --}}
                        <div class="mt-4 d-flex justify-content-center">
                            {{ $transactions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- টাকা জমা করার বড় এবং সেন্টারে থাকা মোডাল --}}
<div class="modal fade" id="addMoneyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form action="{{ route('admin.report.fund_add') }}" method="POST">
            @csrf
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-success p-4">
                    <h4 class="modal-title font-weight-bold"><i class="fas fa-hand-holding-usd mr-2"></i>ফান্ডে টাকা জমা দিন</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-5">
                    <div class="form-group mb-4">
                        <label class="h6 font-weight-bold mb-3">টাকার পরিমাণ (৳) *</label>
                        <input type="number" name="amount" class="form-control" placeholder="0.00" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label class="h6 font-weight-bold mb-3">মন্তব্য/নোট</label>
                        <textarea name="note" class="form-control" rows="4" placeholder="টাকা জমার কারণ বা বিস্তারিত লিখুন..."></textarea>
                    </div>
                </div>
                <div class="modal-footer p-4">
                    <button type="button" class="btn btn-light btn-lg rounded-pill px-5 mr-3" data-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-success btn-lg rounded-pill px-5">কনফার্ম জমা</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- টাকা উত্তোলনের বড় এবং সেন্টারে থাকা মোডাল --}}
<div class="modal fade" id="withdrawModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form action="{{ route('admin.report.fund_withdraw') }}" method="POST">
            @csrf
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-danger p-4">
                    <h4 class="modal-title font-weight-bold"><i class="fas fa-money-bill-wave mr-2"></i>ফান্ড থেকে টাকা উত্তোলন</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-5">
                    <div class="form-group mb-4">
                        <label class="h6 font-weight-bold mb-3">টাকার পরিমাণ (৳) *</label>
                        <input type="number" name="amount" class="form-control" placeholder="0.00" step="0.01" required>
                        <small class="text-muted mt-2 d-block">বর্তমান ব্যালেন্স: ৳ {{ number_format($current_fund, 2) }}</small>
                    </div>
                    <div class="form-group">
                        <label class="h6 font-weight-bold mb-3">উত্তোলনের কারণ</label>
                        <textarea name="note" class="form-control" rows="4" placeholder="কেন টাকা উত্তোলন করছেন লিখুন..."></textarea>
                    </div>
                </div>
                <div class="modal-footer p-4">
                    <button type="button" class="btn btn-light btn-lg rounded-pill px-5 mr-3" data-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-danger btn-lg rounded-pill px-5">কনফার্ম উত্তোলন</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection