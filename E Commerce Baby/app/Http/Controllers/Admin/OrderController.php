<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = $request->query('search');

        $query = Order::with('items')->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        // Counts for tabs
        $counts = [
            'all' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'returned' => Order::where('status', 'returned')->count(),
        ];

        return view('admin.pages.orders.index', compact('orders', 'status', 'search', 'counts'));
    }

    public function show($id)
    {
        $order = Order::with('items')->findOrFail($id);
        return view('admin.pages.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,returned'
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Order #{$order->invoice_no} status updated to " . ucfirst($order->status),
                'status' => $order->status
            ]);
        }

        return back()->with('success', "Order #{$order->invoice_no} status updated to " . ucfirst($order->status));
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'customer_address' => 'required|string',
            'city_type' => 'required|in:inside_dhaka,outside_dhaka',
            'delivery_charge' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,returned',
            'note' => 'nullable|string',
        ]);

        $order->customer_name = $request->customer_name;
        $order->customer_phone = $request->customer_phone;
        $order->customer_address = $request->customer_address;
        $order->city_type = $request->city_type;
        $order->delivery_charge = (float) $request->delivery_charge;
        $order->discount = (float) ($request->discount ?? 0);
        $order->status = $request->status;
        $order->note = $request->note;

        // Recalculate total
        $order->total_amount = max(0, ($order->subtotal + $order->delivery_charge) - $order->discount);
        $order->save();

        return back()->with('success', 'Order updated successfully.');
    }

    public function invoice($id)
    {
        $order = Order::with('items')->findOrFail($id);
        $storeName = Setting::get('store_name', 'Baby Fashion BD');
        $storePhone = Setting::get('store_phone', '01560-016740');
        $storeEmail = Setting::get('store_email', 'support@babyfashionbd.com');
        $storeAddress = Setting::get('store_address', 'Bashundhara R/A, Dhaka, Bangladesh');

        return view('admin.pages.orders.invoice', compact('order', 'storeName', 'storePhone', 'storeEmail', 'storeAddress'));
    }

    public function create()
    {
        $products = Product::where('status', true)->get();
        return view('admin.pages.orders.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'customer_address' => 'required|string',
            'city_type' => 'required|in:inside_dhaka,outside_dhaka',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.size' => 'nullable|string',
        ]);

        $deliveryRate = $request->city_type === 'inside_dhaka'
            ? (float) Setting::get('delivery_inside_dhaka', 70)
            : (float) Setting::get('delivery_outside_dhaka', 130);

        $subtotal = 0;
        $orderItemsData = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $price = (float) $product->sale_price;
                $qty = (int) $item['quantity'];
                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->title,
                    'product_image' => $product->featured_image,
                    'size' => $item['size'] ?? 'Standard',
                    'price' => $price,
                    'quantity' => $qty,
                    'total' => $lineTotal,
                ];
            }
        }

        $freeThreshold = (float) Setting::get('free_delivery_threshold', 3000);
        if ($subtotal >= $freeThreshold) {
            $deliveryRate = 0;
        }

        $totalAmount = $subtotal + $deliveryRate;

        $order = Order::create([
            'invoice_no' => Order::generateInvoiceNo(),
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_address' => $request->customer_address,
            'city_type' => $request->city_type,
            'delivery_charge' => $deliveryRate,
            'subtotal' => $subtotal,
            'discount' => 0,
            'total_amount' => $totalAmount,
            'payment_method' => 'COD',
            'status' => 'pending',
            'note' => $request->note ?? 'Manual order placed from admin panel',
        ]);

        foreach ($orderItemsData as $itemData) {
            $itemData['order_id'] = $order->id;
            OrderItem::create($itemData);
        }

        return redirect()->route('admin.orders.show', $order->id)->with('success', "Order #{$order->invoice_no} created successfully.");
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully.');
    }
}
