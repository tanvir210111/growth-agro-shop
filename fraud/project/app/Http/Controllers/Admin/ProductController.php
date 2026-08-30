<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    // ১. ডাটাটেবল (আগের মতোই)
    public function datatables()
    {
        $datas = Product::orderBy('id', 'desc');
        return DataTables::of($datas)
            ->editColumn('photo', function(Product $data) {
                $photo = $data->photo ? asset('assets/images/products/'.$data->photo) : asset('assets/images/noimage.png');
                return '<img src="' . $photo . '" width="80" class="rounded shadow-sm">';
            })
            ->editColumn('price', function(Product $data) {
                return '৳ ' . number_format($data->price, 2);
            })
            ->addColumn('action', function(Product $data) {
                return '<div class="btn-group btn-group-sm">
                            <a href="' . route('admin.products.edit', $data->id) . '" class="btn btn-info shadow-sm"><i class="fas fa-edit"></i> এডিট</a>
                            <button type="button" data-href="' . route('admin.products.destroy', $data->id) . '" class="btn btn-danger shadow-sm ml-1" data-toggle="modal" data-target="#confirm-delete"><i class="fas fa-trash"></i> ডিলিট</button>
                        </div>';
            })
            ->rawColumns(['photo', 'action'])
            ->make(true);
    }

    public function index() {
        return view('admin.product.index');
    }

    public function create() {
        return view('admin.product.create');
    }

    // ২. স্টোর মেথড (SEO লজিক সহ)
    public function store(Request $request) {
        $request->validate([
            'name'  => 'required|unique:products,name',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'required|numeric',
        ]);

        $input = $request->all();

        // ইমেজ আপলোড
        if ($file = $request->file('photo')) {
            $name = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $file->move(public_path('assets/images/products'), $name);
            $input['photo'] = $name;
        }

        // --- SEO লজিক (নতুন প্রোডাক্টের জন্য) ---
        // যদি meta_title ফাঁকা থাকে, প্রোডাক্টের নাম ব্যবহার করো
        $input['meta_title'] = !empty($request->meta_title) ? $request->meta_title : $request->name;

        // যদি meta_keyword ফাঁকা থাকে, নামগুলোকে কমা দিয়ে আলাদা করে কিওয়ার্ড বানাও
        $input['meta_keyword'] = !empty($request->meta_keyword) ? $request->meta_keyword : str_replace(' ', ', ', $request->name);

        // যদি meta_description ফাঁকা থাকে, details থেকে ১৬০ ক্যারেক্টার নাও
        if (empty($request->meta_description)) {
            // HTML ট্যাগ রিমুভ করে ক্লিন টেক্সট নেওয়া হচ্ছে
            $clean_text = strip_tags($request->description . ' ' . $request->details);
            $input['meta_description'] = Str::limit($clean_text, 160);
        } else {
            $input['meta_description'] = $request->meta_description;
        }

        $input['slug'] = Str::slug($request->name);

        Product::create($input);

        return redirect()->route('admin.products.index')->with('success', 'থিম সফলভাবে যুক্ত হয়েছে।');
    }

    public function edit($id) {
        $data = Product::findOrFail($id);
        return view('admin.product.edit', compact('data'));
    }

    // ৩. আপডেট মেথড (SEO লজিক সহ)
    public function update(Request $request, $id) {
        $data = Product::findOrFail($id);

        $request->validate([
            'name'  => 'required|unique:products,name,' . $id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'required|numeric',
        ]);

        $input = $request->all();

        if ($file = $request->file('photo')) {
            $oldPath = public_path('assets/images/products/' . $data->photo);
            if (File::exists($oldPath)) { File::delete($oldPath); }
            $name = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $file->move(public_path('assets/images/products'), $name);
            $input['photo'] = $name;
        }

        // --- SEO লজিক (আপডেটের সময়) ---
        $input['meta_title'] = !empty($request->meta_title) ? $request->meta_title : $request->name;
        
        $input['meta_keyword'] = !empty($request->meta_keyword) ? $request->meta_keyword : str_replace(' ', ', ', $request->name);

        if (empty($request->meta_description)) {
            $clean_text = strip_tags($request->description . ' ' . $request->details);
            $input['meta_description'] = Str::limit($clean_text, 160);
        } else {
            $input['meta_description'] = $request->meta_description;
        }

        $input['slug'] = Str::slug($request->name);

        $data->update($input);

        return redirect()->route('admin.products.index')->with('success', 'থিম সফলভাবে আপডেট হয়েছে।');
    }

    public function destroy($id) {
        $data = Product::findOrFail($id);
        $imagePath = public_path('assets/images/products/' . $data->photo);
        if (File::exists($imagePath)) { File::delete($imagePath); }
        $data->delete();
        return redirect()->route('admin.products.index')->with('success', 'থিম সফলভাবে ডিলিট করা হয়েছে।');
    }
}