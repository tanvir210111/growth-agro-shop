@extends('admin.layouts.master')

@section('title', 'Order #' . $order->invoice_no)

@section('content')
<div class="content-header">
    <div>
        <h1><i class="fa fa-receipt" style="color:var(--admin-primary); margin-right:8px;"></i> Order #{{ $order->invoice_no }}</h1>
        <p style="font-size:12px; color:var(--admin-text-muted); margin-top:2px;">Placed on {{ $order->created_at->format('d M, Y - h:i A') }} ({{ $order->created_at->diffForHumans() }})</p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="btn-admin btn-admin-primary">
            <i class="fa fa-print"></i> Print Invoice
        </a>
        <a href="{{ route('admin.orders.index') }}" class="btn-admin btn-admin-default">
            ← Back to Orders
        </a>
    </div>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px;">
    <!-- Left Column: Ordered Items -->
    <div class="box">
        <div class="box-header">
            <h3 class="box-title"><i class="fa fa-box-open" style="color:var(--admin-primary); margin-right:6px;"></i> Ordered Products ({{ $order->items->count() }})</h3>
        </div>
        <div class="box-body" style="padding:0;">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Size/Variant</th>
                            <th>Unit Price</th>
                            <th style="text-align:center;">Qty</th>
                            <th style="text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        @if($item->product_image)
                                            <img src="{{ Str::startsWith($item->product_image, 'http') ? $item->product_image : asset($item->product_image) }}" alt="{{ $item->product_name }}" style="width:48px; height:48px; object-fit:cover; border-radius:4px; border:1px solid #e5e7eb;">
                                        @endif
                                        <div>
                                            <strong style="font-size:13px; color:#111827;">{{ $item->product_name }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td><span style="background:#f3f4f6; padding:3px 8px; border-radius:4px; font-weight:600; font-size:12px;">{{ $item->size ?? 'Standard' }}</span></td>
                                <td>৳ {{ number_format($item->price) }}</td>
                                <td style="text-align:center; font-weight:700;">{{ $item->quantity }}</td>
                                <td style="text-align:right; font-weight:700; color:#111827;">৳ {{ number_format($item->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Price Breakdown -->
            <div style="padding:20px; background:#f9fafb; border-top:1px solid #f3f4f6; display:flex; justify-content:flex-end;">
                <div style="width:280px; display:flex; flex-direction:column; gap:8px; font-size:13px;">
                    <div style="display:flex; justify-content:space-between; color:#4b5563;">
                        <span>Products Subtotal:</span>
                        <span style="font-weight:600;">৳ {{ number_format($order->subtotal) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; color:#4b5563;">
                        <span>Delivery Fee ({{ $order->city_type === 'inside_dhaka' ? 'Inside Dhaka' : 'Outside Dhaka' }}):</span>
                        <span style="font-weight:600;">৳ {{ number_format($order->delivery_charge) }}</span>
                    </div>
                    @if($order->discount > 0)
                        <div style="display:flex; justify-content:space-between; color:#ef4444;">
                            <span>Discount / Coupon:</span>
                            <span style="font-weight:600;">- ৳ {{ number_format($order->discount) }}</span>
                        </div>
                    @endif
                    <div style="display:flex; justify-content:space-between; font-weight:800; font-size:16px; color:#111827; border-top:2px solid #e5e7eb; padding-top:8px;">
                        <span>Grand Total (COD):</span>
                        <span style="color:#059669;">৳ {{ number_format($order->total_amount) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Customer Info & Status Manager Form -->
    <div style="display:flex; flex-direction:column; gap:20px;">
        <!-- Status & Update Box -->
        <div class="box" style="border-top-color:#3c8dbc;">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-tasks" style="color:var(--admin-primary); margin-right:6px;"></i> Order Status & Update</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label">Order Fulfillment Status</label>
                        <select name="status" class="form-control-custom" style="font-weight:700; height:40px;">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>⏳ Pending (New Order)</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>📦 Processing / Packaging</option>
                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>🚚 Shipped / Handed to Courier</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>✅ Delivered (Payment Collected)</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                            <option value="returned" {{ $order->status === 'returned' ? 'selected' : '' }}>↩️ Returned by Courier</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Customer Full Name</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name', $order->customer_name) }}" class="form-control-custom" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contact Phone Number</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone', $order->customer_phone) }}" class="form-control-custom" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Delivery Address</label>
                        <textarea name="customer_address" rows="3" class="form-control-custom" required>{{ old('customer_address', $order->customer_address) }}</textarea>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                        <div class="form-group">
                            <label class="form-label">Delivery Region</label>
                            <select name="city_type" class="form-control-custom">
                                <option value="inside_dhaka" {{ $order->city_type === 'inside_dhaka' ? 'selected' : '' }}>Inside Dhaka</option>
                                <option value="outside_dhaka" {{ $order->city_type === 'outside_dhaka' ? 'selected' : '' }}>Outside Dhaka</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Delivery Fee (৳)</label>
                            <input type="number" name="delivery_charge" value="{{ old('delivery_charge', $order->delivery_charge) }}" class="form-control-custom" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Order / Customer Notes</label>
                        <textarea name="note" rows="2" class="form-control-custom" placeholder="Special delivery instructions...">{{ old('note', $order->note) }}</textarea>
                    </div>

                    <button type="submit" class="btn-admin btn-admin-primary" style="width:100%; justify-content:center; padding:10px;">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
