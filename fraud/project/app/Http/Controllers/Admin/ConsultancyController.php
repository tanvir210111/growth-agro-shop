<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultancy; // মডেল ব্যবহার নিশ্চিত করা হয়েছে
use Illuminate\Http\Request;

class ConsultancyController extends Controller
{
    /**
     * ১. অ্যাডমিন প্যানেলে সব রিকোয়েস্ট লিস্ট দেখার জন্য
     */
    public function index()
    {
        $data = Consultancy::orderBy('id', 'desc')->get(); // সর্বশেষ রিকোয়েস্ট আগে দেখাবে
        return view('admin.consultancy.index', compact('data'));
    }

    /**
     * ২. ফ্রন্টএন্ড থেকে ডাটা সেভ করার মেথড (এটি যোগ করা হয়েছে)
     */
    public function storeConsultancy(Request $request)
    {
        // ডাটা ভ্যালিডেশন
        $request->validate([
            'name'  => 'required|max:255',
            'phone' => 'required|max:20',
        ]);

        // ডাটাবেসে ডাটা ইনসার্ট করা
        Consultancy::create([
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'message' => $request->message,
            'status'  => 0, // ডিফল্ট স্ট্যাটাস ০ (Pending)
        ]);

        // সাকসেস মেসেজ সহ ফেরত পাঠানো
        return redirect()->back()->with('success', 'আপনার রিকোয়েস্টটি সফলভাবে গ্রহণ করা হয়েছে। ধন্যবাদ!');
    }

    /**
     * ৩. স্ট্যাটাস পরিবর্তন করার মেথড (Pending/Completed)
     */
    public function status($id, $status)
    {
        $consultancy = Consultancy::findOrFail($id); // আইডি খুঁজে না পেলে এরর দিবে
        $consultancy->status = $status;
        $consultancy->update(); // স্ট্যাটাস আপডেট

        return redirect()->back()->with('success', 'স্ট্যাটাস সফলভাবে আপডেট করা হয়েছে!');
    }

    /**
     * ৪. রিকোয়েস্ট ডিলিট করার মেথড
     */
    public function destroy($id)
    {
        $consultancy = Consultancy::findOrFail($id);
        $consultancy->delete(); // ডাটাবেস থেকে ডিলিট

        return redirect()->back()->with('success', 'রিকোয়েস্টটি সফলভাবে ডিলিট করা হয়েছে!');
    }
}