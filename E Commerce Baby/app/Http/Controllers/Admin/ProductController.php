<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');

        $query = Product::with('category')->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::all();

        return view('admin.pages.products.index', compact('products', 'categories', 'search', 'categoryId'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.pages.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|unique:products,sku',
            'featured_image' => 'nullable|image|max:3072',
            'hover_image' => 'nullable|image|max:3072',
            'gallery_images.*' => 'nullable|image|max:3072',
        ]);

        $category = Category::findOrFail($request->category_id);
        $slug = Str::slug($request->title);
        $count = Product::where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $featuredPath = null;
        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $filename = 'prod_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $featuredPath = 'uploads/products/' . $filename;
        }

        $hoverPath = null;
        if ($request->hasFile('hover_image')) {
            $file = $request->file('hover_image');
            $filename = 'prod_hover_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $hoverPath = 'uploads/products/' . $filename;
        }

        $galleryPaths = [];
        if ($featuredPath) $galleryPaths[] = $featuredPath;
        if ($hoverPath) $galleryPaths[] = $hoverPath;

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $gFile) {
                $filename = 'prod_g_' . time() . '_' . uniqid() . '.' . $gFile->getClientOriginalExtension();
                $gFile->move(public_path('uploads/products'), $filename);
                $galleryPaths[] = 'uploads/products/' . $filename;
            }
        }

        $sizes = $request->sizes ? array_filter(array_map('trim', explode(',', $request->sizes))) : ['Standard'];

        Product::create([
            'title' => $request->title,
            'slug' => $slug,
            'sku' => $request->sku ?: 'BFB-' . strtoupper(substr(uniqid(), -5)),
            'category_id' => $category->id,
            'category_handle' => $category->handle,
            'regular_price' => (float) $request->regular_price,
            'sale_price' => (float) $request->sale_price,
            'cost_price' => (float) ($request->cost_price ?? 0),
            'stock' => (int) $request->stock,
            'featured_image' => $featuredPath ?: 'images/product-placeholder.jpg',
            'hover_image' => $hoverPath ?: $featuredPath,
            'gallery_images' => $galleryPaths,
            'sizes' => array_values($sizes),
            'short_description' => $request->short_description,
            'description' => $request->description,
            'is_featured' => $request->boolean('is_featured'),
            'is_new_arrival' => $request->boolean('is_new_arrival'),
            'is_bestseller' => $request->boolean('is_bestseller'),
            'is_clearance' => $request->boolean('is_clearance'),
            'status' => $request->boolean('status', true),
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        return view('admin.pages.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'featured_image' => 'nullable|image|max:3072',
            'hover_image' => 'nullable|image|max:3072',
        ]);

        $category = Category::findOrFail($request->category_id);

        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $filename = 'prod_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $product->featured_image = 'uploads/products/' . $filename;
        }

        if ($request->hasFile('hover_image')) {
            $file = $request->file('hover_image');
            $filename = 'prod_hover_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $product->hover_image = 'uploads/products/' . $filename;
        }

        if ($request->sizes) {
            $product->sizes = array_values(array_filter(array_map('trim', explode(',', $request->sizes))));
        }

        $product->title = $request->title;
        $product->sku = $request->sku ?: $product->sku;
        $product->category_id = $category->id;
        $product->category_handle = $category->handle;
        $product->regular_price = (float) $request->regular_price;
        $product->sale_price = (float) $request->sale_price;
        $product->cost_price = (float) ($request->cost_price ?? $product->cost_price);
        $product->stock = (int) $request->stock;
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->is_featured = $request->boolean('is_featured');
        $product->is_new_arrival = $request->boolean('is_new_arrival');
        $product->is_bestseller = $request->boolean('is_bestseller');
        $product->is_clearance = $request->boolean('is_clearance');
        $product->status = $request->boolean('status');

        $product->save();

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->status = !$product->status;
        $product->save();

        return back()->with('success', "Product status changed to " . ($product->status ? 'Active' : 'Inactive'));
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
