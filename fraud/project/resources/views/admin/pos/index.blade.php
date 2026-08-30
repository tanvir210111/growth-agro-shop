@extends('layouts.admin')

{{-- ১. স্টাইল সেকশন (হেডারে লোড হবে) --}}
@section('styles')
    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        /* Select2 ডিজাইন কাস্টমাইজেশন */
        .select2-container .select2-selection--single {
            height: 45px !important;
            border: 1px solid #d1d3e2;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 43px !important;
            top: 1px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 15px;
            color: #495057;
            font-size: 15px;
            line-height: 43px;
        }
        /* সার্চ বক্সের ডিজাইন */
        .select2-search__field {
            outline: none !important;
            border: 1px solid #ddd !important;
            padding: 5px !important;
        }

        /* ৩-ডট মেনু ডিজাইন */
        .action-dropdown { position: relative; }
        .custom-menu {
            display: none;
            position: absolute;
            right: 35px; /* বাটনের বামে */
            top: 0;
            background: #fff;
            min-width: 180px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
            z-index: 99999;
            border-radius: 8px;
            border: 1px solid #eee;
            padding: 5px 0;
            text-align: left;
        }
        .custom-menu a {
            display: block; padding: 10px 15px; color: #333; text-decoration: none; font-size: 13px; transition: 0.2s;
        }
        .custom-menu a:hover { background-color: #f0f4f8; color: #2d3274; }
        
        /* টেবিল ওভারফ্লো ফিক্স */
        .table-responsive { overflow: visible !important; }
        
        /* অন্যান্য */
        .bg-gradient-primary { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); }
        .bg-aliceblue { background-color: #f0f8ff; }
        input[type=number] { -moz-appearance: textfield; }
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    </style>
@endsection

{{-- ২. কন্টেন্ট সেকশন (বডিতে লোড হবে) --}}
@section('content')

@php
    $users = \App\Models\User::where('status', 1)->orderBy('name', 'asc')->get();
@endphp

<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading"><i class="fas fa-cash-register mr-2"></i>{{ __('Point of Sale (POS)') }}</h4>
            </div>
        </div>
    </div>

    {{-- নোটিফিকেশন --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif

    {{-- মেইন ফর্ম --}}
    <form action="{{ route('admin.pos.store') }}" method="POST" id="pos-form">
        @csrf
        <div class="row">
            {{-- বাম পাশ --}}
            <div class="col-lg-7">
                {{-- প্রোডাক্ট --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="m-0 font-weight-bold"><i class="fas fa-cart-plus mr-2"></i> Order Items</h5>
                        <button type="button" class="btn btn-light btn-sm font-weight-bold text-primary" id="add-item-btn">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <thead class="bg-light text-muted small uppercase">
                                <tr>
                                    <th width="40%" class="pl-4">Product</th>
                                    <th width="20%" class="text-center">Qty</th>
                                    <th width="20%" class="text-center">Price</th>
                                    <th width="15%" class="text-right">Total</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody id="product-list">
                                <tr class="product-row border-bottom">
                                    <td class="p-3 pl-4"><input type="text" class="form-control border-0 bg-light item-name" placeholder="Item name..." required></td>
                                    <td class="p-3"><input type="number" class="form-control border-0 bg-light text-center qty" value="1" min="1" required></td>
                                    <td class="p-3"><input type="number" class="form-control border-0 bg-light text-center price" placeholder="0.00" step="0.01" required></td>
                                    <td class="p-3 text-right font-weight-bold">৳<span class="row-total">0.00</span></td>
                                    <td class="p-3 text-center"><button type="button" class="btn btn-link text-danger p-0 remove-row" style="display:none;"><i class="fas fa-times-circle"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- কাস্টমার ইনফো --}}
                <div class="card shadow-sm border-0 p-4 mb-4">
                    <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">Customer Information</h6>
                    
                    {{-- [IMPORTANT] সার্চ বক্স --}}
                    <div class="form-group mb-4 p-3 bg-aliceblue rounded border">
                        <label class="font-weight-bold text-primary mb-2">🔍 Search Existing Customer</label>
                        {{-- Select2 ক্লাস এবং আইডি দেওয়া আছে --}}
                        <select class="form-control select2-box" id="customer_search" style="width: 100%;">
                            <option value="">Start typing Name or Phone...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" data-name="{{ $user->name }}" data-phone="{{ $user->phone }}">
                                    {{ $user->name }} - {{ $user->phone }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light border-0" id="final_name" name="name" placeholder="Full Name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small font-weight-bold">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light border-0" id="final_phone" name="phone" placeholder="017xxxxxxxx" required>
                                <small class="text-muted">New number? Auto-account created.</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-0">
                        <label class="small font-weight-bold">Address / Note</label>
                        <textarea class="form-control bg-light border-0" name="support_note" rows="2" placeholder="Address..."></textarea>
                    </div>
                    <textarea name="description" id="final_description" style="display:none;"></textarea>
                </div>
            </div>

            {{-- ডান পাশ --}}
            <div class="col-lg-5">
                <div class="card bg-gradient-primary text-white p-4 mb-4 shadow-lg border-0">
                    <div class="text-center mb-4">
                        <small class="text-uppercase opacity-75">Grand Total</small>
                        <h1 class="font-weight-bold">৳<span id="grand_total_display">0.00</span></h1>
                        <input type="hidden" name="total" id="total_bill_input" value="0">
                    </div>
                    <div class="row border-top border-white-50 pt-3">
                        <div class="col-6 border-right border-white-50">
                            <small class="text-uppercase opacity-75">Paid Now</small>
                            <input type="number" class="form-control bg-transparent border-0 text-white font-weight-bold p-0 shadow-none" name="paid" id="paid_amount" value="0" step="0.01" style="font-size: 1.5rem;">
                        </div>
                        <div class="col-6 text-right">
                            <small class="text-uppercase opacity-75">Due</small>
                            <div class="h4 font-weight-bold text-warning m-0" id="due_display">৳ 0.00</div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <label class="font-weight-bold small text-muted">Payment Method</label>
                        <select name="payment_method" class="form-control bg-light border-0 h-auto py-2">
                            <option value="Cash">Cash (নগদ)</option>
                            <option value="Bkash">bKash</option>
                            <option value="Nagad">Nagad</option>
                            <option value="Bank">Bank Transfer</option>
                        </select>
                    </div>
                </div>

                <button class="btn btn-dark w-100 py-3 font-weight-bold shadow-lg" type="submit">
                    <i class="fas fa-check-circle mr-2"></i> COMPLETE SALE
                </button>
            </div>
        </div>
    </form>

    {{-- ৩. রিসেন্ট সেলস (অ্যাকশন বাটন ফিক্সড) --}}
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold m-0 text-dark">Recent Sales</h5>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('admin.pos.index') }}" class="btn btn-outline-secondary {{ !request('filter') ? 'active' : '' }}">All</a>
                        <a href="{{ route('admin.pos.index', ['filter' => 'paid']) }}" class="btn btn-outline-success {{ request('filter') == 'paid' ? 'active' : '' }}">Paid</a>
                        <a href="{{ route('admin.pos.index', ['filter' => 'due']) }}" class="btn btn-outline-danger {{ request('filter') == 'due' ? 'active' : '' }}">Due</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="pl-4">Invoice</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Status</th>
                                <th class="text-right pr-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td class="pl-4">
                                    <span class="font-weight-bold text-primary">{{ $order->order_number }}</span>
                                    <br><small class="text-muted">{{ $order->created_at->format('d M, h:i A') }}</small>
                                </td>
                                <td>
                                    <span class="font-weight-bold">{{ $order->customer->name }}</span>
                                    <br><small class="text-muted">{{ $order->customer->phone }}</small>
                                </td>
                                <td class="font-weight-bold">৳{{ number_format($order->total_amount, 2) }}</td>
                                <td class="text-success">৳{{ number_format($order->paid_amount, 2) }}</td>
                                <td class="text-danger">৳{{ number_format($order->due_amount, 2) }}</td>
                                <td>
                                    @if($order->due_amount > 0) <span class="badge badge-danger">Due</span>
                                    @else <span class="badge badge-success">Paid</span> @endif
                                </td>
                                <td class="text-right pr-4" style="position: relative;">
                                    <div class="action-dropdown">
                                        <button class="btn btn-sm btn-light border rounded-circle action-btn" onclick="toggleMenu('menu-{{ $order->id }}')">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div id="menu-{{ $order->id }}" class="custom-menu">
                                            <a href="{{ route('frontend.invoice.live', ['order_number' => $order->order_number, 'token' => $order->hash_token]) }}" target="_blank">
                                                <i class="fas fa-eye mr-2"></i> View Invoice
                                            </a>
                                            @if($order->due_amount > 0)
                                            <div style="border-top:1px solid #eee;"></div>
                                            <a href="javascript:;" onclick="openDueModal('{{ $order->id }}')" class="text-success font-weight-bold">
                                                <i class="fas fa-money-bill-wave mr-2"></i> Collect Due
                                            </a>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- মডাল --}}
                                    @if($order->due_amount > 0)
                                    <div class="modal fade" id="modal-{{ $order->id }}" tabindex="-1" style="z-index: 100000;">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0">
                                                <div class="modal-header bg-light">
                                                    <h5 class="modal-title font-weight-bold">Collect Due</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form action="{{ route('admin.pos.due_collect', $order->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body p-4">
                                                        <div class="text-center mb-3">
                                                            <h2 class="text-danger font-weight-bold">৳{{ number_format($order->due_amount, 2) }}</h2>
                                                            <small class="text-muted">CURRENT DUE</small>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Amount</label>
                                                            <input type="number" name="amount" class="form-control font-weight-bold" max="{{ $order->due_amount }}" required step="0.01">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Method</label>
                                                            <select name="payment_method" class="form-control">
                                                                <option value="Cash">Cash</option>
                                                                <option value="Bkash">bKash</option>
                                                                <option value="Nagad">Nagad</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light">
                                                        <button type="submit" class="btn btn-success shadow">Confirm Payment</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center py-4">No data found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white d-flex justify-content-end">{{ $orders->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- ৩. স্ক্রিপ্ট সেকশন (ফুটার এর আগে লোড হবে) --}}
@section('scripts')
{{-- Select2 JS --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        
        // ১. Select2 চালু করা (সার্চ অপশন সহ)
        $('.select2-box').select2({
            placeholder: "Type Name or Phone to search...",
            allowClear: true,
            width: '100%'
        });

        // ২. সার্চ থেকে ডাটা ফিলাপ করা
        $('#customer_search').on('change', function() {
            var opt = $(this).find(':selected');
            if(opt.val()) {
                $('#final_name').val(opt.data('name'));
                $('#final_phone').val(opt.data('phone'));
            } else {
                $('#final_name').val('');
                $('#final_phone').val('');
            }
        });

        // ৩. ক্যালকুলেশন লজিক
        $('#add-item-btn').click(function() {
            $('#product-list').append(`
                <tr class="product-row border-bottom">
                    <td class="p-3 pl-4"><input type="text" class="form-control border-0 bg-light item-name" placeholder="Item name..." required></td>
                    <td class="p-3"><input type="number" class="form-control border-0 bg-light text-center qty" value="1" min="1" required></td>
                    <td class="p-3"><input type="number" class="form-control border-0 bg-light text-center price" placeholder="0.00" step="0.01" required></td>
                    <td class="p-3 text-right font-weight-bold">৳<span class="row-total">0.00</span></td>
                    <td class="p-3 text-center"><button type="button" class="btn btn-link text-danger p-0 remove-row"><i class="fas fa-times-circle"></i></button></td>
                </tr>
            `);
            checkBtn();
        });

        $(document).on('click', '.remove-row', function() { $(this).closest('tr').remove(); calc(); checkBtn(); });
        $(document).on('input', '.qty, .price, #paid_amount', function() { calc(); });

        function calc() {
            let total = 0;
            let desc = "";
            $('.product-row').each(function(i) {
                let name = $(this).find('.item-name').val();
                let q = parseFloat($(this).find('.qty').val()) || 0;
                let p = parseFloat($(this).find('.price').val()) || 0;
                let sub = q * p;
                $(this).find('.row-total').text(sub.toFixed(2));
                total += sub;
                if(name) desc += `${i+1}. ${name} (${q} x ${p}) = ${sub.toFixed(2)}\n`;
            });

            $('#grand_total_display').text(total.toFixed(2));
            $('#total_bill_input').val(total.toFixed(2));
            $('#final_description').val(desc);

            let paid = parseFloat($('#paid_amount').val()) || 0;
            let due = total - paid;
            if(due < 0) due = 0;
            $('#due_display').text('৳ ' + due.toFixed(2));
            
            if(due > 0) $('#due_display').removeClass('text-success').addClass('text-warning');
            else $('#due_display').removeClass('text-warning').addClass('text-success');
        }

        function checkBtn() {
            if($('.product-row').length > 1) $('.remove-row').show();
            else $('.remove-row').hide();
        }
        
        $('#pos-form').on('submit', function() { calc(); });
    });

    // ৩-ডট মেনু টগল (Custom JS)
    function toggleMenu(id) {
        $('.custom-menu').not('#' + id).hide();
        var menu = document.getElementById(id);
        menu.style.display = (menu.style.display === "block") ? "none" : "block";
    }

    // বাইরে ক্লিক করলে মেনু বন্ধ
    window.onclick = function(e) {
        if (!e.target.matches('.action-btn') && !e.target.matches('.fa-ellipsis-v')) {
            $('.custom-menu').hide();
        }
    }

    // মডাল ওপেন
    function openDueModal(id) {
        $('#modal-' + id).modal('show');
    }
</script>
@endsection