<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the Captain Crown Style Exact Admin Dashboard.
     */
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // 12 Exact Order Status Counts & Amounts
        $newOrdersCount = Order::where('status', 'pending')->whereDate('created_at', $today)->count();
        $newOrdersAmount = Order::where('status', 'pending')->whereDate('created_at', $today)->sum('total_amount');

        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $pendingOrdersAmount = Order::where('status', 'pending')->sum('total_amount');

        $wfaOrdersCount = Order::where('status', 'pending')->count(); // Waiting for approval

        $approvedOrdersCount = Order::where('status', 'processing')->count();
        $approvedOrdersAmount = Order::where('status', 'processing')->sum('total_amount');

        $packagingOrdersCount = Order::where('status', 'processing')->count();
        $packagingOrdersAmount = 0;

        $shipmentOrdersCount = Order::where('status', 'shipped')->count();
        $shipmentOrdersAmount = Order::where('status', 'shipped')->sum('total_amount');

        $partialDeliveredCount = 0;
        $deliveredOrdersCount = Order::where('status', 'delivered')->count();
        $returnPendingCount = 0;
        $returnOrdersCount = Order::where('status', 'returned')->count();
        $cancelOrdersCount = Order::where('status', 'cancelled')->count();
        $allOrdersCount = Order::count();

        // Accounts Stats
        $todayCredit = Order::where('status', 'delivered')->whereDate('created_at', $today)->sum('total_amount');
        $totalSales = Order::where('status', 'delivered')->sum('total_amount');

        // Recent Orders
        $recentOrders = Order::with('items')->latest()->take(10)->get();

        // Top Bestsellers
        $bestsellers = Product::where('status', true)->orderBy('stock', 'asc')->take(5)->get();

        return view('admin.pages.dashboard', compact(
            'newOrdersCount',
            'newOrdersAmount',
            'pendingOrdersCount',
            'pendingOrdersAmount',
            'wfaOrdersCount',
            'approvedOrdersCount',
            'approvedOrdersAmount',
            'packagingOrdersCount',
            'packagingOrdersAmount',
            'shipmentOrdersCount',
            'shipmentOrdersAmount',
            'partialDeliveredCount',
            'deliveredOrdersCount',
            'returnPendingCount',
            'returnOrdersCount',
            'cancelOrdersCount',
            'allOrdersCount',
            'todayCredit',
            'totalSales',
            'recentOrders',
            'bestsellers'
        ));
    }
}
