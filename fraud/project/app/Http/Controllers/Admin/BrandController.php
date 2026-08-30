<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // ভ্যালিডেশনের জন্য

class BrandController extends Controller
{
    // ১. সব ব্র্যান্ড দেখানোর জন্য (List)
    public function index()
    {
        $brands = Brand::orderBy('id', 'desc')->get();
        return view('admin.brand.index', compact('brands'));
    }

    // ২. নতুন ব্র্যান্ড তৈরির পেজ (Create Form)
    public function create()
    {
        return view('admin.brand.create');
    }

    // ৩. ডেটাবেসে সেভ করা (Store)
    public function store(Request $request)
    {
        // ভ্যালিডেশন
        $rules = [
            'name'  => 'required|max:191',
            'photo' => 'required|mimes:jpeg,jpg,png,svg|max:2048', // সর্বোচ্চ 2MB
            'url'   => 'nullable|url'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $input = $request->all();

        // লোগো আপলোড লজিক
        if ($file = $request->file('photo')) {
            $name = time() . $file->getClientOriginalName();
            $file->move('assets/images/brands', $name); // পাবলিক ফোল্ডারে সেভ হবে
            $input['photo'] = $name;
        }

        Brand::create($input);

        return redirect()->route('admin.brand.index')->with('success', 'New Brand Added Successfully.');
    }

    // ৪. এডিট পেজ (Edit Form)
    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.brand.edit', compact('brand'));
    }

    // ৫. আপডেট করা (Update)
    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        // ভ্যালিডেশন (ফটো না দিলেও আপডেট হবে)
        $rules = [
            'name'  => 'required|max:191',
            'photo' => 'nullable|mimes:jpeg,jpg,png,svg|max:2048',
            'url'   => 'nullable|url'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $input = $request->all();

        // যদি নতুন লোগো আপলোড করা হয়
        if ($file = $request->file('photo')) {
            // আগের লোগো ডিলিট করা
            if(file_exists(public_path('assets/images/brands/'.$brand->photo))){
                @unlink(public_path('assets/images/brands/'.$brand->photo));
            }

            $name = time() . $file->getClientOriginalName();
            $file->move('assets/images/brands', $name);
            $input['photo'] = $name;
        }

        $brand->update($input);

        return redirect()->route('admin.brand.index')->with('success', 'Brand Updated Successfully.');
    }

    // ৬. ডিলিট করা (Delete)
    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);

        // ফোল্ডার থেকে লোগো ডিলিট করা
        if(file_exists(public_path('assets/images/brands/'.$brand->photo))){
            @unlink(public_path('assets/images/brands/'.$brand->photo));
        }

        $brand->delete();

        return redirect()->route('admin.brand.index')->with('success', 'Brand Deleted Successfully.');
    }
}