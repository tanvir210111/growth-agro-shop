<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ModulePageController extends Controller
{
    /**
     * Generic renderer for all Captain Crown module sub-pages.
     */
    public function renderPage($module, $page = 'manage', Request $request)
    {
        $orders = Order::with('items')->latest()->paginate(10);
        $products = Product::with('category')->latest()->paginate(10);
        $categories = Category::withCount('products')->get();

        // Title formatting
        $moduleTitle = ucwords(str_replace('-', ' ', $module));
        $pageTitle = ucwords(str_replace('-', ' ', $page));

        return view('admin.pages.generic_module', compact(
            'module',
            'page',
            'moduleTitle',
            'pageTitle',
            'orders',
            'products',
            'categories'
        ));
    }
}
