<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
  <title>অর্ডার কনফার্মেশন #{{ $order['order_number'] }} | {{ $landingPageTitle }}</title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/images/logo.png">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Centralized Meta Pixel & PageView -->
  @include('partials.meta-pixel')

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Hind Siliguri', 'Outfit', system-ui, -apple-system, sans-serif;
      background: #f8fafc;
      color: #0f172a;
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .success-header {
      background: #ffffff;
      border-bottom: 1px solid #e2e8f0;
      padding: 1rem 1.5rem;
      text-align: center;
      box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .success-header .brand-title {
      font-family: 'Outfit', 'Hind Siliguri', sans-serif;
      font-size: 1.25rem;
      font-weight: 800;
      color: #0f766e;
      letter-spacing: -0.02em;
    }

    .success-container {
      max-width: 680px;
      margin: 2.5rem auto 4rem;
      padding: 0 1rem;
      width: 100%;
      flex: 1;
    }

    .success-card {
      background: #ffffff;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      padding: 2.5rem 2rem;
      box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.03);
      text-align: center;
    }

    .checkmark-wrap {
      width: 76px;
      height: 76px;
      background: #ecfdf5;
      color: #10b981;
      border: 2px solid #a7f3d0;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.4rem;
      font-weight: bold;
      margin: 0 auto 1.25rem;
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }

    .badge-status {
      display: inline-block;
      background: #f0fdf4;
      color: #166534;
      font-size: 0.85rem;
      font-weight: 700;
      padding: 0.35rem 0.9rem;
      border-radius: 9999px;
      border: 1px solid #bbf7d0;
      margin-bottom: 0.75rem;
    }

    .heading-title {
      font-size: 1.85rem;
      font-weight: 800;
      color: #0f172a;
      margin: 0.3rem 0 0.6rem;
    }

    .subtext {
      color: #475569;
      font-size: 0.98rem;
      line-height: 1.6;
      margin-bottom: 2rem;
      max-width: 540px;
      margin-left: auto;
      margin-right: auto;
    }

    .invoice-card {
      background: #f8fafc;
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      padding: 1.5rem;
      text-align: left;
      margin-bottom: 2rem;
    }

    .invoice-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.1rem;
      border-bottom: 1px solid #e2e8f0;
      padding-bottom: 0.75rem;
    }

    .invoice-title {
      font-size: 1.15rem;
      font-weight: 700;
      color: #0f172a;
    }

    .order-number-badge {
      font-size: 0.88rem;
      font-weight: 800;
      color: #0f766e;
      background: #ccfbf1;
      padding: 0.25rem 0.65rem;
      border-radius: 6px;
    }

    .info-list {
      display: flex;
      flex-direction: column;
      gap: 0.65rem;
      font-size: 0.92rem;
      color: #334155;
    }
    .info-row {
      display: flex;
      justify-content: space-between;
      gap: 1rem;
    }
    .info-label { color: #64748b; }
    .info-val { color: #0f172a; text-align: right; }

    .items-section {
      margin-top: 1.25rem;
      border-top: 1px dashed #cbd5e1;
      padding-top: 1rem;
    }
    .items-title {
      font-size: 0.95rem;
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 0.85rem;
    }
    .item-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.92rem;
      padding: 0.45rem 0;
      border-bottom: 1px solid #f1f5f9;
    }

    .totals-section {
      border-top: 2px solid #e2e8f0;
      margin-top: 1.25rem;
      padding-top: 0.9rem;
      font-size: 0.93rem;
    }
    .total-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.45rem;
      color: #475569;
    }
    .grand-total {
      display: flex;
      justify-content: space-between;
      font-size: 1.3rem;
      font-weight: 800;
      color: #dc2626;
      margin-top: 0.75rem;
      padding-top: 0.6rem;
      border-top: 1px solid #e2e8f0;
    }

    .actions-wrap {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
    }

    .btn-return {
      background: #0f766e;
      color: #ffffff;
      padding: 0.8rem 1.6rem;
      border-radius: 8px;
      font-weight: 700;
      text-decoration: none;
      font-size: 0.95rem;
      transition: background 0.2s, transform 0.15s;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    .btn-return:hover {
      background: #115e59;
      transform: translateY(-1px);
    }

    .btn-whatsapp {
      background: #25D366;
      color: #ffffff;
      padding: 0.8rem 1.5rem;
      border-radius: 8px;
      font-weight: 700;
      text-decoration: none;
      font-size: 0.95rem;
      transition: background 0.2s, transform 0.15s;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    .btn-whatsapp:hover {
      background: #20ba56;
      transform: translateY(-1px);
    }

    .btn-receipt {
      background: #1e293b;
      color: #ffffff;
      padding: 0.8rem 1.5rem;
      border-radius: 8px;
      font-weight: 700;
      border: none;
      cursor: pointer;
      font-size: 0.95rem;
      font-family: inherit;
      transition: background 0.2s, transform 0.15s;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    .btn-receipt:hover {
      background: #0f172a;
      transform: translateY(-1px);
    }

    .footer-note {
      text-align: center;
      font-size: 0.85rem;
      color: #94a3b8;
      padding: 1.5rem 1rem;
      border-top: 1px solid #e2e8f0;
      background: #ffffff;
      margin-top: auto;
    }

    @media print {
      body {
        background: #ffffff !important;
        color: #0f172a !important;
        font-size: 12pt;
      }
      .success-header, .actions-wrap, .footer-note, .btn-return, .btn-whatsapp, .btn-receipt {
        display: none !important;
      }
      .success-container {
        margin: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
      }
      .success-card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        text-align: left !important;
      }
      .checkmark-wrap {
        display: none !important;
      }
      .heading-title {
        font-size: 1.4rem !important;
        margin-bottom: 0.25rem !important;
      }
      .subtext {
        font-size: 0.9rem !important;
        margin-bottom: 1.25rem !important;
        max-width: 100% !important;
      }
      .invoice-card {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        padding: 1.25rem !important;
        border-radius: 8px !important;
      }
      .order-number-badge {
        border: 1px solid #0f766e !important;
        background: #f0fdfa !important;
      }
      .print-receipt-header {
        display: block !important;
        text-align: center;
        border-bottom: 2px solid #0f766e;
        padding-bottom: 0.75rem;
        margin-bottom: 1.25rem;
      }
      .print-receipt-header h2 {
        font-size: 1.5rem;
        color: #0f766e;
        margin: 0;
      }
      .print-receipt-header p {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
      }
    }
  </style>
</head>
<body>

  <!-- Minimal Clean Header -->
  <header class="success-header">
    <div class="brand-title">
      {{ $landingPageTitle }}
    </div>
  </header>

  <!-- Main Success Content -->
  <main class="success-container">
    <div class="success-card">
      
      <!-- Print Only Header -->
      <div class="print-receipt-header" style="display:none;">
        <h2>{{ $landingPageTitle }}</h2>
        <p>অফিশিয়াল ক্যাশ অন ডেলিভারি অর্ডার রসিদ (Official Purchase Receipt)</p>
      </div>

      <!-- Checkmark -->
      <div class="checkmark-wrap">✓</div>

      <!-- Badge -->
      <span class="badge-status">
        অর্ডার সফল হয়েছে • Order Confirmed
      </span>

      <!-- Title -->
      <h1 class="heading-title">
        ধন্যবাদ, {{ $order['customer_name'] }}!
      </h1>

      <p class="subtext">
        আপনার অর্ডার <strong>#{{ $order['order_number'] }}</strong> সফলভাবে গ্রহণ করা হয়েছে। অর্ডারটি নিশ্চিত করতে আমাদের প্রতিনিধি শীঘ্রই <strong>{{ $order['customer_phone'] }}</strong> নম্বরে আপনার সাথে ফোনে যোগাযোগ করবেন।
      </p>

      <!-- Invoice Details Card -->
      <div class="invoice-card">
        <div class="invoice-header">
          <h3 class="invoice-title">অর্ডারের বিবরণ (Order Invoice)</h3>
          <span class="order-number-badge">#{{ $order['order_number'] }}</span>
        </div>

        <div class="info-list">
          <div class="info-row">
            <span class="info-label">পণ্য / ল্যান্ডিং পেজ:</span>
            <strong class="info-val" style="color: #0f766e;">{{ $landingPageTitle }}</strong>
          </div>
          <div class="info-row">
            <span class="info-label">অর্ডারের তারিখ:</span>
            <span class="info-val">{{ $order['created_at'] ?? date('d M, Y h:i A') }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">গ্রাহকের নাম:</span>
            <strong class="info-val">{{ $order['customer_name'] }}</strong>
          </div>
          <div class="info-row">
            <span class="info-label">মোবাইল নম্বর:</span>
            <strong class="info-val">{{ $order['customer_phone'] }}</strong>
          </div>
          <div class="info-row">
            <span class="info-label">ডেলিভারি ঠিকানা:</span>
            <span class="info-val" style="max-width: 65%;">{{ $order['customer_address'] }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">ডেলিভারি অঞ্চল:</span>
            <span class="info-val">{{ $order['delivery_area_label'] }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">পেমেন্ট পদ্ধতি:</span>
            <span class="info-val" style="color: #166534; font-weight: 700;">ক্যাশ অন ডেলিভারি (Cash on Delivery)</span>
          </div>
        </div>

        <!-- Ordered Items -->
        <div class="items-section">
          <h4 class="items-title">অর্ডারকৃত পণ্যসমূহ:</h4>
          <div class="info-list">
            @foreach($order['items'] as $it)
              <div class="item-row">
                <div>
                  <strong style="color: #0f172a;">{{ $it['title'] ?? $it['name'] ?? 'Product' }}</strong>
                  @if(!empty($it['size']) && $it['size'] !== 'Default' && $it['size'] !== 'Standard')
                    <span style="color: #64748b; font-size: 0.85rem;">({{ $it['size'] }})</span>
                  @endif
                  <span style="color: #64748b; margin-left: 0.35rem;">&times; {{ $it['quantity'] }} টি</span>
                </div>
                <div style="font-weight: 700; color: #0f172a;">
                  ৳ {{ number_format($it['price'] * $it['quantity']) }}
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <!-- Totals -->
        <div class="totals-section">
          <div class="total-row">
            <span>সাবটোটাল (Subtotal):</span>
            <span>৳ {{ number_format($order['subtotal']) }}</span>
          </div>
          <div class="total-row">
            <span>ডেলিভারি চার্জ (Delivery):</span>
            <span style="{{ (float)$order['shipping'] === 0.0 ? 'color:#16a34a; font-weight:700;' : '' }}">
              {{ (float)$order['shipping'] === 0.0 ? 'ফ্রি (FREE)' : '৳ ' . number_format($order['shipping']) }}
            </span>
          </div>
          <div class="grand-total">
            <span>সর্বমোট বিল (Total Payable):</span>
            <span>৳ {{ number_format($order['total']) }}</span>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="actions-wrap">
        <button type="button" class="btn-receipt" onclick="window.print()" id="btnDownloadReceipt">
          📄 রসিদ ডাউনলোড / প্রিন্ট করুন
        </button>
        <a href="{{ $landingPageUrl }}" class="btn-return">
          পণ্য পেজে ফিরে যান &rarr;
        </a>
        @php
          $whatsappNumber = $landingPage->content['footer']['whatsapp_phone'] ?? '8801560016740';
          $whatsappClean = preg_replace('/[^0-9]/', '', $whatsappNumber);
        @endphp
        <a href="https://wa.me/{{ $whatsappClean }}?text=Hi,%20I%20have%20an%20inquiry%20regarding%20Order%20{{ $order['order_number'] }}" class="btn-whatsapp" target="_blank" rel="noopener noreferrer">
          💬 হোয়াটসঅ্যাপে যোগাযোগ
        </a>
      </div>

    </div>
  </main>

  <footer class="footer-note">
    &copy; {{ date('Y') }} {{ $landingPageTitle }}. সর্বস্বত্ব সংরক্ষিত।
  </footer>

  <!-- Global Tracking & Meta Pixel Purchase Script -->
  <script src="/js/growth-agro-tracking.js"></script>
  <script>
    (function() {
      const orderNo = "{{ $order['order_number'] }}";
      const totalVal = {{ (float)($order['total'] ?? 0) }};
      const itemsCount = {{ count($order['items'] ?? []) }};
      const purchaseEventId = 'purchase_' + orderNo;
      const dedupeKey = 'ga_tracked_order_' + orderNo;

      if (!sessionStorage.getItem(dedupeKey) && window.GrowthAgroTracking) {
        sessionStorage.setItem(dedupeKey, '1');
        window.GrowthAgroTracking.track('purchase', {
          event_id: purchaseEventId,
          entity_type: 'order',
          entity_id: orderNo,
          event_value: totalVal,
          properties: {
            items_count: itemsCount,
            currency: 'BDT',
            landing_page: "{{ $landingPageUrl }}"
          }
        });
      }

      // Meta Pixel Purchase Event with Deduplication Guard
      const metaDedupeKey = 'meta_tracked_purchase_' + orderNo;
      if (!sessionStorage.getItem(metaDedupeKey) && typeof window.fbq === 'function') {
        sessionStorage.setItem(metaDedupeKey, '1');
        window.fbq('track', 'Purchase', {
          content_ids: @json(collect($order['items'] ?? [])->pluck('title')->values()->toArray()),
          content_name: '{{ addslashes($landingPageTitle ?: ($order["items"][0]["title"] ?? "Order #" . $order["order_number"])) }}',
          content_type: 'product',
          value: totalVal,
          currency: 'BDT',
          num_items: {{ array_sum(array_column($order['items'] ?? [], 'quantity')) ?: 1 }}
        }, {
          eventID: purchaseEventId
        });
      }
    })();
  </script>
</body>
</html>
