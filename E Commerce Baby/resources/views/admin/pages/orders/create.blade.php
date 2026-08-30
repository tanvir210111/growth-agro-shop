@extends('admin.layouts.master')

@section('title', 'Create Manual Order')

@section('content')
<div class="content-header">
    <div>
        <h1><i class="fa fa-cart-plus" style="color:var(--admin-success); margin-right:8px;"></i> Create Manual Order</h1>
        <p style="font-size:12px; color:var(--admin-text-muted); margin-top:2px;">Take phone, WhatsApp, or Facebook Messenger orders directly</p>
    </div>
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a> / <a href="{{ route('admin.orders.index') }}">Orders</a> / <span>Create</span>
    </div>
</div>

<form action="{{ route('admin.orders.store') }}" method="POST">
    @csrf

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px;">
        <!-- Left: Customer Information & Items -->
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-user" style="color:var(--admin-primary); margin-right:6px;"></i> Customer & Delivery Details</h3>
            </div>
            <div class="box-body">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label class="form-label">Customer Name *</label>
                        <input type="text" name="customer_name" class="form-control-custom" placeholder="e.g. Nusrat Jahan" value="{{ old('customer_name') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number *</label>
                        <input type="text" name="customer_phone" class="form-control-custom" placeholder="e.g. 01712345678" value="{{ old('customer_phone') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Full Delivery Address *</label>
                    <textarea name="customer_address" rows="3" class="form-control-custom" placeholder="House, Road, Area, City..." required>{{ old('customer_address') }}</textarea>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label class="form-label">Delivery Location *</label>
                        <select name="city_type" class="form-control-custom" required>
                            <option value="inside_dhaka" {{ old('city_type') === 'inside_dhaka' ? 'selected' : '' }}>Inside Dhaka (৳ 70)</option>
                            <option value="outside_dhaka" {{ old('city_type') === 'outside_dhaka' ? 'selected' : '' }}>Outside Dhaka (৳ 130)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Order Note</label>
                        <input type="text" name="note" class="form-control-custom" placeholder="Optional delivery instructions..." value="{{ old('note') }}">
                    </div>
                </div>

                <hr style="border:0; border-top:1px solid #f3f4f6; margin:20px 0;">

                <h3 class="box-title" style="margin-bottom:15px;"><i class="fa fa-tshirt" style="color:var(--admin-primary); margin-right:6px;"></i> Select Products</h3>

                <div id="orderItemsContainer">
                    <div class="order-item-row" style="display:grid; grid-template-columns: 3fr 1fr 1fr auto; gap:10px; align-items:flex-end; margin-bottom:12px; background:#f9fafb; padding:12px; border-radius:6px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Product</label>
                            <select name="items[0][product_id]" class="form-control-custom" required>
                                <option value="">Select Product...</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" data-price="{{ $p->sale_price }}">{{ $p->title }} (৳ {{ number_format($p->sale_price) }}) - Stock: {{ $p->stock }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Size</label>
                            <input type="text" name="items[0][size]" class="form-control-custom" placeholder="e.g. 6-12M" value="Standard">
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="items[0][quantity]" class="form-control-custom" value="1" min="1" required>
                        </div>

                        <div>
                            <button type="button" class="btn-admin btn-admin-danger" onclick="this.closest('.order-item-row').remove()" style="padding:9px 12px;"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn-admin btn-admin-default" id="addItemBtn" style="margin-top:5px;">
                    <i class="fa fa-plus"></i> Add Another Product
                </button>
            </div>
        </div>

        <!-- Right: Submit Action -->
        <div class="box" style="border-top-color:#00a65a;">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-check" style="color:#00a65a; margin-right:6px;"></i> Confirm Order</h3>
            </div>
            <div class="box-body">
                <p style="font-size:13px; color:#4b5563; margin-bottom:15px;">
                    This will create a new confirmed Cash on Delivery (COD) order with an automatic invoice number.
                </p>

                <button type="submit" class="btn-admin btn-admin-success" style="width:100%; justify-content:center; padding:12px; font-size:14px;">
                    <i class="fa fa-save"></i> Save & Place Order
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    let itemIndex = 1;
    document.getElementById('addItemBtn').addEventListener('click', function() {
        const container = document.getElementById('orderItemsContainer');
        const firstRow = container.querySelector('.order-item-row');
        const newRow = firstRow.cloneNode(true);

        newRow.querySelectorAll('select, input').forEach(input => {
            input.name = input.name.replace(/\[\d+\]/, `[${itemIndex}]`);
            if (input.tagName === 'INPUT' && input.name.includes('quantity')) input.value = 1;
            if (input.tagName === 'INPUT' && input.name.includes('size')) input.value = 'Standard';
        });

        container.appendChild(newRow);
        itemIndex++;
    });
</script>
@endpush
@endsection
