<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CartService
{
    protected const SESSION_KEY = 'baby_fashion_cart';
    protected const FREE_SHIPPING_MIN = 3000;

    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function getCart(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public function add(int $productId, ?string $size = null, ?string $color = null, int $quantity = 1): array
    {
        $product = $this->productService->findById($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }

        $size = $size ?: ($product['sizes'][0] ?? 'Standard');
        $color = $color ?: ($product['colors'][0] ?? 'Default');
        $cartKey = "{$productId}_{$size}_{$color}";

        $cart = $this->getCart();

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
        } else {
            $cart[$cartKey] = [
                'id' => $cartKey,
                'product_id' => $product['id'],
                'title' => $product['title'],
                'slug' => $product['slug'],
                'sku' => $product['sku'],
                'price' => $product['price'],
                'original_price' => $product['original_price'],
                'image' => $product['primary_image'],
                'size' => $size,
                'color' => $color,
                'quantity' => $quantity,
            ];
        }

        Session::put(self::SESSION_KEY, $cart);

        return [
            'success' => true,
            'message' => 'Added to bag successfully',
            'cart' => $this->getSummary()
        ];
    }

    public function update(string $cartKey, int $quantity): array
    {
        $cart = $this->getCart();

        if ($quantity <= 0) {
            unset($cart[$cartKey]);
        } elseif (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = $quantity;
        }

        Session::put(self::SESSION_KEY, $cart);

        return [
            'success' => true,
            'cart' => $this->getSummary()
        ];
    }

    public function remove(string $cartKey): array
    {
        $cart = $this->getCart();

        if (isset($cart[$cartKey])) {
            unset($cart[$cartKey]);
            Session::put(self::SESSION_KEY, $cart);
        }

        return [
            'success' => true,
            'cart' => $this->getSummary()
        ];
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function getSubtotal(): int
    {
        $cart = $this->getCart();
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
        }
        return $subtotal;
    }

    public function getItemCount(): int
    {
        $cart = $this->getCart();
        $count = 0;
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    public function getFreeShippingInfo(): array
    {
        $subtotal = $this->getSubtotal();
        $threshold = self::FREE_SHIPPING_MIN;
        $remaining = max(0, $threshold - $subtotal);
        $progress = min(100, (int)(($subtotal / $threshold) * 100));
        $qualified = $subtotal >= $threshold;

        return [
            'threshold' => $threshold,
            'remaining' => $remaining,
            'progress' => $progress,
            'qualified' => $qualified,
            'message' => $qualified 
                ? '🎉 Congratulations! You have unlocked FREE Delivery all across Bangladesh!' 
                : "Add ৳ " . number_format($remaining) . " more to get FREE Delivery!"
        ];
    }

    public function getSummary(): array
    {
        $items = array_values($this->getCart());
        $subtotal = $this->getSubtotal();
        $itemCount = $this->getItemCount();
        $freeShipping = $this->getFreeShippingInfo();

        return [
            'items' => $items,
            'item_count' => $itemCount,
            'subtotal' => $subtotal,
            'subtotal_formatted' => '৳ ' . number_format($subtotal),
            'free_shipping' => $freeShipping,
        ];
    }
}
