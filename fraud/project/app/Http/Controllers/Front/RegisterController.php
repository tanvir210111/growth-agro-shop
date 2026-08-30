<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\GeneralSettings;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Classes\GeniusMailer; 
use Toastr;

class RegisterController extends Controller
{
    public function LogReg(){
        // ক্যাপচা ইমেজ কোড দরকার নেই যদি গুগল রিক্যাপচা ব্যবহার করেন
        return view('frontend.log-reg');
    }

    public function register(Request $request){
        
        $gs = GeneralSettings::first();

        // ১. ভ্যালিডেশন রুলস
        if($gs->is_capcha == 1) {
            $rules = [
                'name'     => 'required|string',
                'email'    => 'required|email|unique:users',
                'password' => 'required|min:4|confirmed',
                'g-recaptcha-response' => 'required|captcha'
            ];
        } else {
            $rules = [
                'name'     => 'required|string',
                'email'    => 'required|email|unique:users',
                'password' => 'required|min:4|confirmed'
            ];
        }

        // ২. ভ্যালিডেশন চেক
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            // [FIXED] JSON এর বদলে আগের পেজে এরর সহ পাঠানো হচ্ছে
            return back()->with('error', 'তথ্যগুলো সঠিকভাবে পূরণ করুন।')->withErrors($validator)->withInput();
        }

        // ৩. ইউজার তৈরি
        $user = new User();
        $input = $request->all();
        $input['password'] = bcrypt($request['password']);
        $input['token'] = md5(time().$request->name.$request->email);
        
        // ডিফল্ট ভ্যালু
        $input['status'] = 1; 
        $input['verify'] = 1; 

        // ৪. ইমেইল ভেরিফিকেশন লজিক
        if($gs->is_verification_email == 1)
        {
            $input['status'] = 0; // ভেরিফাই না হওয়া পর্যন্ত ইনএকটিভ
            $input['verify'] = 0;
            
            $user->fill($input)->save();

            $to = $request->email;
            $subject = 'Verify your email address.';
            $msg = "Dear Customer,<br> We noticed that you need to verify your email address. <a href=".url('register/verify/'.$input['token']).">Simply click here to verify. </a>";

            // মেইল পাঠানো
            if($gs->is_smtp == 1)
            {
                $data = [
                    'to' => $to,
                    'subject' => $subject,
                    'body' => $msg,
                ];

                $mailer = new GeniusMailer();
                $mailer->sendCustomMail($data);
            }
            else
            {
                $headers = "From: ".$gs->from_name."<".$gs->from_email.">";
                mail($to,$subject,$msg,$headers);
            }

            return back()->with('success', 'আপনার ইমেইলে একটি ভেরিফিকেশন লিংক পাঠানো হয়েছে। দয়া করে চেক করুন।');
        }
        else 
        {
            // ৫. ইমেইল ভেরিফিকেশন অফ থাকলে সরাসরি লগইন এবং ড্যাশবোর্ড
            $user->fill($input)->save();
            
            Auth::login($user); // অটো লগইন
            // রেজিস্ট্রেশন শেষে অটো লগইন করে অর্ডার পেজে পাঠিয়ে দিবে
Auth::login($user); 
return redirect()->intended(route('user.dashboard'))->with('success', 'রেজিস্ট্রেশন সফল হয়েছে!');
        }
    }

    public function token($token)
    {
        $user = User::where('token',$token)->first();
        if($user){
            $user->status = 1;
            $user->verify = 1;
            $user->token  = NULL;
            $user->update();

            Auth::login($user);
            return redirect()->route('user.dashboard')->with('success', 'ইমেইল ভেরিফিকেশন সফল হয়েছে!');
        }
        else{
            return redirect('/login')->with('error', 'টোকেন মেয়াদউত্তীর্ণ বা ভুল!');
        }
    }
}