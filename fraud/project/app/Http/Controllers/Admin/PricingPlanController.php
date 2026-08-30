<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Illuminate\Http\Request;

class PricingPlanController extends Controller
{
    public function index()
    {
        $plans = PricingPlan::orderBy('id', 'desc')->get();
        return view('admin.pricing.index', compact('plans'));
    }

    // নতুন প্ল্যান সেভ করার মেথড (লিঙ্ক সহ)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'price' => 'required',
            'features' => 'required'
        ]);

        $input = $request->all();
        // চেকবক্স হ্যান্ডেল করা
        $input['is_featured'] = $request->has('is_featured') ? 1 : 0;
        
        // নিশ্চিত করা যে order_link ইনপুট যাচ্ছে
        PricingPlan::create($input);

        return redirect()->back()->with('success', 'নতুন প্যাকেজ সফলভাবে যোগ করা হয়েছে।');
    }

    // এডিট পেইজ দেখানো (যা আগে ছিল না বলে এরর আসছিল)
    public function edit($id)
    {
        $data = PricingPlan::findOrFail($id);
        return view('admin.pricing.edit', compact('data'));
    }

    // আপডেট করার মেথড
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'price' => 'required',
            'features' => 'required'
        ]);

        $data = PricingPlan::findOrFail($id);
        $input = $request->all();
        $input['is_featured'] = $request->has('is_featured') ? 1 : 0;

        $data->update($input);

        return redirect()->route('admin.pricing.index')->with('success', 'প্যাকেজটি আপডেট করা হয়েছে।');
    }

    public function destroy($id)
    {
        PricingPlan::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'প্যাকেজটি ডিলিট করা হয়েছে।');
    }
}