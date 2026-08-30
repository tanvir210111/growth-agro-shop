<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Models\Fund;
use App\Models\FundTransaction;
use App\Models\GeneralSettings;
use App\Models\Deposit; // এই লাইনটি মিসিং ছিল, যোগ করা হলো
use App\Models\UserDomain;
use App\Models\UserHosting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; 
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon; 

class PosController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('customer')->latest();

        if ($request->filter == 'due') {
            $query->where('due_amount', '>', 0);
        } elseif ($request->filter == 'paid') {
            $query->where('due_amount', '<=', 0);
        }

        $orders = $query->paginate(10)->appends($request->all());
        
        return view('admin.pos.index', compact('orders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'required|string|max:20',
            'total'          => 'required|numeric|min:0',
            'paid'           => 'nullable|numeric|min:0',
            'payment_method' => 'required',
        ]);

        // DB::transaction ব্যবহার করা হলো স্পিড বাড়ানোর জন্য
        try {
            DB::transaction(function () use ($request) {
                
                // ১. ইউজার হ্যান্ডলিং
                $user = User::where('phone', $request->phone)->first();
                $isNewUser = false;

                if (!$user) {
                    $user = new User();
                    $user->name = $request->name;
                    $user->email = $request->phone . '@noemail.com'; 
                    $user->phone = $request->phone;
                    $user->password = Hash::make('12345678');
                    $user->status = 1;
                    $user->email_verified = 'No';
                    $user->save();
                    $isNewUser = true;
                }

                // ২. কাস্টমার হ্যান্ডলিং
                $customer = Customer::firstOrCreate(
                    ['phone' => $request->phone],
                    ['name' => $request->name, 'address' => $request->address]
                );

                // ইউজার লিংক আপডেট (যদি না থাকে)
                if (!$customer->user_id) {
                    $customer->user_id = $user->id;
                    $customer->save();
                }

                // ৩. অর্ডার ক্রিয়েশন
                $paid_amount = $request->paid ?? 0;
                $order = Order::create([
                    'customer_id'    => $customer->id,
                    'description'    => $request->description, 
                    'support_note'   => $request->support_note, 
                    'order_number'   => 'INV-' . strtoupper(Str::random(8)),
                    'total_amount'   => $request->total,
                    'paid_amount'    => $paid_amount,
                    'payment_method' => $request->payment_method, 
                    'due_amount'     => $request->total - $paid_amount,
                    'hash_token'     => bin2hex(random_bytes(16))
                ]);

                // ৪. ফান্ড এবং ট্রানজেকশন
                if ($paid_amount > 0) {
                    $fund = Fund::firstOrCreate(['id' => 1]);
                    $fund->increment('current_balance', $paid_amount);

                    FundTransaction::create([
                        'type'            => 'income',
                        'amount'          => $paid_amount,
                        'payment_method'  => $request->payment_method,
                        'reference_id'    => $order->order_number,
                        'note'            => 'পস সেল: ' . $order->order_number
                    ]);
                }

      
                // ৫. অটোমেটিক রিচার্জ অথবা প্যাকেজ আপগ্রেড চেক
                if ($order->due_amount <= 0) {
                    // চেক করা হচ্ছে এটা কি এসএমএস রিচার্জ নাকি প্যাকেজ?
                    if ($this->isSmsRechargeOrder($order)) {
                        $this->processSmsRecharge($order);
                    } else {
                        $this->upgradeUserPackage($order, $user);
                    }
                }

                // ৬. এসএমএস (এটি ট্রানজেকশনের বাইরে রাখা ভালো, তবে এখানে লজিক সহজ রাখার জন্য রাখা হলো)
                // নতুন ইউজার হলে ওয়েলকাম মেসেজ
                if ($isNewUser) {
                    $this->sendSms($request->phone, "স্বাগতম! আপনার একাউন্ট খোলা হয়েছে। পাসওয়ার্ড: 12345678");
                }
                
                $liveLink = route('frontend.invoice.live', ['order_number' => $order->order_number, 'token' => $order->hash_token]);
                $orderMsg = "অর্ডার সফল। বিল: {$order->total_amount}tk, জমা: {$paid_amount}tk। ইনভয়েস: {$liveLink}";
                $this->sendSms($request->phone, $orderMsg);
            });

            return redirect()->back()->with('success', 'অর্ডার প্রসেসিং সফল হয়েছে!');

        } catch (\Exception $e) {
            Log::error("POS Store Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'ত্রুটি: ' . $e->getMessage());
        }
    }

    /**
     * ডিউ কালেকশন (অপ্টিমাইজড)
     */
    public function collectDue(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required'
        ]);

        try {
            // ডাটাবেস ট্রানজেকশন শুরু (সব কাজ একসাথে হবে, তাই দ্রুত হবে)
            DB::transaction(function () use ($request, $id) {
                
                // অর্ডার লক করা হলো যাতে একই সময়ে দুইবার রিকোয়েস্ট না আসে
                $order = Order::with('customer')->lockForUpdate()->findOrFail($id);

                if ($request->amount > $order->due_amount) {
                    throw new \Exception('অ্যামাউন্ট বকেয়ার চেয়ে বেশি হতে পারে না!');
                }

                // অর্ডার আপডেট
                $order->paid_amount += $request->amount;
                $order->due_amount -= $request->amount;
                $order->save();

                // ফান্ড আপডেট
                $fund = Fund::firstOrCreate(['id' => 1]);
                $fund->increment('current_balance', $request->amount);

                FundTransaction::create([
                    'type'            => 'due_collection',
                    'amount'          => $request->amount,
                    'payment_method'  => $request->payment_method,
                    'reference_id'    => $order->order_number,
                    'note'            => 'বকেয়া কালেকশন: ' . $order->order_number
                ]);

    // প্যাকেজ বা রিচার্জ চেক – ডোমেইন/হোস্টিং অর্ডার সর্বপ্রথম চেক (POS থেকে পেইড করলে অটো অ্যাক্টিভ + cPanel)
                if ($order->due_amount <= 0) {
                    if ($this->isDomainHostingOrder($order)) {
                        $this->activateDomainHostingFromPOS($order);
                    } elseif ($this->isSmsRechargeOrder($order)) {
                        $this->processSmsRecharge($order);
                    } else {
                        $this->upgradeUserPackage($order);
                    }
                }

                // এসএমএস পাঠানো
                $liveLink = route('frontend.invoice.live', ['order_number' => $order->order_number, 'token' => $order->hash_token]);
                $message = "পেমেন্ট {$request->amount}tk রিসিভ। বকেয়া: {$order->due_amount}tk। ইনভয়েস: {$liveLink}";
                
                // কাস্টমার ফোন চেক
                if($order->customer && $order->customer->phone){
                    $this->sendSms($order->customer->phone, $message);
                }
            });

            return redirect()->back()->with('success', 'বকেয়া কালেক্ট এবং আপডেট সফল!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * প্যাকেজ আপগ্রেড (অপ্টিমাইজড)
     * $user প্যারামিটার অপশনাল, যদি আগে থেকেই ইউজার অবজেক্ট থাকে তাহলে কুয়েরি কমাবে
     */
    private function upgradeUserPackage($order, $user = null)
    {
        try {
            if (!$order->customer) return;

            // ইউজার না পাঠানো হলে খুঁজে বের করা
            if (!$user) {
                if ($order->customer->user_id) {
                    $user = User::find($order->customer->user_id);
                } else {
                    $user = User::where('phone', $order->customer->phone)->first();
                    if ($user) {
                        // ফিউচার আপডেটের জন্য লিংক করে দেওয়া
                        $order->customer->user_id = $user->id;
                        $order->customer->save();
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error("Pkg Upgrade Fail: " . $e->getMessage());
        }
    }

    public function downloadInvoice($order_number)
    {
        try {
            $order = Order::where('order_number', $order_number)->with('customer')->firstOrFail();
            $gs = GeneralSettings::first(); 
            $pdf = Pdf::loadView('frontend.invoice_pdf', compact('order', 'gs'))
                      ->setPaper('a4', 'portrait')
                      ->setOptions(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true, 'defaultFont' => 'sans-serif']);
            return $pdf->download('Invoice-'.$order->order_number.'.pdf');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'পিডিএফ এরর।');
        }
    }
	
	/**
     * চেক করবে এটি ডোমেইন বা হোস্টিং অর্ডার কিনা
     * (FrontendController এর completeDomainOrder থেকে তৈরি অর্ডার)
     */
    private function isDomainHostingOrder($order)
    {
        if (stripos($order->description ?? '', 'Domain Reg:') !== false || stripos($order->description ?? '', 'Hosting Order for:') !== false) {
            return true;
        }
        $meta = is_string($order->support_note ?? '') ? json_decode($order->support_note, true) : ($order->support_note ?? []);
        return is_array($meta) && isset($meta['domain']) && isset($meta['user_id']);
    }

    /**
     * POS থেকে ডোমেইন/হোস্টিং অর্ডারের বকেয়া শোধ হলে – অর্ডার, ডোমেইন ও হোস্টিং Active করে cPanel তৈরি
     */
    private function activateDomainHostingFromPOS($order)
    {
        try {
            $order->update(['status' => 'Active']);

            UserDomain::where('order_id', $order->id)->update(['status' => 'Active']);

            $userHosting = UserHosting::where('order_id', $order->id)->first();
            if ($userHosting) {
                $userHosting->update(['status' => 'Active']);
                $meta = json_decode($order->support_note, true);
                if (!empty($meta['plan_id']) && empty($userHosting->username)) {
                    app(\App\Http\Controllers\User\HostingController::class)->activateHosting($order->id);
                }
            }
            Log::info("POS Domain/Hosting activated: Order {$order->order_number}");
        } catch (\Exception $e) {
            Log::error("POS Domain/Hosting activation fail: " . $e->getMessage());
        }
    }

	/**
     * চেক করবে এটি এসএমএস রিচার্জের অর্ডার কিনা
     * (অর্ডারের ডেসক্রিপশনে 'SMS' বা 'Recharge' শব্দ থাকলে সত্য হবে)
     */
    private function isSmsRechargeOrder($order)
    {
        if (stripos($order->description ?? '', 'SMS') !== false || stripos($order->description ?? '', 'Recharge') !== false) {
            return true;
        }
        return false;
    }

    /**
     * ইউজারের ওয়ালেটে ব্যালেন্স যোগ করার ফাংশন
     */
    private function processSmsRecharge($order)
    {
        try {
            // কাস্টমার এবং ইউজার চেক
            if (!$order->customer) return;

            $user = null;
            if ($order->customer->user_id) {
                $user = User::find($order->customer->user_id);
            } else {
                $user = User::where('phone', $order->customer->phone)->first();
            }

            if ($user) {
                // ডুপ্লিকেট রিচার্জ আটকাতে চেক (অর্ডার নাম্বার দিয়ে ট্রানজেকশন চেক)
                // 'deposits' টেবিল ব্যবহার করা হচ্ছে
                $alreadyProcessed = Deposit::where('trx_id', $order->order_number)->exists();

                if (!$alreadyProcessed) {
                    // ১. ব্যালেন্স যোগ করা (users টেবিলে wallet_balance)
                    $user->increment('wallet_balance', $order->total_amount);

                    // ২. ডিপোজিট হিস্ট্রি তৈরি করা
                    Deposit::create([
                        'user_id' => $user->id,
                        'amount'  => $order->total_amount,
                        'method'  => 'POS / Admin', // পেমেন্ট মেথড
                        'trx_id'  => $order->order_number, // অর্ডার নাম্বার ট্রানজেকশন আইডি হিসেবে যাবে
                        'status'  => 'completed',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // ৩. ফিউচার আপডেটের জন্য কাস্টমারের সাথে ইউজার লিংক করা
                    if (!$order->customer->user_id) {
                        $order->customer->user_id = $user->id;
                        $order->customer->save();
                    }

                    Log::info("POS SMS Recharge Success: User {$user->id} -> Amount {$order->total_amount}");
                }
            }
        } catch (\Exception $e) {
            Log::error("POS SMS Recharge Fail: " . $e->getMessage());
        }
    }

    // এসএমএস ফাংশনে টাইমআউট কমানো হয়েছে যাতে সাইট স্লো না হয়
    private function sendSms($number, $message)
    {
        try {
            $gs = GeneralSettings::first(); 
            if (!$gs || !$gs->sms_api_key || !$gs->sms_sender_id) return;

            // Timeout 3 সেকেন্ড করা হলো। এর বেশি সময় লাগলে স্কিপ করবে।
            Http::timeout(3)->get("http://bulksmsbd.net/api/smsapi", [
                'api_key'  => $gs->sms_api_key,     
                'type'     => 'text',
                'number'   => $number,
                'senderid' => $gs->sms_sender_id, 
                'message'  => $message
            ]);
        } catch (\Exception $e) {
            // এসএমএস না গেলে লগ ফাইলে থাকবে কিন্তু ইউজার আটকাবে না
            Log::warning("SMS Slow/Fail: " . $e->getMessage());
        }
    }
}