<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Fund;
use App\Models\FundTransaction;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * খরচের তালিকা এবং ফর্ম প্রদর্শন করা (প্যাজিনেশনসহ)
     */
    public function index()
    {
        // ডাটাবেস থেকে সকল খরচ সংগ্রহ করা (সাম্প্রতিকগুলো আগে)
        // paginate(10) ব্যবহার করা হয়েছে যাতে প্রতি পেজে ১০টি ডাটা দেখায়
        $expenses = Expense::latest()->paginate(10); 
        
        // view পাথ অনুযায়ী 'admin.expense.index' লোড করা
        return view('admin.expense.index', compact('expenses'));
    }

    /**
     * নতুন খরচ সেভ করা এবং তহবিল থেকে টাকা কমানো
     */
    public function store(Request $request)
    {
        // ডাটা ভ্যালিডেশন
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
        ]);

        try {
            // ১. খরচ রেকর্ড তৈরি করা
            $expense = Expense::create($request->all());

            // ২. মূল তহবিল (Fund) থেকে টাকা কমানো এবং ট্রানজ্যাকশন রেকর্ড করা
            $fund = Fund::firstOrCreate(['id' => 1]); // তহবিল না থাকলে তৈরি করবে

            if ($fund->current_balance < $request->amount) {
                return redirect()->back()->with('error', 'তহবিলে পর্যাপ্ত ব্যালেন্স নেই!');
            }

            // তহবিল থেকে টাকা কমানো
            $fund->decrement('current_balance', $request->amount);

            // ট্রানজ্যাকশন হিস্ট্রি রেকর্ড করা (রিপোর্টের জন্য)
            FundTransaction::create([
                'type' => 'expense',
                'amount' => $request->amount,
                'reference_id' => 'EXP-' . $expense->id,
                'note' => 'খরচ: ' . $expense->title
            ]);

            return redirect()->back()->with('success', 'খরচ সফলভাবে যুক্ত হয়েছে এবং তহবিল আপডেট করা হয়েছে।');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'কিছু একটা সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * খরচ ডিলিট করা
     */
    public function destroy($id)
    {
        try {
            $expense = Expense::findOrFail($id);
            
            // নোট: বাস্তব ক্ষেত্রে খরচ ডিলিট করলে তহবিলে টাকা ফেরত আসবে কি না তা আপনার পলিসির ওপর নির্ভর করে। 
            // এখানে শুধু ডিলিট লজিক দেওয়া হলো।
            $expense->delete();
            
            return redirect()->back()->with('success', 'খরচ সফলভাবে ডিলিট করা হয়েছে।');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'ডিলিট করতে সমস্যা হয়েছে।');
        }
    }
}