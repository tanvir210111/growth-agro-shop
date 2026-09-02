<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FrontendController extends Controller
{
    protected ProductService $productService;
    protected CartService $cartService;
    protected CheckoutService $checkoutService;

    public function __construct(
        ProductService $productService,
        CartService $cartService,
        CheckoutService $checkoutService
    ) {
        $this->productService = $productService;
        $this->cartService = $cartService;
        $this->checkoutService = $checkoutService;
    }

    public function home(): View
    {
        $collections = $this->productService->getCollections();
        $newArrivals = $this->productService->getNewArrivals(8);
        $bestsellers = $this->productService->getBestsellers(8);
        $featured = $this->productService->getFeaturedProducts(8);
        $cartSummary = $this->cartService->getSummary();

        return view('pages.home', compact(
            'collections',
            'newArrivals',
            'bestsellers',
            'featured',
            'cartSummary'
        ));
    }

    public function shop(Request $request): View
    {
        $collections = $this->productService->getCollections();
        $handle = $request->get('category', 'all-collection');
        $sort = $request->get('sort', 'newest');
        $minPrice = $request->filled('min_price') ? (int)$request->get('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (int)$request->get('max_price') : null;

        $currentCollection = $this->productService->getCollectionByHandle($handle) ?? [
            'id' => 'all-collection',
            'title' => 'All Collection',
            'handle' => 'all-collection',
            'description' => 'Explore our complete selection of baby & toddler clothing sets.'
        ];

        $products = $this->productService->getProductsByCollection($handle, $sort, $minPrice, $maxPrice);
        $cartSummary = $this->cartService->getSummary();

        return view('pages.shop', compact(
            'collections',
            'currentCollection',
            'products',
            'sort',
            'minPrice',
            'maxPrice',
            'cartSummary'
        ));
    }

    public function collection(string $handle, Request $request): View
    {
        $collections = $this->productService->getCollections();
        $currentCollection = $this->productService->getCollectionByHandle($handle);

        if (!$currentCollection) {
            abort(404, 'Collection not found');
        }

        $sort = $request->get('sort', 'newest');
        $minPrice = $request->filled('min_price') ? (int)$request->get('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (int)$request->get('max_price') : null;

        $products = $this->productService->getProductsByCollection($handle, $sort, $minPrice, $maxPrice);
        $cartSummary = $this->cartService->getSummary();

        return view('pages.shop', compact(
            'collections',
            'currentCollection',
            'products',
            'sort',
            'minPrice',
            'maxPrice',
            'cartSummary'
        ));
    }

    public function product(string $slug, Request $request)
    {
        // 1. Check if a Landing Page exists with this slug
        $landingPage = LandingPage::where('slug', $slug)->first();

        if (!$landingPage && $slug === 'chicken-booster') {
            $landingPage = LandingPage::firstOrCreate(
                ['slug' => 'chicken-booster'],
                [
                    'name'            => 'Chicken Booster (চিকেন বুস্টার)',
                    'slug'            => 'chicken-booster',
                    'status'          => 'published',
                    'theme'           => 'chicken-booster',
                    'product_id'      => 'chicken-booster',
                    'product_name'    => 'Chicken Booster (Broiler & Layer Booster)',
                    'title'           => 'চিকেন বুস্টার (Broiler & Layer Booster) — Growth Agro | রোগমুক্ত, দ্রুত মোটাতাজা ও সুস্থ মুরগি',
                    'meta_title'      => 'চিকেন বুস্টার (Broiler & Layer Booster) — Growth Agro',
                    'meta_description'=> 'মুরগির দ্রুত ওজন বৃদ্ধি, এফসিআর (FCR) উন্নয়ন ও সুস্থতার সম্পূর্ণ প্রাকৃতিক খাদ্য সম্পূরক। সারাদেশের খামারিদের নির্ভরযোগ্য সমাধান। ক্যাশ অন ডেলিভারি।',
                    'content'         => LandingPage::getDefaultMasterContent(),
                    'delivery_config' => LandingPage::getDefaultDeliveryConfig(),
                    'theme_config'    => LandingPage::getDefaultThemeConfig(),
                    'seo_config'      => [
                        'og_title'       => 'চিকেন বুস্টার (Broiler & Layer Booster) — Growth Agro',
                        'og_description' => 'রোগমুক্ত, দ্রুত মোটাতাজা ও সুস্থ মুরগি পেতে চান? জানুন প্রাকৃতিক সমাধান।',
                        'og_image'       => '/assets/images/chicken-booster-product.webp',
                        'canonical_url'  => 'https://growthagro.shop/product/chicken-booster',
                        'robots'         => 'index, follow',
                    ],
                    'section_order'   => LandingPage::getDefaultSectionOrder(),
                    'published_at'    => \Carbon\Carbon::now(),
                ]
            );
        }

        if ($landingPage) {
            // Allow if published or in preview mode
            $isPreview = $request->has('preview') || $request->query('preview') === 'true';
            if ($landingPage->status === 'published' || $isPreview) {
                $content = $landingPage->content ?: LandingPage::getDefaultMasterContent();
                $deliveryConfig = $landingPage->delivery_config ?: LandingPage::getDefaultDeliveryConfig();
                $themeConfig = $landingPage->theme_config ?: LandingPage::getDefaultThemeConfig();
                $sectionOrder = $landingPage->section_order ?: LandingPage::getDefaultSectionOrder();

                return response()
                    ->view('pages.landing-page', compact('landingPage', 'content', 'deliveryConfig', 'themeConfig', 'sectionOrder'))
                    ->header('X-Frame-Options', 'SAMEORIGIN')
                    ->header('Content-Security-Policy', "frame-ancestors 'self'");
            }

            // Draft or Unpublished without preview
            abort(404, 'Landing page is currently not published.');
        }

        // 2. Standard Storefront Product Fallback
        $product = $this->productService->findBySlug($slug);
        if (!$product) {
            abort(404, 'Product not found');
        }

        $relatedProducts = array_filter(
            $this->productService->getAllProducts(),
            fn($p) => $p['id'] !== $product['id'] && ($p['category_handle'] === $product['category_handle'] || $p['is_featured'])
        );
        $relatedProducts = array_slice(array_values($relatedProducts), 0, 4);
        $cartSummary = $this->cartService->getSummary();

        return view('pages.product-detail', compact('product', 'relatedProducts', 'cartSummary'));
    }

    public function cart(): View
    {
        $cartSummary = $this->cartService->getSummary();
        $recommendedProducts = $this->productService->getBestsellers(4);

        return view('pages.cart', compact('cartSummary', 'recommendedProducts'));
    }

    public function cartAdd(Request $request): JsonResponse
    {
        $productId = (int) $request->input('product_id');
        $size = $request->input('size');
        $color = $request->input('color');
        $quantity = max(1, (int) $request->input('quantity', 1));

        $result = $this->cartService->add($productId, $size, $color, $quantity);

        return response()->json($result);
    }

    public function cartUpdate(Request $request): JsonResponse
    {
        $cartKey = $request->input('cart_key');
        $quantity = (int) $request->input('quantity', 1);

        $result = $this->cartService->update($cartKey, $quantity);

        return response()->json($result);
    }

    public function cartRemove(Request $request): JsonResponse
    {
        $cartKey = $request->input('cart_key');
        $result = $this->cartService->remove($cartKey);

        return response()->json($result);
    }

    public function cartJson(): JsonResponse
    {
        return response()->json($this->cartService->getSummary());
    }

    public function quickView(string $slug): JsonResponse
    {
        $product = $this->productService->findBySlug($slug);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json(['success' => true, 'product' => $product]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $results = $this->productService->search($query, 8);

        return response()->json([
            'query' => $query,
            'count' => count($results),
            'results' => $results
        ]);
    }

    public function checkout(Request $request): View
    {
        $cartSummary = $this->cartService->getSummary();
        $directProductId = $request->query('direct_product_id');
        $directProduct = null;

        if ($directProductId) {
            $prod = $this->productService->findById((int)$directProductId);
            if ($prod) {
                $directProduct = [
                    'id' => $prod['id'],
                    'title' => $prod['title'],
                    'price' => $prod['price'],
                    'size' => $request->query('size', $prod['sizes'][0] ?? 'Standard'),
                    'quantity' => max(1, (int)$request->query('quantity', 1)),
                    'image' => $prod['primary_image'],
                ];
            }
        }

        return view('pages.checkout', compact('cartSummary', 'directProduct'));
    }

    public function processCheckout(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
            'delivery_area' => 'required|in:inside_dhaka,outside_dhaka',
            'payment_method' => 'nullable|string',
            'order_notes' => 'nullable|string|max:500',
        ]);

        $directProduct = null;
        if ($request->filled('direct_product_id')) {
            $prod = $this->productService->findById((int)$request->input('direct_product_id'));
            if ($prod) {
                $directProduct = [
                    'id' => $prod['id'],
                    'title' => $prod['title'],
                    'price' => $prod['price'],
                    'size' => $request->input('direct_size', $prod['sizes'][0] ?? 'Standard'),
                    'quantity' => max(1, (int)$request->input('direct_quantity', 1)),
                    'image' => $prod['primary_image'],
                ];
            }
        }

        $result = $this->checkoutService->placeOrder($validated, $directProduct);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->route('order.success', ['orderNumber' => $result['order_number']]);
    }

    public function orderSuccess(string $orderNumber)
    {
        $dbOrder = \App\Models\Order::where('invoice_no', $orderNumber)->orWhere('id', $orderNumber)->with('items')->first();
        $order = null;

        if ($dbOrder) {
            $order = [
                'order_number'        => $dbOrder->invoice_no ?: ('ORD-' . $dbOrder->id),
                'customer_name'       => $dbOrder->customer_name,
                'customer_phone'      => $dbOrder->customer_phone,
                'customer_address'    => $dbOrder->customer_address,
                'delivery_area_label' => ($dbOrder->city_type === 'inside_dhaka' || $dbOrder->city_type === 'inside') ? 'ঢাকার ভিতরে (Inside Dhaka)' : 'ঢাকার বাইরে (Outside Dhaka)',
                'items'               => $dbOrder->items->map(fn($it) => [
                    'title'    => $it->product_name,
                    'price'    => (float)$it->price,
                    'quantity' => (int)$it->quantity,
                    'size'     => $it->size ?: 'Standard'
                ])->toArray(),
                'subtotal'            => (float)$dbOrder->subtotal,
                'shipping'            => (float)$dbOrder->delivery_charge,
                'total'               => (float)$dbOrder->total_amount,
                'source_type'         => $dbOrder->source_type,
                'landing_page'        => $dbOrder->landing_page,
                'created_at'          => $dbOrder->created_at ? $dbOrder->created_at->format('d M, Y h:i A') : date('d M, Y h:i A'),
            ];
        } else {
            $order = $this->checkoutService->getOrder($orderNumber);
        }

        if (!$order) {
            abort(404, 'Order not found.');
        }

        // Backward compatibility: If a Landing Page order is accessed via /order/success/{orderNumber},
        // redirect to its canonical source-matched URL: /product/{slug}/success/{orderNumber}
        $rawSource = strtolower(trim($dbOrder->source_type ?? ($order['source_type'] ?? '')));
        $rawLanding = $dbOrder->landing_page ?? ($order['landing_page'] ?? '');
        $landingSlug = $this->normalizeLandingSlug($rawLanding);

        $isLandingPage = in_array($rawSource, ['landing_page', 'landing', 'landing-page', 'landing page'])
            || (!empty($landingSlug) && $rawSource !== 'storefront');

        if ($isLandingPage && $landingSlug) {
            return redirect()->route('landing.order.success', [
                'slug' => $landingSlug,
                'orderNumber' => $order['order_number']
            ]);
        }

        // Main website order: render pages.order-success (extends layouts.app)
        return view('pages.order-success', compact('order'));
    }

    /**
     * Just-in-time fallback: retrieve an order from Node.js SQLite via internal API,
     * sync it to the Laravel DB, and return the resulting Order model.
     * Returns null if the order cannot be found or synced.
     * Used when the async Node→Laravel sync bridge failed at order-creation time.
     */
    private function syncOrderFromNode(string $orderNumber, \App\Models\LandingPage $landingPage): ?\App\Models\Order
    {
        try {
            $nodeHost = env('NODE_HOST', '127.0.0.1');
            $nodePort = env('NODE_PORT', 3000);
            $secret   = env('INTERNAL_API_SECRET', 'baby-fashion-internal-2024-secret');

            $response = \Illuminate\Support\Facades\Http::timeout(4)
                ->withHeaders([
                    'Accept'            => 'application/json',
                    'X-Internal-Secret' => $secret,
                ])
                ->get("http://{$nodeHost}:{$nodePort}/api/internal/order-lookup/{$orderNumber}");

            if (!$response->ok()) {
                return null;
            }

            $data = $response->json();
            if (empty($data['success']) || empty($data['order'])) {
                return null;
            }

            $nodeOrder = $data['order'];
            $invoiceNo = $nodeOrder['order_number'] ?? $orderNumber;

            // Verify it is a landing-page order from this slug
            $nodeLanding = $nodeOrder['landing_page'] ?? '';
            $nodeSource  = strtolower($nodeOrder['source'] ?? '');
            if (!in_array($nodeSource, ['landing_page', 'landing', 'landing-page', 'landing page']) && empty($nodeLanding)) {
                return null;
            }

            // Sync to Laravel DB (idempotent)
            $syncPayload = [
                'order_number'    => $invoiceNo,
                'customer_name'   => $nodeOrder['customer_name'] ?? 'Customer',
                'customer_phone'  => $nodeOrder['phone'] ?? $nodeOrder['customer_phone'] ?? '',
                'customer_address'=> $nodeOrder['address'] ?? '-',
                'delivery_zone'   => $nodeOrder['delivery_zone'] ?? 'inside',
                'delivery_charge' => $nodeOrder['delivery_charge'] ?? 0,
                'subtotal'        => $nodeOrder['subtotal'] ?? 0,
                'total'           => $nodeOrder['total'] ?? 0,
                'payment_method'  => $nodeOrder['payment_method'] ?? 'Cash on Delivery',
                'product_name'    => $nodeOrder['product'] ?? $landingPage->product_name ?? 'Product',
                'variant_name'    => $nodeOrder['variant'] ?? 'Standard',
                'quantity'        => $nodeOrder['quantity'] ?? 1,
                'unit_price'      => $nodeOrder['subtotal'] ?? 0,
                'landing_page'    => $nodeLanding ?: ('/product/' . $landingPage->slug),
                'items'           => [],
            ];

            $syncResponse = \Illuminate\Support\Facades\Http::timeout(4)
                ->withHeaders([
                    'Content-Type'     => 'application/json',
                    'Accept'           => 'application/json',
                    'X-Internal-Secret' => $secret,
                ])
                ->post(url('/api/internal/sync-landing-order'), $syncPayload);

            if (!$syncResponse->ok()) {
                \Illuminate\Support\Facades\Log::warning("[LandingSuccess] JIT sync failed for {$orderNumber}: " . $syncResponse->status());
            }

            // Re-query Laravel DB (should now exist after sync)
            return \App\Models\Order::where('invoice_no', $invoiceNo)
                ->with('items')
                ->first();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("[LandingSuccess] JIT order fallback failed for {$orderNumber}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Safely normalize a landing page slug or URL path to a clean slug string.
     */
    private function normalizeLandingSlug(?string $val): ?string
    {
        if ($val === null || $val === '') {
            return null;
        }

        $raw = trim($val);
        $path = parse_url($raw, PHP_URL_PATH) ?: $raw;
        $trimmed = trim($path, '/');

        // Ignore non-landing storefront routes
        if (in_array(strtolower($trimmed), ['', 'checkout', 'cart', 'shop', 'order', 'orders', 'admin'])) {
            return null;
        }

        // Remove leading 'product/' or 'products/'
        $cleaned = preg_replace('#^(product|products)/#i', '', $trimmed);

        // Strip any trailing segments like /checkout, /success, etc.
        $segments = explode('/', $cleaned);
        $slug = strtolower(trim($segments[0] ?? ''));

        if (in_array($slug, ['', 'checkout', 'cart', 'shop', 'order', 'orders', 'admin'])) {
            return null;
        }

        return !empty($slug) ? $slug : null;
    }

    public function landingOrderSuccess(string $slug, string $orderNumber)
    {
        $cleanSlug = $this->normalizeLandingSlug($slug);
        if (!$cleanSlug) {
            abort(404, 'Invalid landing page slug.');
        }

        // 1. Resolve Landing Page Model
        $landingPage = \App\Models\LandingPage::where('slug', $cleanSlug)
            ->orWhere('slug', '/product/' . $cleanSlug)
            ->orWhere('slug', '/products/' . $cleanSlug)
            ->orWhereRaw('LOWER(slug) = ?', [$cleanSlug])
            ->first();

        if (!$landingPage && $cleanSlug === 'chicken-booster') {
            $landingPage = \App\Models\LandingPage::firstOrCreate(
                ['slug' => 'chicken-booster'],
                [
                    'name'            => 'Chicken Booster (চিকেন বুস্টার)',
                    'theme'           => 'chicken-booster',
                    'status'          => 'published',
                    'product_id'      => 'chicken-booster',
                    'product_name'    => 'Chicken Booster (চিকেন বুস্টার)',
                    'title'           => 'চিকেন বুস্টার (Broiler & Layer Booster) — Growth Agro',
                    'content'         => \App\Models\LandingPage::getDefaultMasterContent(),
                    'delivery_config' => \App\Models\LandingPage::getDefaultDeliveryConfig(),
                    'theme_config'    => \App\Models\LandingPage::getDefaultThemeConfig(),
                    'section_order'   => \App\Models\LandingPage::getDefaultSectionOrder(),
                    'published_at'    => \Carbon\Carbon::now(),
                ]
            );
        }

        if (!$landingPage) {
            abort(404, 'Landing page not found.');
        }

        // 2. Resolve Order (Case-Insensitive & ID / Invoice lookup)
        $cleanOrderNumber = trim($orderNumber);
        $dbOrder = \App\Models\Order::where('invoice_no', $cleanOrderNumber)
            ->orWhere('id', $cleanOrderNumber)
            ->orWhereRaw('LOWER(invoice_no) = ?', [strtolower($cleanOrderNumber)])
            ->with('items')
            ->first();

        // 2b. Fallback: If not in Laravel DB, attempt just-in-time retrieval from Node.js SQLite
        //     (handles the case where the Node→Laravel sync bridge failed or timed out)
        if (!$dbOrder) {
            $dbOrder = $this->syncOrderFromNode($cleanOrderNumber, $landingPage);
        }

        if (!$dbOrder) {
            abort(404, 'Order not found.');
        }

        // 3. Verify this order is a landing-page order
        $rawSource = strtolower(trim($dbOrder->source_type ?? ''));
        $rawLanding = $dbOrder->landing_page ?? '';
        $rawNote = strtolower(trim($dbOrder->note ?? ''));

        $isLandingPageOrder = in_array($rawSource, ['landing_page', 'landing', 'landing-page', 'landing page'])
            || !empty($rawLanding)
            || str_starts_with($rawNote, 'landing page order');

        if (!$isLandingPageOrder) {
            // If main website order is mistakenly opened here, redirect to canonical main success page
            return redirect()->route('order.success', ['orderNumber' => $dbOrder->invoice_no ?: $dbOrder->id]);
        }

        // 4. Verify order belongs to the requested landing page
        $orderLandingSlug = $this->normalizeLandingSlug($rawLanding);

        if (!$orderLandingSlug) {
            $firstItemName = strtolower($dbOrder->items->first()->product_name ?? '');
            $lpProductName = strtolower($landingPage->product_name ?? '');
            $lpName = strtolower($landingPage->name ?? '');

            if (
                str_contains($rawNote, $cleanSlug) ||
                ($firstItemName && (
                    str_contains($firstItemName, $cleanSlug) ||
                    ($lpProductName && str_contains($firstItemName, $lpProductName)) ||
                    ($lpName && str_contains($firstItemName, $lpName))
                )) ||
                ($cleanSlug === 'chicken-booster' && (str_contains($firstItemName, 'chicken') || str_contains($firstItemName, 'booster')))
            ) {
                $orderLandingSlug = $cleanSlug;
            }
        }

        // Security check: If order belongs to a different landing page, do not expose it
        if ($orderLandingSlug && $orderLandingSlug !== $cleanSlug) {
            abort(404, 'Order does not belong to this landing page.');
        }

        if (!$orderLandingSlug && $cleanSlug !== 'chicken-booster') {
            abort(404, 'Order does not belong to this landing page.');
        }

        // 5. Build order payload for landing-success view
        $order = [
            'order_number'        => $dbOrder->invoice_no ?: ('ORD-' . $dbOrder->id),
            'customer_name'       => $dbOrder->customer_name,
            'customer_phone'      => $dbOrder->customer_phone,
            'customer_address'    => $dbOrder->customer_address,
            'delivery_area_label' => ($dbOrder->city_type === 'inside_dhaka' || $dbOrder->city_type === 'inside') ? 'ঢাকার ভিতরে (Inside Dhaka)' : 'ঢাকার বাইরে (Outside Dhaka)',
            'items'               => $dbOrder->items->map(fn($it) => [
                'title'    => $it->product_name,
                'price'    => (float)$it->price,
                'quantity' => (int)$it->quantity,
                'size'     => $it->size ?: 'Standard'
            ])->toArray(),
            'subtotal'            => (float)$dbOrder->subtotal,
            'shipping'            => (float)$dbOrder->delivery_charge,
            'total'               => (float)$dbOrder->total_amount,
            'source_type'         => $dbOrder->source_type,
            'landing_page'        => $dbOrder->landing_page,
            'created_at'          => $dbOrder->created_at ? $dbOrder->created_at->format('d M, Y h:i A') : date('d M, Y h:i A'),
        ];

        $landingPageUrl = '/product/' . $cleanSlug;
        $landingPageTitle = $landingPage->product_name ?: ($landingPage->title ?: $landingPage->name);

        return view('pages.landing-success', compact('order', 'landingPage', 'landingPageUrl', 'landingPageTitle'));
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function policy(string $type = 'return'): View
    {
        return view('pages.policy', compact('type'));
    }
}
