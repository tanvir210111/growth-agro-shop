<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InternalSyncController extends Controller
{
    protected TrackingService $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Server-to-server bridge: Sync a landing page order created in Node.js to Laravel.
     * Attaches authoritative attribution context, creates order & order items, and records purchase event.
     */
    public function syncLandingOrder(Request $request): JsonResponse
    {
        // 1. Verify Internal Secret
        $expectedSecret = env('INTERNAL_API_SECRET', 'baby-fashion-internal-2024-secret');
        $incomingSecret = $request->header('X-Internal-Secret');

        if (!$incomingSecret || !hash_equals($expectedSecret, $incomingSecret)) {
            return response()->json(['success' => false, 'error' => 'Forbidden: Invalid internal secret.'], 403);
        }

        $orderNumber = trim($request->input('order_number', ''));
        if (empty($orderNumber)) {
            return response()->json(['success' => false, 'error' => 'Missing order_number.'], 400);
        }

        // 2. Idempotency Check: Do not duplicate if order already synced
        $existingOrder = Order::where('invoice_no', $orderNumber)->first();
        if ($existingOrder) {
            $incomingPhone = trim((string)($request->input('customer_phone') ?: $request->input('phone') ?: ''));
            $incomingName = trim((string)($request->input('customer_name') ?: $request->input('name') ?: ''));
            $incomingAddr = trim((string)($request->input('customer_address') ?: $request->input('address') ?: $request->input('shipping_address') ?: ''));

            $updates = [];
            if (!empty($incomingPhone) && (empty($existingOrder->customer_phone) || $existingOrder->customer_phone === '-')) {
                $updates['customer_phone'] = $incomingPhone;
            }
            if (!empty($incomingName) && ($existingOrder->customer_name === 'Landing Customer' || empty($existingOrder->customer_name))) {
                $updates['customer_name'] = $incomingName;
            }
            if (!empty($incomingAddr) && (empty($existingOrder->customer_address) || $existingOrder->customer_address === '-')) {
                $updates['customer_address'] = $incomingAddr;
            }
            if (!empty($updates)) {
                $existingOrder->update($updates);
            }

            return response()->json([
                'success' => true,
                'is_duplicate' => true,
                'order_id' => $existingOrder->id,
                'invoice_no' => $existingOrder->invoice_no,
            ], 200);
        }

        DB::beginTransaction();
        try {
            $customerName = trim((string)($request->input('customer_name') ?: $request->input('name') ?: 'Customer'));
            $phone = trim((string)($request->input('customer_phone') ?: $request->input('phone') ?: ''));
            $address = trim((string)($request->input('customer_address') ?: $request->input('address') ?: $request->input('shipping_address') ?: '-'));
            $deliveryZone = $request->input('delivery_zone', 'inside');
            $cityType = ($deliveryZone === 'inside' || $deliveryZone === 'inside_dhaka') ? 'inside_dhaka' : 'outside_dhaka';
            $shipping = (float) $request->input('delivery_charge', 0);
            $subtotal = (float) $request->input('subtotal', 0);
            $total = (float) $request->input('total', 0);
            $paymentMethod = $request->input('payment_method', 'Cash on Delivery');
            $landingPage = $request->input('landing_page', '/products/chicken-booster/');
            $productName = $request->input('product_name', 'Chicken Booster');
            $variantName = $request->input('variant_name', 'Standard');
            $quantity = (int) $request->input('quantity', 1);
            $unitPrice = (float) $request->input('unit_price', $subtotal);

            // 3. Create Laravel Order
            $order = Order::create([
                'invoice_no' => $orderNumber,
                'customer_name' => $customerName,
                'customer_phone' => $phone,
                'customer_address' => $address,
                'city_type' => $cityType,
                'delivery_charge' => $shipping,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total_amount' => $total,
                'payment_method' => $paymentMethod,
                'status' => 'pending',
                'is_new' => true,
                'note' => 'Landing Page Order: ' . $productName,
                'source_type' => 'LANDING',
                'landing_page' => $landingPage,
            ]);

            // 4. Attach Unified Tracking Attribution
            try {
                $this->trackingService->attachOrderAttribution($order, $request);
            } catch (\Throwable $te) {
                Log::warning('[InternalSync] Failed to attach order attribution: ' . $te->getMessage());
            }

            // 5. Create Order Item(s)
            $rawItems = $request->input('items');
            if (is_array($rawItems) && count($rawItems) > 0) {
                foreach ($rawItems as $it) {
                    $itQty = max(1, (int) ($it['quantity'] ?? 1));
                    $itPrice = (float) ($it['price'] ?? $unitPrice);
                    $itTotal = (float) ($it['total'] ?? ($itPrice * $itQty));
                    $itName = $it['name'] ?? ($productName . ' (' . ($it['variant_name'] ?? $it['variant_id'] ?? 'Standard') . ')');
                    $itSize = $it['variant_name'] ?? $it['variant_id'] ?? ($variantName ?: 'Standard');
                    $itImg = $it['image'] ?? '';

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => null,
                        'product_name' => $itName,
                        'product_image' => $itImg,
                        'size' => $itSize,
                        'price' => $itPrice,
                        'quantity' => $itQty,
                        'total' => $itTotal,
                    ]);
                }
            } else {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => null,
                    'product_name' => $productName . ($variantName ? " ({$variantName})" : ''),
                    'product_image' => '',
                    'size' => $variantName ?: 'Standard',
                    'price' => $unitPrice,
                    'quantity' => $quantity,
                    'total' => $subtotal,
                ]);
            }

            // 6. Record Deduplicated Purchase Event in Tracking Stream
            try {
                $this->trackingService->trackEvent(
                    eventName: 'purchase',
                    entityType: 'order',
                    entityId: $orderNumber,
                    properties: [
                        'items_count' => $quantity,
                        'currency' => 'BDT',
                        'landing_page' => $landingPage,
                        'source' => 'landing_page_bridge'
                    ],
                    eventValue: $total,
                    request: $request
                );
            } catch (\Throwable $ee) {
                Log::warning('[InternalSync] Failed to record purchase event: ' . $ee->getMessage());
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'invoice_no' => $order->invoice_no,
                'visitor_id' => $order->visitor_id,
                'session_id' => $order->session_id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[InternalSync] Failed to sync order: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $request->except(['customer_phone', 'customer_address'])
            ]);
            return response()->json(['success' => false, 'error' => 'Internal server error while syncing order.'], 500);
        }
    }

    /**
     * Internal endpoint: Fetch authoritative Landing Page pricing and delivery configuration by slug.
     */
    public function getLandingPageConfig(Request $request, string $slug): JsonResponse
    {
        $expectedSecret = env('INTERNAL_API_SECRET', 'baby-fashion-internal-2024-secret');
        $incomingSecret = $request->header('X-Internal-Secret');

        if (!$incomingSecret || !hash_equals($expectedSecret, $incomingSecret)) {
            return response()->json(['success' => false, 'error' => 'Forbidden: Invalid internal secret.'], 403);
        }

        $cleanSlug = trim($slug);
        $landingPage = \App\Models\LandingPage::where('slug', $cleanSlug)->first();

        if (!$landingPage) {
            return response()->json(['success' => false, 'error' => 'Landing page not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'landing_page' => [
                'id' => $landingPage->id,
                'name' => $landingPage->name,
                'slug' => $landingPage->slug,
                'status' => $landingPage->status,
                'product_id' => $landingPage->product_id,
                'product_name' => $landingPage->product_name,
                'content' => $landingPage->content,
                'delivery_config' => $landingPage->delivery_config,
            ]
        ]);
    }
}
