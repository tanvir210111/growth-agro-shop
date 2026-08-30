<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Fund;
use App\Models\FundTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * ফিনান্সিয়াল স্টেটমেন্ট ভিউ (তারিখ অনুযায়ী ফিল্টার এবং প্যাজিনেশনসহ)
     */
    public function fundStatement(Request $request)
    {
        // ১. রিকোয়েস্ট থেকে তারিখ নেওয়া, না থাকলে আজকের তারিখ সেট করা
        $date = $request->date ?? date('Y-m-d');

        // ২. নির্দিষ্ট তারিখের ইনকাম (অর্ডারের পেইড অ্যামাউন্ট)
        $data['total_income'] = Order::whereDate('created_at', $date)->sum('paid_amount');
        
        // ৩. নির্দিষ্ট তারিখের মোট খরচ
        $data['total_expense'] = Expense::whereDate('expense_date', $date)->sum('amount');
        
        // ৪. কারেন্ট ফান্ড ব্যালেন্স
        $data['current_fund'] = Fund::value('current_balance') ?? 0;
        
        // ৫. নির্দিষ্ট তারিখের ট্রানজ্যাকশন হিস্ট্রি (প্যাজিনেশন যোগ করা হয়েছে)
        // appends() ব্যবহার করা হয়েছে যাতে পেজ পরিবর্তনের সময় তারিখের ফিল্টার ঠিক থাকে
        $data['transactions'] = FundTransaction::whereDate('created_at', $date)
                                ->latest()
                                ->paginate(10, ['*'], 'transactions_page')
                                ->appends(['date' => $date]);
                                
        // ৬. বকেয়া তালিকা (প্যাজিনেশন যোগ করা হয়েছে)
        $data['dues'] = Order::where('due_amount', '>', 0)
                        ->with('customer')
                        ->latest()
                        ->paginate(10, ['*'], 'dues_page');

        return view('admin.report.fund', $data);
    }

    /**
     * ফান্ডে ম্যানুয়ালি টাকা জমা করা
     */
    public function addMoney(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // ১. ফান্ডের মেইন ব্যালেন্স বাড়ানো
            $fund = Fund::firstOrCreate(['id' => 1]);
            $fund->increment('current_balance', $request->amount);

            // ২. ট্রানজ্যাকশন টেবিলে এন্ট্রি দেওয়া
            FundTransaction::create([
                'type' => 'income',
                'amount' => $request->amount,
                'reference_id' => 'MANUAL_ADD',
                'note' => $request->note ?? 'ফান্ডে টাকা জমা করা হয়েছে',
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'টাকা সফলভাবে জমা হয়েছে!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'কিছু একটা সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * ফান্ড থেকে টাকা উত্তোলন করা
     */
    public function withdrawMoney(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string'
        ]);

        $fund = Fund::first();
        if (!$fund || $fund->current_balance < $request->amount) {
            return redirect()->back()->with('error', 'ফান্ডে পর্যাপ্ত ব্যালেন্স নেই!');
        }

        DB::beginTransaction();
        try {
            // ১. ফান্ডের মেইন ব্যালেন্স কমানো
            $fund->decrement('current_balance', $request->amount);

            // ২. ট্রানজ্যাকশন টেবিলে উত্তোলনের এন্ট্রি দেওয়া
            FundTransaction::create([
                'type' => 'withdraw',
                'amount' => $request->amount,
                'reference_id' => 'MANUAL_WITHDRAW',
                'note' => $request->note ?? 'ফান্ড থেকে টাকা উত্তোলন করা হয়েছে',
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'টাকা উত্তোলন সফল হয়েছে!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'কিছু একটা সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }
}