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
        $order = $this->checkoutService->getOrder($orderNumber);
        if (!$order) {
            $dbOrder = \App\Models\Order::where('invoice_no', $orderNumber)->orWhere('id', $orderNumber)->with('items')->first();
            if ($dbOrder) {
                $order = [
                    'order_number'        => $dbOrder->invoice_no ?: ('ORD-' . $dbOrder->id),
                    'customer_name'       => $dbOrder->customer_name,
                    'customer_phone'      => $dbOrder->customer_phone,
                    'customer_address'    => $dbOrder->customer_address,
                    'delivery_area_label' => $dbOrder->city_type === 'inside_dhaka' ? 'Inside Dhaka' : 'Outside Dhaka',
                    'items'               => $dbOrder->items->map(fn($it) => [
                        'title'    => $it->product_name,
                        'price'    => $it->price,
                        'quantity' => $it->quantity,
                        'size'     => $it->size
                    ])->toArray(),
                    'subtotal'            => (float)$dbOrder->subtotal,
                    'shipping'            => (float)$dbOrder->delivery_charge,
                    'total'               => (float)$dbOrder->total_amount,
                ];
            }
        }
        if (!$order) {
            return redirect()->route('home')->with('info', 'No active order found.');
        }

        return view('pages.order-success', compact('order'));
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
