<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\GeneralSettings;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserResetController extends Controller
{
    // ১. ফোন নাম্বার চাওয়ার ফর্ম দেখাবে
    public function showPhoneForm()
    {
        // ভিউ ফাইল: resources/views/frontend/auth/phone.blade.php
        return view('frontend.auth.phone');
    }

    // ২. ওটিপি জেনারেট এবং এসএমএস পাঠানো
    public function sendOtp(Request $request)
    {
        // ফোন নাম্বার ভ্যালিডেশন (চেক করবে ইউজার আছে কি না)
        $request->validate([
            'phone' => 'required|exists:users,phone',
        ], [
            'phone.exists' => 'এই ফোন নাম্বারে কোনো অ্যাকাউন্ট পাওয়া যায়নি।'
        ]);

        $phone = $request->phone;
        
        // ৬ সংখ্যার ওটিপি তৈরি
        $otp = rand(100000, 999999);

        // সেশনে ওটিপি এবং ফোন নাম্বার সেভ রাখা (টেম্পোরারি)
        Session::put('user_reset_otp', $otp);
        Session::put('user_reset_phone', $phone);

        // এসএমএস পাঠানো
        $msg = "আপনার পাসওয়ার্ড রিসেট ওটিপি: {$otp}";
        $this->sendSms($phone, $msg);

        return redirect()->route('user.otp.verify')->with('success', 'আপনার মোবাইলে ওটিপি পাঠানো হয়েছে।');
    }

    // ৩. ওটিপি ইনপুট ফর্ম দেখাবে
    public function showOtpForm()
    {
        // যদি সেশনে ফোন নাম্বার না থাকে, তাহলে আবার শুরুতে পাঠাবে
        if (!Session::has('user_reset_phone')) {
            return redirect()->route('user.forgot.password');
        }
        // ভিউ ফাইল: resources/views/frontend/auth/otp.blade.php
        return view('frontend.auth.otp');
    }

    // ৪. ওটিপি চেক এবং ভেরিফাই করা
    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|numeric']);

        // সেশনের ওটিপির সাথে মিলছে কিনা চেক করা
        if ($request->otp == Session::get('user_reset_otp')) {
            // মিললে ভেরিফাইড ফ্ল্যাগ সেট করা
            Session::put('user_otp_verified', true);
            return redirect()->route('user.password.reset');
        }

        return back()->with('error', 'ভুল ওটিপি! আবার চেষ্টা করুন।');
    }

    // ৫. পাসওয়ার্ড রিসেট ফর্ম দেখাবে
    public function showResetForm()
    {
        // ওটিপি ভেরিফাই না করে এখানে আসলে ফেরত পাঠাবে
        if (!Session::has('user_otp_verified')) {
            return redirect()->route('user.forgot.password');
        }
        // ভিউ ফাইল: resources/views/frontend/auth/reset.blade.php
        return view('frontend.auth.reset');
    }

    // ৬. নতুন পাসওয়ার্ড আপডেট করা
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:6',
        ]);

        $phone = Session::get('user_reset_phone');
        $user = User::where('phone', $phone)->first();

        if ($user) {
            // পাসওয়ার্ড হ্যাশ করে আপডেট করা
            $user->password = Hash::make($request->password);
            $user->save();

            // কাজ শেষ, তাই সেশন ক্লিয়ার করে দেওয়া
            Session::forget(['user_reset_otp', 'user_reset_phone', 'user_otp_verified']);

            // লগইন পেজে রিডাইরেক্ট (আপনার ফ্রন্টএন্ড লগইন রাউট)
            return redirect()->route('login')->with('success', 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে। এখন লগইন করুন।');
        }

        return back()->with('error', 'কোথাও সমস্যা হয়েছে। আবার চেষ্টা করুন।');
    }

    // ৭. এসএমএস পাঠানোর ফাংশন
    private function sendSms($number, $message)
    {
        try {
            $gs = GeneralSettings::first(); // সেটিংস থেকে API Key আনা

            if ($gs->sms_api_key && $gs->sms_sender_id) {
                Http::timeout(10)->get("http://bulksmsbd.net/api/smsapi", [
                    'api_key'  => $gs->sms_api_key,
                    'type'     => 'text',
                    'number'   => $number,
                    'senderid' => $gs->sms_sender_id,
                    'message'  => $message
                ]);
            }
        } catch (\Exception $e) {
            // এসএমএস না গেলে লগ ফাইলে এরর রাখবে
            Log::error("User SMS Error: " . $e->getMessage());
        }
    }
}