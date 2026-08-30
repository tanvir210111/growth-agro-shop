<?php

namespace App\Http\Controllers;

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

    public function product(string $slug): View
    {
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

    public function orderSuccess(string $orderNumber): View
    {
        $order = $this->checkoutService->getOrder($orderNumber);
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
