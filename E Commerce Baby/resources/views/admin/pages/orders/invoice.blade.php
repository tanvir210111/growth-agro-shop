<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - #{{ $order->invoice_no }} - {{ $storeName }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        body { background: #fff; color: #333; font-size: 13px; line-height: 1.4; padding: 20px; }
        .invoice-container { max-width: 800px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #3c8dbc; padding-bottom: 15px; margin-bottom: 20px; }
        .brand-info { display: flex; align-items: center; gap: 15px; }
        .brand-info img { height: 60px; object-fit: contain; }
        .brand-text h2 { font-size: 22px; color: #3c8dbc; font-weight: 800; }
        .brand-text p { font-size: 12px; color: #666; margin-top: 2px; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { font-size: 24px; color: #333; font-weight: 800; letter-spacing: 1px; }
        .invoice-title p { font-size: 12px; color: #666; margin-top: 3px; }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; }
        .box h3 { font-size: 12px; text-transform: uppercase; color: #3c8dbc; margin-bottom: 8px; font-weight: 700; }
        .box p { font-size: 13px; margin-bottom: 4px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background: #3c8dbc; color: #fff; font-size: 12px; text-transform: uppercase; font-weight: 700; }
        td { font-size: 13px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-section { display: flex; justify-content: flex-end; margin-bottom: 30px; }
        .totals-table { width: 300px; }
        .totals-table td { padding: 6px 12px; }
        .totals-table .grand-total { font-size: 16px; font-weight: 800; color: #3c8dbc; border-top: 2px solid #3c8dbc; }
        .footer { text-align: center; font-size: 11px; color: #888; border-top: 1px dashed #cbd5e1; padding-top: 15px; }
        .print-btn { display: inline-block; padding: 10px 20px; background: #3c8dbc; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-bottom: 20px; cursor: pointer; border: none; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="no-print" style="margin-bottom: 15px; text-align: right;">
            <button onclick="window.print()" class="print-btn">🖨️ Print Invoice</button>
        </div>

        <div class="header">
            <div class="brand-info">
                <img src="{{ asset('images/logo.png') }}" alt="{{ $storeName }}">
                <div class="brand-text">
                    <h2>{{ $storeName }}</h2>
                    <p>{{ $storeAddress }}</p>
                    <p>Helpline: {{ $storePhone }} | {{ $storeEmail }}</p>
                </div>
            </div>
            <div class="invoice-title">
                <h1>INVOICE</h1>
                <p><strong>Invoice #:</strong> {{ $order->invoice_no }}</p>
                <p><strong>Date:</strong> {{ $order->created_at->format('d M, Y - h:i A') }}</p>
                <p><strong>Payment:</strong> Cash on Delivery (COD)</p>
            </div>
        </div>

        <div class="details-grid">
            <div class="box">
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> {{ $order->customer_name }}</p>
                <p><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                <p><strong>Delivery Address:</strong> {{ $order->customer_address }}</p>
                <p><strong>Region:</strong> {{ $order->city_type === 'inside_dhaka' ? 'Inside Dhaka' : 'Outside Dhaka' }}</p>
            </div>

            <div class="box">
                <h3>Order Status & Notes</h3>
                <p><strong>Fulfillment Status:</strong> {{ strtoupper($order->status) }}</p>
                @if($order->note)
                    <p><strong>Customer Note:</strong> {{ $order->note }}</p>
                @endif
                <p><strong>Delivery Method:</strong> Express Courier (Home Delivery)</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">#</th>
                    <th>Product Description</th>
                    <th class="text-center">Size / Age</th>
                    <th class="text-right">Price</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $item->product_name }}</strong></td>
                        <td class="text-center">{{ $item->size ?? 'Standard' }}</td>
                        <td class="text-right">৳ {{ number_format($item->price) }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">৳ {{ number_format($item->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Items Subtotal:</td>
                    <td class="text-right">৳ {{ number_format($order->subtotal) }}</td>
                </tr>
                <tr>
                    <td>Delivery Charge:</td>
                    <td class="text-right">৳ {{ number_format($order->delivery_charge) }}</td>
                </tr>
                @if($order->discount > 0)
                    <tr>
                        <td>Discount:</td>
                        <td class="text-right">- ৳ {{ number_format($order->discount) }}</td>
                    </tr>
                @endif
                <tr class="grand-total">
                    <td>Total Payable (COD):</td>
                    <td class="text-right">৳ {{ number_format($order->total_amount) }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Thank you for shopping with <strong>{{ $storeName }}</strong>!</p>
            <p>For any queries or returns, please contact our support at {{ $storePhone }} or visit our website.</p>
        </div>
    </div>
</body>
</html>
