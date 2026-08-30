@extends('layouts.admin')

@section('content')
<style>
    :root {
        --primary-dark: #1a1c3d;
        --accent-blue: #2d3274;
        --soft-bg: #f8f9fc;
        --border-color: #e3e6f0;
    }

    .content-area { background-color: var(--soft-bg); padding: 30px; }
    
    .custom-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 120, 0.1);
        overflow: hidden;
    }

    /* কাস্টমার অ্যাভাতার স্টাইল */
    .customer-avatar {
        width: 40px;
        height: 40px;
        background-color: var(--primary-dark);
        color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 15px;
    }

    /* মডার্ন টেবিল স্টাইল */
    .modern-table thead th {
        background-color: #f8f9fc;
        border-top: none;
        color: var(--primary-dark);
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 1px;
        padding: 15px;
    }

    .modern-table tbody td {
        padding: 18px 15px;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
        color: #444;
    }

    .modern-table tbody tr:hover {
        background-color: rgba(45, 50, 116, 0.02);
        transition: 0.3s;
    }

    /* অ্যাকশন বাটন স্টাইল */
    .action-btn {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: 0.3s;
        border: none;
        text-decoration: none;
    }

    .btn-edit { background-color: rgba(78, 115, 223, 0.1); color: #4e73df; }
    .btn-edit:hover { background-color: #4e73df; color: white; }
    
    .btn-delete { background-color: rgba(231, 74, 59, 0.1); color: #e74e3b; }
    .btn-delete:hover { background-color: #e74e3b; color: white; }
</style>

<div class="content-area">
    <div class="mr-breadcrumb d-flex justify-content-between align-items-center mb-4">
        <h4 class="heading font-weight-bold" style="color: var(--primary-dark);">
            <i class="fas fa-users mr-2"></i>{{ __('Customers Management') }}
        </h4>
        <div class="search-box">
            <input type="text" id="customerSearch" class="form-control form-control-sm shadow-sm" style="border-radius: 20px; padding: 10px 20px;" placeholder="Search customers...">
        </div>
    </div>

    <div class="card custom-card bg-white">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="customerTable" class="table modern-table mb-0 w-100">
                    <thead>
                        <tr>
                            <th>{{ __('Customer Details') }}</th>
                            <th>{{ __('Contact Info') }}</th>
                            <th>{{ __('Location') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="customer-avatar">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-weight-bold text-dark">{{ $customer->name }}</div>
                                        <small class="text-muted">Customer ID: #{{ $customer->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-phone-alt mr-2 text-muted small"></i>
                                    {{ $customer->phone }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center text-truncate" style="max-width: 250px;">
                                    <i class="fas fa-map-marker-alt mr-2 text-muted small"></i>
                                    {{ $customer->address ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="text-right">
                                <div class="d-flex justify-content-end" style="gap: 8px;">
                                    {{-- এডিট বাটন --}}
                                    <a href="{{ route('admin.customer.edit', $customer->id) }}" 
                                       class="action-btn btn-edit" 
                                       title="Edit Customer">
                                        <i class="fas fa-pen small"></i>
                                    </a>
                                    
                                    {{-- ডিলিট ফর্ম --}}
                                    <form action="{{ route('admin.customer.destroy', $customer->id) }}" method="POST" class="d-inline-block">
                                        @csrf @method('DELETE')
                                        <button type="submit" 
                                                class="action-btn btn-delete" 
                                                onclick="return confirm('Are you sure you want to delete this customer?')"
                                                title="Delete Customer">
                                            <i class="fas fa-trash-alt small"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
			{{-- প্যাজিনেশন কোডটি এখানে বসান --}}
            <div class="mt-3 mb-3 d-flex justify-content-center">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    // ক্লায়েন্ট সাইড সার্চ ফিল্টার
    document.getElementById('customerSearch').addEventListener('keyup', function() {
        let searchValue = this.value.toLowerCase();
        let rows = document.querySelectorAll('#customerTable tbody tr');

        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(searchValue) ? '' : 'none';
        });
    });
</script>
@endsection