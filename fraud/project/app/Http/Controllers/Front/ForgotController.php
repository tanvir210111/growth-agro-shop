<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Classes\GeniusMailer;

class ForgotController extends Controller
{
    /**
     * Forgot Password Form দেখানো
     */
    public function showForgotForm()
    {
        return view('frontend.forgot');
    }

    /**
     * Forgot Password Submit হ্যান্ডেল
     */
    public function forgot(Request $request)
    {
        // ভ্যালিডেশন
        $request->validate([
            'email' => 'required|email',
        ]);

        // General Settings লোড
        $gs = GeneralSettings::findOrFail(1);

        // ইউজার খোঁজা
        $user = User::where('email', $request->email)->first();

        if ($user) {
            // নতুন পাসওয়ার্ড তৈরি
            $newPass = Str::random(8);
            $user->password = bcrypt($newPass);
            $user->save();

            // ইমেইল পাঠানো
            $subject = "Reset Password Request";
            $msg = "Your new password is: " . $newPass;

            if ($gs->is_smtp == 1) {
                $data = [
                    'to' => $request->email,
                    'subject' => $subject,
                    'body' => $msg,
                ];
                $mailer = new GeniusMailer();
                $mailer->sendCustomMail($data);
            } else {
                $headers = "From: " . $gs->from_name . "<" . $gs->from_email . ">";
                mail($request->email, $subject, $msg, $headers);
            }

            // সফল হলে redirect
            return redirect()->back()->with('success', '✅ Your password has been reset successfully. Please check your email for the new password.');
        } else {
            // ইউজার না পেলে error মেসেজ
            return redirect()->back()->with('error', '❌ No account found with this email address.');
        }
    }
}
