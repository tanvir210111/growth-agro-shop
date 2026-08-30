<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact; // কন্টাক্ট মডেল ব্যবহার করা হয়েছে
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * ১. অ্যাডমিন প্যানেলে সব মেসেজ লিস্ট দেখার জন্য
     */
    public function index()
    {
        // সর্বশেষ মেসেজগুলো আগে দেখাবে
        $data = Contact::orderBy('id', 'desc')->get();
        return view('admin.contact.index', compact('data'));
    }

    /**
     * ২. ফ্রন্টএন্ড থেকে মেসেজ সেভ করার মেথড
     */
    public function store(Request $request)
    {
        // ডাটা ভ্যালিডেশন
        $request->validate([
            'name'    => 'required|max:255',
            'email'   => 'required|email|max:255',
            'subject' => 'nullable|max:255',
            'message' => 'required',
        ]);

        // ডাটাবেসে সেভ করা
        Contact::create([
            'name'    => $request->name,
            'email'   => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
            'status'  => 0, // ডিফল্ট Unread (০)
        ]);

        // সাকসেস মেসেজ সহ ফেরত পাঠানো
        return redirect()->back()->with('success', 'আপনার মেসেজটি সফলভাবে পাঠানো হয়েছে। আমরা শীঘ্রই আপনার সাথে যোগাযোগ করব।');
    }

    /**
     * ৩. মেসেজ ডিলিট করার মেথড
     */
    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return redirect()->back()->with('success', 'মেসেজটি সফলভাবে ডিলিট করা হয়েছে!');
    }

    /**
     * ৪. মেসেজ স্ট্যাটাস আপডেট করার মেথড (অপশনাল - যদি পড়ার পর 'Read' করতে চান)
     */
    public function updateStatus($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->status = 1; // Read (১)
        $contact->save();

        return response()->json(['status' => 'success', 'message' => 'Status updated to Read']);
    }
}