@extends('admin.layouts.master')

@section('title', 'Orders Management')

@section('content')
<div class="content-header">
    <div>
        <h1><i class="fa fa-shopping-cart" style="color:var(--admin-primary); margin-right:8px;"></i> Orders Management</h1>
        <p style="font-size:12px; color:var(--admin-text-muted); margin-top:2px;">Filter, process, track deliveries, and print invoices</p>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a> / <span>Orders</span>
    </div>
</div>

<!-- Status Filter Tabs -->
<div class="filter-tabs">
    <a href="{{ route('admin.orders.index', ['status' => 'all', 'search' => $search]) }}" class="tab-btn {{ $status === 'all' ? 'active' : '' }}">
        All Orders <span class="badge">{{ $counts['all'] }}</span>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'pending', 'search' => $search]) }}" class="tab-btn {{ $status === 'pending' ? 'active' : '' }}">
        Pending <span class="badge">{{ $counts['pending'] }}</span>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'processing', 'search' => $search]) }}" class="tab-btn {{ $status === 'processing' ? 'active' : '' }}">
        Processing <span class="badge">{{ $counts['processing'] }}</span>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'shipped', 'search' => $search]) }}" class="tab-btn {{ $status === 'shipped' ? 'active' : '' }}">
        Shipped / On Delivery <span class="badge">{{ $counts['shipped'] }}</span>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'delivered', 'search' => $search]) }}" class="tab-btn {{ $status === 'delivered' ? 'active' : '' }}">
        Delivered <span class="badge">{{ $counts['delivered'] }}</span>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'cancelled', 'search' => $search]) }}" class="tab-btn {{ $status === 'cancelled' ? 'active' : '' }}">
        Cancelled <span class="badge">{{ $counts['cancelled'] }}</span>
    </a>
</div>

<!-- Search & Table Box -->
<div class="box">
    <div class="box-header">
        <form action="{{ route('admin.orders.index') }}" method="GET" style="display:flex; gap:10px; max-width:450px; width:100%;">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="text" name="search" value="{{ $search }}" class="form-control-custom" placeholder="Search by Invoice #, Name, Phone...">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="fa fa-search"></i> Search</button>
            @if($search)
                <a href="{{ route('admin.orders.index', ['status' => $status]) }}" class="btn-admin btn-admin-default" title="Clear Search"><i class="fa fa-times"></i></a>
            @endif
        </form>

        <a href="{{ route('admin.orders.create') }}" class="btn-admin btn-admin-success">
            <i class="fa fa-plus-circle"></i> Create Manual Order
        </a>
    </div>

    <div class="box-body" style="padding:0;">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Items</th>
                        <th>Delivery</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" style="font-weight:700; color:var(--admin-primary); text-decoration:none;">
                                    #{{ $order->invoice_no }}
                                </a>
                            </td>
                            <td style="font-size:12px; color:#6b7280; white-space:nowrap;">
                                {{ $order->created_at->format('d M, Y') }}<br>
                                <small>{{ $order->created_at->format('h:i A') }}</small>
                            </td>
                            <td><strong>{{ $order->customer_name }}</strong></td>
                            <td><a href="tel:{{ $order->customer_phone }}" style="color:#2563eb; text-decoration:none; font-weight:600;">{{ $order->customer_phone }}</a></td>
                            <td style="max-width:180px; font-size:12px; color:#4b5563; line-height:1.3;">
                                {{ Str::limit($order->customer_address, 45) }}
                                <span style="display:block; font-size:11px; font-weight:700; color:#059669;">
                                    {{ $order->city_type === 'inside_dhaka' ? 'Inside Dhaka' : 'Outside Dhaka' }}
                                </span>
                            </td>
                            <td>
                                <span style="font-weight:700; background:#f3f4f6; padding:2px 8px; border-radius:10px; font-size:12px;">
                                    {{ $order->items->count() }} items
                                </span>
                            </td>
                            <td style="font-size:12px; color:#6b7280;">৳ {{ number_format($order->delivery_charge) }}</td>
                            <td style="font-weight:700; color:#111827; font-size:14px;">৳ {{ number_format($order->total_amount) }}</td>
                            <td>
                                <select class="status-select status-{{ $order->status }} order-status-changer" data-order-id="{{ $order->id }}" data-current-status="{{ $order->status }}">
                                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="returned" {{ $order->status === 'returned' ? 'selected' : '' }}>Returned</option>
                                </select>
                            </td>
                            <td style="text-align:center; white-space:nowrap;">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-admin btn-admin-default btn-sm" title="View & Edit Order"><i class="fa fa-eye"></i></a>
                                <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="btn-admin btn-admin-primary btn-sm" title="Print Invoice"><i class="fa fa-print"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center; padding:40px; color:#9ca3af;">
                                <i class="fa fa-folder-open" style="font-size:32px; display:block; margin-bottom:10px;"></i>
                                No orders found matching this filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($orders->hasPages())
        <div class="box-footer">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
