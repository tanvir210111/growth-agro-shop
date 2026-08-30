<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\PortfolioCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PortfolioCategoryController extends Controller {
    public function index() {
        $categories = PortfolioCategory::orderBy('id', 'desc')->get(); // ডাটাবেস থেকে ক্যাটাগরি সংগ্রহ
        return view('admin.portfolio_category.index', compact('categories'));
    }

    public function store(Request $request) {
        $request->validate(['name' => 'required|unique:portfolio_categories']);
        $category = new PortfolioCategory();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->save();
        return redirect()->back()->with('success', 'Category Created Successfully.');
    }

    public function destroy($id) {
        PortfolioCategory::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Category Deleted Successfully.');
    }
}