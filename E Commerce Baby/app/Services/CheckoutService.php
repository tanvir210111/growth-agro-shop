<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CheckoutService
{
    public const SHIPPING_INSIDE_DHAKA = 70;
    public const SHIPPING_OUTSIDE_DHAKA = 130;
    public const FREE_SHIPPING_MIN = 3000;

    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function calculateShipping(string $area, int $subtotal): int
    {
        if ($subtotal >= self::FREE_SHIPPING_MIN) {
            return 0;
        }

        return ($area === 'inside_dhaka') ? self::SHIPPING_INSIDE_DHAKA : self::SHIPPING_OUTSIDE_DHAKA;
    }

    public function calculateOrderSummary(string $area = 'inside_dhaka', ?string $couponCode = null): array
    {
        $subtotal = $this->cartService->getSubtotal();
        $shipping = $this->calculateShipping($area, $subtotal);
        $discount = 0;

        if ($couponCode && strtoupper(trim($couponCode)) === 'BABY10') {
            $discount = (int) round($subtotal * 0.10);
        }

        $total = max(0, $subtotal + $shipping - $discount);

        return [
            'subtotal' => $subtotal,
            'subtotal_formatted' => '৳ ' . number_format($subtotal),
            'shipping' => $shipping,
            'shipping_formatted' => ($shipping === 0) ? 'FREE' : '৳ ' . number_format($shipping),
            'discount' => $discount,
            'discount_formatted' => '৳ ' . number_format($discount),
            'total' => $total,
            'total_formatted' => '৳ ' . number_format($total),
            'is_free_shipping' => ($shipping === 0 && $subtotal > 0),
        ];
    }

    public function placeOrder(array $customerData, ?array $directProduct = null): array
    {
        $orderNumber = 'BFB-' . strtoupper(Str::random(6));

        if ($directProduct) {
            // Direct 1-Click Order Form
            $items = [[
                'id' => 'direct_' . $directProduct['id'],
                'product_id' => $directProduct['id'],
                'title' => $directProduct['title'],
                'price' => $directProduct['price'],
                'size' => $directProduct['size'] ?? 'Standard',
                'color' => $directProduct['color'] ?? 'Default',
                'quantity' => (int)($directProduct['quantity'] ?? 1),
                'image' => $directProduct['image'] ?? '',
            ]];
            $subtotal = $items[0]['price'] * $items[0]['quantity'];
        } else {
            $cartSummary = $this->cartService->getSummary();
            $items = $cartSummary['items'];
            $subtotal = $cartSummary['subtotal'];
        }

        if (empty($items)) {
            return [
                'success' => false,
                'message' => 'Your cart is empty. Please add items to place an order.'
            ];
        }

        $area = $customerData['delivery_area'] ?? 'inside_dhaka';
        $shipping = $this->calculateShipping($area, $subtotal);
        $total = $subtotal + $shipping;

        $order = [
            'order_number' => $orderNumber,
            'customer_name' => $customerData['customer_name'] ?? 'Valued Customer',
            'customer_phone' => $customerData['customer_phone'] ?? '',
            'customer_address' => $customerData['customer_address'] ?? '',
            'delivery_area' => $area,
            'delivery_area_label' => ($area === 'inside_dhaka') ? 'Inside Dhaka (৳ 70)' : 'Outside Dhaka (৳ 130)',
            'payment_method' => $customerData['payment_method'] ?? 'cash_on_delivery',
            'payment_method_label' => 'Cash on Delivery (COD)',
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
            'notes' => $customerData['order_notes'] ?? '',
            'created_at' => now()->toDateTimeString(),
            'status' => 'Pending Confirmation'
        ];

        // Persist to Database
        try {
            $dbOrder = Order::create([
                'invoice_no' => $orderNumber,
                'customer_name' => $order['customer_name'],
                'customer_phone' => $order['customer_phone'],
                'customer_address' => $order['customer_address'],
                'city_type' => $area,
                'delivery_charge' => $shipping,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total_amount' => $total,
                'payment_method' => 'COD',
                'status' => 'pending',
                'note' => $order['notes'],
            ]);

            foreach ($items as $it) {
                \App\Models\OrderItem::create([
                    'order_id' => $dbOrder->id,
                    'product_id' => is_numeric($it['product_id'] ?? null) ? $it['product_id'] : null,
                    'product_name' => $it['title'] ?? $it['name'] ?? 'Baby Outfit',
                    'product_image' => $it['image'] ?? '',
                    'size' => $it['size'] ?? 'Standard',
                    'price' => $it['price'] ?? 0,
                    'quantity' => $it['quantity'] ?? 1,
                    'total' => ($it['price'] ?? 0) * ($it['quantity'] ?? 1),
                ]);
            }
        } catch (\Exception $e) {
            // log error
        }

        // Store in session and recent orders
        Session::put("order_{$orderNumber}", $order);
        Session::put('last_order', $order);

        // Clear cart if ordered from regular cart
        if (!$directProduct) {
            $this->cartService->clear();
        }

        return [
            'success' => true,
            'order_number' => $orderNumber,
            'order' => $order,
        ];
    }

    public function getOrder(string $orderNumber): ?array
    {
        return Session::get("order_{$orderNumber}", Session::get('last_order'));
    }
}
