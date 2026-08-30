<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HostingPlan;
use App\Models\UserHosting;
use App\Models\Order;
use App\Models\HostingServer;
use App\Models\User;
use App\Jobs\ProcessHosting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class HostingController extends Controller
{
    public function index()
    {
        $plans = HostingPlan::where('status', 1)->get();
        return view('user.hosting.index', compact('plans'));
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'domain' => 'required',
            'plan_id' => 'required'
        ]);

        $domain = trim($request->domain);
        // ইতিমধ্যে এই ডোমেইনে এক্টিভ হোস্টিং থাকলে অর্ডার ব্লক
        if (UserHosting::where('domain', $domain)->where('status', 'Active')->exists()) {
            return redirect()->back()->with('error', 'এই ডোমেইনে ইতিমধ্যে এক্টিভ হোস্টিং আছে। নতুন করে অর্ডার করা যাবে না।');
        }

        $user = Auth::user();
        $plan = HostingPlan::findOrFail($request->plan_id);
        $totalAmount = $plan->price_yearly; 

        // অর্ডার তৈরি
        $order = Order::create([
            'customer_id' => $user->id, 
            'order_number' => 'HOST-' . strtoupper(Str::random(8)),
            'description' => "Hosting Purchase: " . $request->domain,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'due_amount' => $totalAmount,
            'payment_method' => 'Online',
            'status' => 'Pending',
            'support_note' => json_encode([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'domain' => $request->domain,
                'server_id' => $plan->server_id,
                'hosting_price' => $totalAmount
            ]),
            'hash_token' => Str::random(20)
        ]);

        return $this->activateHosting($order->id); 
    }

    public function activateHosting($orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            $meta = json_decode($order->support_note, true);

            if (!$meta || !isset($meta['plan_id'])) {
                return redirect()->route('user.dashboard')->with('error', 'অর্ডার তথ্যে সমস্যা।');
            }

            // ১. সঠিক ইউজার খুঁজে বের করা (Foreign Key Fix)
            $user = User::find($meta['user_id'] ?? $order->customer_id);
            if (!$user) {
                return redirect()->route('user.dashboard')->with('error', 'ইউজার খুঁজে পাওয়া যায়নি।');
            }

            $plan = HostingPlan::find($meta['plan_id']);
            $serverId = $meta['server_id'] ?? ($plan ? $plan->server_id : null);
            
            // ২. অর্ডার স্ট্যাটাস সাথে সাথে Active (পেমেন্ট ডান)
            $order->update([
                'paid_amount' => $order->total_amount, 
                'due_amount' => 0, 
                'status' => 'Active' 
            ]);

            // ক্রেডেনশিয়াল জেনারেট
            $domainParts = explode('.', $meta['domain']);
            $baseName = Str::lower(preg_replace('/[^a-z0-9]/', '', $domainParts[0]));
            $cpanelUser = Str::limit($baseName, 6, '') . rand(10, 99);
            $cpanelPass = Str::random(12) . 'A1!';

            // ৩. হোস্টিং ডাটাবেসে সেভ (Pending) - ইউজার দেখবে তার হোস্টিং প্রসেসিং এ আছে
            $userHosting = UserHosting::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'user_id' => $user->id, // Fixed: সঠিক ইউজার আইডি
                    'server_id' => $serverId,
                    'plan_id' => $plan->id,
                    'domain' => $meta['domain'],
                    'username' => $cpanelUser,
                    'password' => encrypt($cpanelPass),
                    'billing_cycle' => 'Yearly',
                    'amount' => $plan->price_yearly,
                    'next_due_date' => now()->addYear(),
                    'status' => 'Pending' 
                ]
            );

            // ৪. ব্যাকগ্রাউন্ড জব ডিসপ্যাচ (ম্যাজিক লাইন)
            // afterResponse() ব্যবহার করায় ইউজার ওয়েট করবে না, ব্রাউজার ক্লোজ করলেও কাজ হবে
            ProcessHosting::dispatch($userHosting->id)->afterResponse();

            // ৫. ইনস্ট্যান্ট সাকসেস মেসেজ
            return redirect()->route('user.dashboard')->with('success', 'পেমেন্ট সফল! আপনার হোস্টিং সেটআপ হচ্ছে, ১-২ মিনিটের মধ্যে এসএমএস পাবেন।');

        } catch (\Exception $e) {
            Log::error("Controller Activation Error: " . $e->getMessage());
            return redirect()->route('user.dashboard')->with('error', 'পেমেন্ট সেভ হয়েছে কিন্তু সেটআপে সমস্যা। সাপোর্টে যোগাযোগ করুন।');
        }
    }

}