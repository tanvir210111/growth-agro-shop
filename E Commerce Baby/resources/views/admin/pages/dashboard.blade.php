@extends('admin.layouts.master')

@section('title', 'Dashboard')

@section('content')
<!-- Exact Captain Crown 12 Stat Cards (3 Columns Layout) -->
<div class="cc-stats-container">
    <!-- 1. New Orders -->
    <div class="cc-stat-card border-blue">
        <div class="cc-stat-top">
            <div>
                <div class="cc-stat-data">
                    <span class="cc-stat-count">{{ $newOrdersCount }}</span>
                    <span class="cc-stat-title">New Orders</span>
                </div>
                <div class="cc-stat-amount">৳{{ number_format($newOrdersAmount) }}</div>
            </div>
            <div class="cc-stat-icon">
                <img src="{{ asset('images/dashboard/new_orders.svg') }}" alt="New Orders">
            </div>
        </div>
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="cc-stat-more">More info <i class="fa fa-arrow-circle-right"></i></a>
    </div>

    <!-- 2. Pending Orders -->
    <div class="cc-stat-card border-blue">
        <div class="cc-stat-top">
            <div>
                <div class="cc-stat-data">
                    <span class="cc-stat-count">{{ $pendingOrdersCount }}</span>
                    <span class="cc-stat-title">Pending Orders</span>
                </div>
                <div class="cc-stat-amount">৳{{ number_format($pendingOrdersAmount) }}</div>
            </div>
            <div class="cc-stat-icon">
                <img src="{{ asset('images/dashboard/pending_orders.svg') }}" alt="Pending Orders">
            </div>
        </div>
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="cc-stat-more">More info <i class="fa fa-arrow-circle-right"></i></a>
    </div>

    <!-- 3. Waiting for Approval (WFA) -->
    <div class="cc-stat-card border-blue">
        <div class="cc-stat-top">
            <div>
                <div class="cc-stat-data">
                    <span class="cc-stat-count">{{ $wfaOrdersCount }}</span>
                    <span class="cc-stat-title">Waiting for Approval (WFA)</span>
                </div>
            </div>
            <div class="cc-stat-icon">
                <img src="{{ asset('images/dashboard/wfa.svg') }}" alt="WFA">
            </div>
        </div>
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="cc-stat-more">More info <i class="fa fa-arrow-circle-right"></i></a>
    </div>

    <!-- 4. Approved Order -->
    <div class="cc-stat-card border-green">
        <div class="cc-stat-top">
            <div>
                <div class="cc-stat-data">
                    <span class="cc-stat-count">{{ $approvedOrdersCount }}</span>
                    <span class="cc-stat-title">Approved Order</span>
                </div>
                <div class="cc-stat-amount">৳{{ number_format($approvedOrdersAmount) }}</div>
            </div>
            <div class="cc-stat-icon">
                <img src="{{ asset('images/dashboard/approved.svg') }}" alt="Approved Order">
            </div>
        </div>
        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" class="cc-stat-more">More info <i class="fa fa-arrow-circle-right"></i></a>
    </div>

    <!-- 5. Packaging Order -->
    <div class="cc-stat-card border-green">
        <div class="cc-stat-top">
            <div>
                <div class="cc-stat-data">
                    <span class="cc-stat-count">{{ $packagingOrdersCount }}</span>
                    <span class="cc-stat-title">Packaging Order</span>
                </div>
                <div class="cc-stat-amount">৳{{ number_format($packagingOrdersAmount) }}</div>
            </div>
            <div class="cc-stat-icon">
                <img src="{{ asset('images/dashboard/packaging.svg') }}" alt="Packaging Order">
            </div>
        </div>
        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" class="cc-stat-more">More info <i class="fa fa-arrow-circle-right"></i></a>
    </div>

    <!-- 6. Shipment Orders -->
    <div class="cc-stat-card border-blue">
        <div class="cc-stat-top">
            <div>
                <div class="cc-stat-data">
                    <span class="cc-stat-count">{{ $shipmentOrdersCount }}</span>
                    <span class="cc-stat-title">Shipment Orders</span>
                </div>
                <div class="cc-stat-amount">৳{{ number_format($shipmentOrdersAmount) }}</div>
            </div>
            <div class="cc-stat-icon">
                <img src="{{ asset('images/dashboard/shipment.svg') }}" alt="Shipment Orders">
            </div>
        </div>
        <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" class="cc-stat-more">More info <i class="fa fa-arrow-circle-right"></i></a>
    </div>

    <!-- 7. Partial Delivered -->
    <div class="cc-stat-card border-green">
        <div class="cc-stat-top">
            <div>
                <div class="cc-stat-data">
                    <span class="cc-stat-count">{{ $partialDeliveredCount }}</span>
                    <span class="cc-stat-title">Partial Delivered</span>
                </div>
            </div>
            <div class="cc-stat-icon">
                <img src="{{ asset('images/dashboard/partial_delivered.svg') }}" alt="Partial Delivered">
            </div>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="cc-stat-more">More info <i class="fa fa-arrow-circle-right"></i></a>
    </div>

    <!-- 8. Delivered Order -->
    <div class="cc-stat-card border-green">
        <div class="cc-stat-top">
            <div>
                <div class="cc-stat-data">
                    <span class="cc-stat-count">{{ $deliveredOrdersCount }}</span>
                    <span class="cc-stat-title">Delivered Order</span>
                </div>
            </div>
            <div class="cc-stat-icon">
                <img src="{{ asset('images/dashboard/delivered.svg') }}" alt="Delivered Order">
            </div>
        </div>
        <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" class="cc-stat-more">More info <i class="fa fa-arrow-circle-right"></i></a>
    </div>

    <!-- 9. Return Pending -->
    <div class="cc-stat-card border-red">
        <div class="cc-stat-top">
            <div>
                <div class="cc-stat-data">
                    <span class="cc-stat-count">{{ $returnPendingCount }}</span>
                    <span class="cc-stat-title">Return Pending</span>
                </div>
            </div>
            <div class="cc-stat-icon">
                <img src="{{ asset('images/dashboard/return_pending.svg') }}" alt="Return Pending">
            </div>
        </div>
        <a href="{{ route('admin.orders.index', ['status' => 'returned']) }}" class="cc-stat-more">More info <i class="fa fa-arrow-circle-right"></i></a>
    </div>

    <!-- 10. Return Order -->
    <div class="cc-stat-card border-red">
        <div class="cc-stat-top">
            <div>
                <div class="cc-stat-data">
                    <span class="cc-stat-count">{{ $returnOrdersCount }}</span>
                    <span class="cc-stat-title">Return Order</span>
                </div>
            </div>
            <div class="cc-stat-icon">
                <img src="{{ asset('images/dashboard/return_order.svg') }}" alt="Return Order">
            </div>
        </div>
        <a href="{{ route('admin.orders.index', ['status' => 'returned']) }}" class="cc-stat-more">More info <i class="fa fa-arrow-circle-right"></i></a>
    </div>

    <!-- 11. Cancel Orders -->
    <div class="cc-stat-card border-red">
        <div class="cc-stat-top">
            <div>
                <div class="cc-stat-data">
                    <span class="cc-stat-count">{{ $cancelOrdersCount }}</span>
                    <span class="cc-stat-title">Cancel Orders</span>
                </div>
            </div>
            <div class="cc-stat-icon">
                <img src="{{ asset('images/dashboard/cancel_orders.svg') }}" alt="Cancel Orders">
            </div>
        </div>
        <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="cc-stat-more">More info <i class="fa fa-arrow-circle-right"></i></a>
    </div>

    <!-- 12. All Orders -->
    <div class="cc-stat-card border-blue">
        <div class="cc-stat-top">
            <div>
                <div class="cc-stat-data">
                    <span class="cc-stat-count">{{ $allOrdersCount }}</span>
                    <span class="cc-stat-title">All Orders</span>
                </div>
            </div>
            <div class="cc-stat-icon">
                <img src="{{ asset('images/dashboard/all_orders.svg') }}" alt="All Orders">
            </div>
        </div>
        <a href="{{ route('admin.orders.index', ['status' => 'all']) }}" class="cc-stat-more">More info <i class="fa fa-arrow-circle-right"></i></a>
    </div>
</div>

<!-- Accounts Section (Exact Captain Crown 3 Columns Box) -->
<h2 class="cc-accounts-title">Accounts</h2>

<div class="cc-accounts-container">
    <!-- Box 1: TODAY CREDIT -->
    <div class="cc-account-box">
        <div class="cc-account-body">
            <div class="cc-account-row"><span>In Cash :</span> <strong>0</strong></div>
            <div class="cc-account-row"><span>In DBBL :</span> <strong>0</strong></div>
            <div class="cc-account-row"><span>In City Bank :</span> <strong>0</strong></div>
            <div class="cc-account-row"><span>In Nagad :</span> <strong>0</strong></div>
            <div class="cc-account-row"><span>In Bkash :</span> <strong>0</strong></div>
            <div class="cc-account-row"><span>In Brack :</span> <strong>0</strong></div>
            <div class="cc-account-row total-row"><span>In Total :</span> <strong>0.00</strong></div>
        </div>
        <div class="cc-account-footer-btn">TODAY CREDIT</div>
    </div>

    <!-- Box 2: TODAY DEBIT -->
    <div class="cc-account-box">
        <div class="cc-account-body">
            <div class="cc-account-row"><span>In Cash :</span> <strong>0</strong></div>
            <div class="cc-account-row"><span>In DBBL :</span> <strong>0</strong></div>
            <div class="cc-account-row"><span>In City Bank :</span> <strong>0</strong></div>
            <div class="cc-account-row"><span>In Nagad :</span> <strong>0</strong></div>
            <div class="cc-account-row"><span>In Bkash :</span> <strong>0</strong></div>
            <div class="cc-account-row"><span>In Brack :</span> <strong>0</strong></div>
            <div class="cc-account-row total-row"><span>In Total :</span> <strong>0.00</strong></div>
        </div>
        <div class="cc-account-footer-btn">TODAY DEBIT</div>
    </div>

    <!-- Box 3: TOTAL BALANCE -->
    <div class="cc-account-box">
        <div class="cc-account-body">
            <div class="cc-account-row"><span>In Cash :</span> <strong>0</strong></div>
            <div class="cc-account-row"><span>In DBBL :</span> <strong>0</strong></div>
            <div class="cc-account-row"><span>In City Bank :</span> <strong>0</strong></div>
            <div class="cc-account-row"><span>In Nagad :</span> <strong>0</strong></div>
            <div class="cc-account-row"><span>In Bkash :</span> <strong>0</strong></div>
            <div class="cc-account-row"><span>In Brack :</span> <strong>0</strong></div>
            <div class="cc-account-row total-row"><span>In Total :</span> <strong>0.00</strong></div>
        </div>
        <div class="cc-account-footer-btn">TOTAL BALANCE</div>
    </div>
</div>
@endsection
