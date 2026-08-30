@extends('layouts.admin')

@section('content')
<style>
    :root {
        --primary-dark: #2d3274;
        --expense-red: #d9534f;
        --soft-bg: #f8f9fc;
        --border-color: #e3e6f0;
    }

    .content-area { background-color: var(--soft-bg); padding: 30px; }
    
    /* কার্ড ডিজাইন */
    .custom-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 120, 0.1);
    }

    /* স্ট্যাটাস বক্স */
    .stat-box {
        padding: 20px;
        background: white;
        border-radius: 12px;
        border-left: 5px solid var(--expense-red);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    /* ইনপুট ফিল্ড ডিজাইন */
    .form-control {
        border-radius: 8px;
        padding: 12px;
        border: 1px solid #d1d3e2;
    }
    .form-control:focus {
        border-color: var(--expense-red);
        box-shadow: 0 0 0 0.2rem rgba(217, 83, 79, 0.1);
    }

    /* মডার্ন টেবিল */
    .modern-table thead th {
        background-color: #f8f9fc;
        color: var(--primary-dark);
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 1px;
        padding: 15px;
        border-top: none;
    }

    .modern-table tbody td {
        padding: 15px;
        vertical-align: middle;
        font-size: 14px;
        color: #4e73df;
        border-bottom: 1px solid var(--border-color);
    }

    .expense-icon {
        width: 35px;
        height: 35px;
        background-color: rgba(217, 83, 79, 0.1);
        color: var(--expense-red);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
    }

    .btn-submit {
        background-color: var(--expense-red);
        border: none;
        border-radius: 8px;
        padding: 12px;
        font-weight: bold;
        transition: 0.3s;
    }
    .btn-submit:hover {
        background-color: #c9302c;
        transform: translateY(-2px);
    }
</style>

<div class="content-area">
    <div class="mr-breadcrumb d-flex justify-content-between align-items-center mb-4">
        <h4 class="heading font-weight-bold" style="color: var(--primary-dark);">
            <i class="fas fa-wallet mr-2"></i>{{ __('Daily Expenses Management') }}
        </h4>
    </div>

    <div class="row mb-2">
        <div class="col-lg-4">
            <div class="stat-box custom-card shadow-sm">
                <div>
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Today's Total Expense</div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800">৳{{ number_format($expenses->where('expense_date', date('Y-m-d'))->sum('amount'), 2) }}</div>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-day fa-2x text-gray-300" style="color: #dddfeb;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="product-area">
        <div class="row">
            <div class="col-lg-4">
                <div class="card custom-card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="sub-title mb-4 font-weight-bold" style="color: var(--expense-red);">
                            <i class="fas fa-plus-circle mr-2"></i>{{ __('Add New Expense') }}
                        </h5>
                        <form action="{{ route('admin.expense.store') }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-dark">Expense Title *</label>
                                <input type="text" class="form-control" name="title" placeholder="e.g. Office Rent / Snacks" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-dark">Amount (৳) *</label>
                                <input type="number" class="form-control" name="amount" placeholder="0.00" step="0.01" required>
                            </div>
                            <div class="form-group mb-4">
                                <label class="small font-weight-bold text-dark">Expense Date *</label>
                                <input type="date" class="form-control" name="expense_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <button class="btn btn-danger btn-submit w-100 shadow-sm" type="submit">
                                <i class="fas fa-check-circle mr-2"></i>{{ __('SUBMIT EXPENSE') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card custom-card shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="sub-title mb-0 font-weight-bold" style="color: var(--primary-dark);">
                                <i class="fas fa-history mr-2"></i>{{ __('Recent Expense History') }}
                            </h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table modern-table mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th>TITLE</th>
                                        <th>AMOUNT</th>
                                        <th>DATE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expenses as $expense)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="expense-icon">
                                                    <i class="fas fa-receipt"></i>
                                                </div>
                                                <span class="font-weight-bold text-dark">{{ $expense->title }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-danger">৳{{ number_format($expense->amount, 2) }}</span>
                                        </td>
                                        <td>
                                            <div class="text-muted small">
                                                <i class="far fa-calendar-alt mr-1"></i>
                                                {{ date('d M, Y', strtotime($expense->expense_date)) }}
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No expenses recorded yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
						<div class="mt-4 d-flex justify-content-center">
                            {{ $expenses->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection