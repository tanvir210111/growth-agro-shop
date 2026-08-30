<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * কাস্টমার লিস্ট প্রদর্শন (প্যাজিনেশনসহ)
     */
    public function index()
    {
        // এখানে paginate(15) ব্যবহার করা হয়েছে। 
        // আপনি চাইলে ১৫ এর জায়গায় যেকোনো সংখ্যা দিতে পারেন।
        $customers = Customer::latest()->paginate(10);
        
        return view('admin.customer.index', compact('customers'));
    }

    /**
     * কাস্টমার এডিট পেজ
     */
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin.customer.edit', compact('customer'));
    }

    /**
     * কাস্টমার তথ্য আপডেট
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|unique:customers,phone,' . $id,
            'address' => 'nullable|string'
        ]);

        $customer->update($request->all());
        
        return redirect()->route('admin.customer.index')->with('success', 'কাস্টমার আপডেট হয়েছে।');
    }

    /**
     * কাস্টমার ডিলিট
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        
        return redirect()->route('admin.customer.index')->with('success', 'কাস্টমার ডিলিট হয়েছে।');
    }
}