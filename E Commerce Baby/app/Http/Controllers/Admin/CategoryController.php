<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort_order', 'asc')->get();
        return view('admin.pages.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'handle' => 'nullable|string|max:255|unique:categories,handle',
            'image' => 'nullable|image|max:3072',
            'banner_image' => 'nullable|image|max:4096',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $handle = $request->handle ? Str::slug($request->handle) : Str::slug($request->title);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'cat_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/categories'), $filename);
            $imagePath = 'uploads/categories/' . $filename;
        }

        $bannerPath = null;
        if ($request->hasFile('banner_image')) {
            $file = $request->file('banner_image');
            $filename = 'cat_banner_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/categories'), $filename);
            $bannerPath = 'uploads/categories/' . $filename;
        }

        Category::create([
            'title' => $request->title,
            'handle' => $handle,
            'image' => $imagePath ?: 'images/category-placeholder.jpg',
            'banner_image' => $bannerPath ?: 'images/banners/all-collection.jpg',
            'description' => $request->description,
            'sort_order' => (int) ($request->sort_order ?? (Category::count() + 1)),
            'status' => true,
        ]);

        return back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'handle' => 'required|string|max:255|unique:categories,handle,' . $category->id,
            'image' => 'nullable|image|max:3072',
            'banner_image' => 'nullable|image|max:4096',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'cat_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/categories'), $filename);
            $category->image = 'uploads/categories/' . $filename;
        }

        if ($request->hasFile('banner_image')) {
            $file = $request->file('banner_image');
            $filename = 'cat_banner_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/categories'), $filename);
            $category->banner_image = 'uploads/categories/' . $filename;
        }

        $category->title = $request->title;
        $category->handle = Str::slug($request->handle);
        $category->description = $request->description;
        $category->sort_order = (int) ($request->sort_order ?? $category->sort_order);
        $category->status = $request->boolean('status');
        $category->save();

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return back()->with('success', 'Category deleted successfully.');
    }
}
