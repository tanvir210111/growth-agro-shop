<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        :root {
            --primary-dark: #2d3274;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0; padding: 20px;
            background-color: #f0f2f5;
            display: flex; justify-content: center;
        }

        @page { size: A4; margin: 0; }

        @media print {
            .no-print { display: none !important; }
            body { background-color: #fff !important; padding: 0 !important; }
            .invoice-container { 
                box-shadow: none !important; border: none !important; 
                width: 210mm !important; height: 297mm !important; 
                margin: 0 !important; padding: 0 !important; 
            }
            .header, .footer { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }

        .invoice-container {
            width: 210mm;
            min-height: 297mm;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            position: relative;
            display: flex;
            flex-direction: column;
            margin: 0 auto;
        }

        .header {
            background-color: var(--primary-dark);
            color: white;
            padding: 50px 50px 70px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 { font-size: 32px; margin: 0; text-transform: uppercase; }
        .header h2 { font-size: 55px; margin: 0; font-weight: 800; opacity: 0.9; }
        .header p { margin: 5px 0; opacity: 0.9; font-size: 14px; }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            padding: 40px 50px;
            gap: 50px;
        }

        .info-box h3 { color: var(--primary-dark); border-bottom: 2px solid #eee; padding-bottom: 5px; font-size: 18px; margin-bottom: 12px; text-transform: uppercase; }
        .info-box p { font-size: 15px; color: #444; line-height: 1.6; margin: 5px 0; }

        .table-container { padding: 10px 50px; flex-grow: 1; }
        table { width: 100%; border-collapse: collapse; }
        thead tr { background-color: var(--primary-dark); color: white; }
        th { padding: 15px; text-align: left; font-size: 14px; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: top; font-size: 15px; }

        .description-text { white-space: pre-line; color: #333; line-height: 1.6; }

        .calculation-area { display: flex; justify-content: flex-end; padding: 20px 50px; }
        .calc-table { width: 320px; }
        .calc-table tr td { padding: 10px; border: none; font-size: 15px; }
        .bg-gray { background-color: #f4f4f4; font-weight: 600; color: #555; width: 60%; }
        .bg-light { background-color: #fcfcfc; text-align: right; font-weight: 700; color: #333; }
        .total-row td { background-color: var(--primary-dark) !important; color: white !important; font-weight: bold; font-size: 18px; }

        .signature-wrapper { padding: 40px 50px; display: flex; justify-content: flex-end; }
        .signature-box { text-align: center; width: 220px; position: relative; }
        .sig-line { border-top: 2px solid #333; margin-bottom: 8px; }
        
        /* সিগনেচার ইমেজ স্টাইল */
        .sig-image {
            max-width: 150px;
            max-height: 60px;
            position: absolute;
            bottom: 30px; /* লাইনের উপরে পজিশন */
            left: 50%;
            transform: translateX(-50%);
            display: block;
        }

        .footer {
            background-color: var(--primary-dark); color: white; padding: 40px 50px;
            margin-top: auto;
        }

        .action-btns { position: fixed; bottom: 30px; right: 30px; z-index: 1000; display: flex; flex-direction: column; gap: 10px; }
        .btn-action { background: #2d3274; color: white; border: none; padding: 15px 30px; border-radius: 50px; font-weight: bold; cursor: pointer; box-shadow: 0 5px 15px rgba(0,0,0,0.2); font-size: 14px; text-align: center; transition: 0.3s; }
        .btn-download { background: #ff4757; }
        .btn-action:hover { transform: scale(1.05); }
    </style>
</head>
<body>

<div class="action-btns no-print">
    <button class="btn-action" onclick="window.print()">Print Invoice</button>
    <button class="btn-action btn-download" onclick="downloadInvoicePDF()">Download PDF Directly</button>
</div>

<div id="invoice-page" class="invoice-container">
    <div class="header">
        <div class="header-left">
            @if($gs->logo)
                <img src="{{ url('assets/images/logo/memo.png') }}" crossorigin="anonymous" style="max-width: 250px; max-height: 100px; margin-bottom: 10px; display: block;">
            @else
                <h1>{{ $gs->title }}</h1>
            @endif
            <p>{{ $gs->header_address }}</p>
            <div style="margin-top: 15px;">
                <p>Invoice : <strong>#{{ $order->order_number }}</strong></p>
                <p>Date : {{ $order->created_at->format('d/m/Y') }}</p>
            </div>
        </div>
        <div class="header-right">
            <h2>INVOICE</h2>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <h3>Payment Status</h3>
            <p><strong>Method :</strong> {{ $order->payment_method }}</p>
            <p><strong>Status :</strong> 
                <span style="color: {{ $order->due_amount > 0 ? '#ef4444' : '#10b981' }}; font-weight: bold;">
                    {{ $order->due_amount > 0 ? 'PARTIAL/DUE' : 'FULLY PAID' }}
                </span>
            </p>
            <p><strong>Transaction ID :</strong> #{{ strtoupper(substr($order->hash_token, 0, 8)) }}</p>
        </div>
        <div class="info-box">
            <h3>Billed To</h3>
            <p><strong>{{ $order->customer->name }}</strong></p>
            <p>Phone: {{ $order->customer->phone }}</p>
            <p>{{ $order->customer->address ?? 'Customer Address' }}</p>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th width="60" style="text-align: center;">SL.</th>
                    <th style="text-align: left;">Product Details (Qty x Price)</th>
                    <th width="160" style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center; border-right: 1px solid #eee; font-weight: bold;">01.</td>
                    <td>
                        <div class="description-text">{{ $order->description }}</div>
                    </td>
                    <td style="text-align: right; font-weight: bold; font-size: 18px; color: var(--primary-dark);">
                        ৳ {{ number_format($order->total_amount, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="calculation-area">
        <table class="calc-table">
            <tr><td class="bg-gray">Sub Total :</td><td class="bg-light">৳ {{ number_format($order->total_amount, 2) }}</td></tr>
            <tr><td class="bg-gray">Paid Amount :</td><td class="bg-light" style="color: #10b981;">৳ {{ number_format($order->paid_amount, 2) }}</td></tr>
            @if($order->due_amount > 0)
                <tr><td class="bg-gray" style="color: #ef4444;">Balance Due :</td><td class="bg-light" style="color: #ef4444;">৳ {{ number_format($order->due_amount, 2) }}</td></tr>
            @endif
            <tr class="total-row"><td>Grand Total :</td><td style="text-align: right;">৳ {{ number_format($order->total_amount, 2) }}</td></tr>
        </table>
    </div>

    <div class="signature-wrapper">
        <div class="signature-box">
            {{-- সিগনেচার ইমেজ যোগ করা হলো --}}
            <img src="{{ url('assets/images/logo/signature.png') }}" class="sig-image" crossorigin="anonymous" alt="Signature">
            
            <div class="sig-line"></div>
            <span>Authorized Signature</span>
        </div>
    </div>

    <div class="footer">
        <p><strong>Email :</strong> {{ $gs->email }} | <strong>Web :</strong> www.creativedesign.com.bd</p>
        <p><strong>Address :</strong> House: Munshi Bari, Nayarhat, Lalmonirhat.</p>
        <p><strong>Mob :</strong> 01849832178</p>
        
        @if($order->support_note)
            <div style="margin-top: 15px;">
                <p style="font-weight: bold;">Note: {{ $order->support_note }}</p>
            </div>
        @endif
    </div>
</div>

<script>
    function downloadInvoicePDF() {
        const element = document.getElementById('invoice-page');
        
        const options = {
            margin: 0,
            filename: 'Invoice-{{ $order->order_number }}.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 3, 
                useCORS: true, 
                letterRendering: true,
                allowTaint: false
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        // সিগনেচার এবং লোগো লোড হওয়ার জন্য সামান্য সময় দেওয়া
        setTimeout(() => {
            html2pdf().set(options).from(element).save();
        }, 500);
    }
</script>

</body>
</html>