@extends('admin.layouts.master')

@section('title', $moduleTitle . ' - ' . $pageTitle)

@section('content')
<div class="content-header" style="margin-bottom: 20px;">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; color: #1f2937; display: flex; align-items: center; gap: 8px;">
            <i class="fa fa-folder-open" style="color: #178ca4;"></i>
            {{ $moduleTitle }} <span style="font-size: 14px; font-weight: 500; color: #6b7280;">/ {{ $pageTitle }}</span>
        </h1>
        <p style="font-size: 12px; color: #6b7280; margin-top: 2px;">Manage and monitor {{ strtolower($moduleTitle) }} records, actions, and settings</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" class="btn-admin btn-admin-primary" onclick="showToast('info', 'New {{ $pageTitle }} modal ready!')">
            <i class="fa fa-plus-circle"></i> Add New {{ $pageTitle }}
        </button>
        <a href="{{ route('admin.dashboard') }}" class="btn-admin btn-admin-default">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<!-- Main Module Card with Shadow -->
<div class="cc-card">
    <div class="cc-card-header">
        <div style="display: flex; gap: 10px; align-items: center; width: 100%; justify-content: space-between;">
            <div style="display: flex; gap: 8px; max-width: 400px; width: 100%;">
                <input type="text" class="form-control-custom" placeholder="Search {{ strtolower($pageTitle) }}...">
                <button type="button" class="btn-admin btn-admin-primary"><i class="fa fa-search"></i></button>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn-admin btn-admin-default btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print</button>
                <button type="button" class="btn-admin btn-admin-default btn-sm" onclick="showToast('success', 'Exporting Excel...')"><i class="fa fa-file-excel"></i> Export</button>
            </div>
        </div>
    </div>

    <div class="cc-card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="width: 60px;">#SL</th>
                        <th>Title / Details</th>
                        <th>Type / Category</th>
                        <th>Amount / Status</th>
                        <th>Date & Time</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if(str_contains(strtolower($module), 'order') || str_contains(strtolower($module), 'sale'))
                        @foreach($orders as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item->customer_name }}</strong>
                                    <small style="display: block; color: #6b7280;">Inv: #{{ $item->invoice_no }} | {{ $item->customer_phone }}</small>
                                </td>
                                <td><span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 11px;">Online Store</span></td>
                                <td><strong style="color: #059669;">৳ {{ number_format($item->total_amount) }}</strong></td>
                                <td style="font-size: 12px; color: #6b7280;">{{ $item->created_at->format('d M, Y h:i A') }}</td>
                                <td style="text-align: center;">
                                    <a href="{{ route('admin.orders.show', $item->id) }}" class="btn-admin btn-admin-default btn-sm"><i class="fa fa-eye"></i></a>
                                    <a href="{{ route('admin.orders.invoice', $item->id) }}" target="_blank" class="btn-admin btn-admin-primary btn-sm"><i class="fa fa-print"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    @elseif(str_contains(strtolower($module), 'product') || str_contains(strtolower($module), 'stock'))
                        @foreach($products as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <img src="{{ Str::startsWith($item->featured_image, 'http') ? $item->featured_image : asset($item->featured_image) }}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                        <div>
                                            <strong>{{ $item->title }}</strong>
                                            <small style="display: block; color: #6b7280;">SKU: {{ $item->sku }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span style="background: #f3f4f6; color: #374151; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 11px;">{{ $item->category->title ?? 'General' }}</span></td>
                                <td><strong style="color: #059669;">৳ {{ number_format($item->sale_price) }}</strong> (Stock: {{ $item->stock }})</td>
                                <td style="font-size: 12px; color: #6b7280;">{{ $item->created_at->format('d M, Y') }}</td>
                                <td style="text-align: center;">
                                    <a href="{{ route('admin.products.edit', $item->id) }}" class="btn-admin btn-admin-primary btn-sm"><i class="fa fa-edit"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        @for($i = 1; $i <= 5; $i++)
                            <tr>
                                <td>{{ $i }}</td>
                                <td>
                                    <strong>{{ $pageTitle }} Record #{{ 100 + $i }}</strong>
                                    <small style="display: block; color: #6b7280;">Active system record in {{ $moduleTitle }}</small>
                                </td>
                                <td><span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 11px;">Processed</span></td>
                                <td><strong>৳ {{ number_format($i * 1250) }}</strong></td>
                                <td style="font-size: 12px; color: #6b7280;">{{ now()->subDays($i)->format('d M, Y h:i A') }}</td>
                                <td style="text-align: center;">
                                    <button class="btn-admin btn-admin-default btn-sm" onclick="showToast('info', 'Viewing Record #{{ 100 + $i }}')"><i class="fa fa-eye"></i></button>
                                    <button class="btn-admin btn-admin-primary btn-sm" onclick="showToast('success', 'Editing Record #{{ 100 + $i }}')"><i class="fa fa-edit"></i></button>
                                </td>
                            </tr>
                        @endfor
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
