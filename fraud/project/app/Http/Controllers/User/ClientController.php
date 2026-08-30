<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http; 
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\UserDomain; // ফাইলের একদম উপরে এটি যোগ করুন
use App\Models\UserHosting;
use App\Services\WhmService;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================
    // ১. ড্যাশবোর্ড
    // =========================================================
    public function index()
    {
        $user = Auth::user();

        // কাস্টমার আইডি বের করা
        $customer = DB::table('customers')->where('phone', $user->phone)->first();
        $customerId = $customer ? $customer->id : 0;

        // ডাটা লোড
        $data['total_orders']    = DB::table('orders')->where('customer_id', $customerId)->count();
        $data['pending_tickets'] = DB::table('tickets')->where('user_id', $user->id)->where('status', 'pending')->count();

        // Fraud checker stats
        if ($user->last_check_date !== now()->toDateString()) {
            $todayChecks = 0;
        } else {
            $todayChecks = (int) ($user->today_check_count ?? 0);
        }
        $data['fraud_today'] = $todayChecks;
        $data['fraud_daily_limit'] = (int) ($user->fraud_check_daily_limit ?? 0);
        $data['fraud_total'] = 0;
        try {
            if (class_exists(\App\Models\FraudCheckLog::class)) {
                $data['fraud_total'] = \App\Models\FraudCheckLog::where('user_id', $user->id)->count();
            }
        } catch (\Throwable $e) {
            $data['fraud_total'] = 0;
        }
        $data['fraud_recent'] = collect();
        try {
            if (class_exists(\App\Models\FraudCheckLog::class)) {
                $data['fraud_recent'] = \App\Models\FraudCheckLog::where('user_id', $user->id)
                    ->orderBy('id', 'desc')
                    ->limit(5)
                    ->get();
            }
        } catch (\Throwable $e) {
            $data['fraud_recent'] = collect();
        }

        // সাম্প্রতিক সার্ভিস অর্ডার + লাইসেন্স রিনিউ অর্ডার একসাথে (তারিখ অনুযায়ী সাজানো)
        $serviceOrders = DB::table('orders')
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();
        $serviceItems = $serviceOrders->map(function ($o) {
            return (object)[
                'type'            => 'service',
                'order_number'    => $o->order_number,
                'created_at'      => $o->created_at,
                'payment_method'  => $o->payment_method ?? '—',
                'total_amount'    => $o->total_amount,
                'paid_amount'     => $o->paid_amount ?? 0,
                'due_amount'      => $o->due_amount ?? 0,
                'description'     => $o->description ?? '—',
                'hash_token'      => $o->hash_token ?? '',
            ];
        });

        $merged = $serviceItems
            ->sortByDesc(fn ($i) => \Carbon\Carbon::parse($i->created_at)->timestamp)
            ->values();
        $perPage = 5;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $merged->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $data['recent_orders'] = new LengthAwarePaginator(
            $currentPageItems,
            $merged->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('user.client.dashboard', compact('data'));
    }
	
	
	public function userCpanelLogin($id)
{
    // ইউজার যাতে শুধু নিজের হোস্টিংয়ে ঢুকতে পারে (security check)
    $hosting = \App\Models\UserHosting::where('user_id', \Auth::id())->findOrFail($id);
    $server = \App\Models\HostingServer::find($hosting->server_id);

    if (!$server || !$hosting->username) {
        return back()->with('error', 'সার্ভার বা হোস্টিং তথ্য পাওয়া যায়নি।');
    }

    $whm_user = trim($server->username);
    $whm_hash = str_replace(["\r", "\n", " "], '', $server->access_hash);
    $hostname = preg_replace('#^https?://|/$#', '', trim($server->hostname));

    // WHM API এর মাধ্যমে সেশন তৈরি
    $api_url = "https://{$hostname}:2087/json-api/create_user_session?api.version=1&user={$hosting->username}&service=cpaneld";

    try {
        $response = \Illuminate\Support\Facades\Http::withoutVerifying()
            ->withHeaders(['Authorization' => "whm $whm_user:$whm_hash"])
            ->get($api_url);

        $result = $response->json();

        if (isset($result['data']['url'])) {
            return redirect()->away($result['data']['url']);
        }
        return back()->with('error', 'লগইন সেশন তৈরি করতে ব্যর্থ।');
    } catch (\Exception $e) {
        return back()->with('error', 'সার্ভারের সাথে কানেক্ট করা যাচ্ছে না।');
    }
}
	
	
	
	// ==================== হোস্টিং পেজ (আলাদা লিস্ট) ====================
    public function hostings()
    {
        // ইউজারের হোস্টিং ডাটা পেজিনেশনসহ আনা হচ্ছে
        $hostings = \App\Models\UserHosting::with('plan')
                    ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                    ->orderBy('id', 'desc')
                    ->paginate(5);

        return view('user.hosting.index', compact('hostings'));
    }

public function hostingDetails($id)
    {
        // ১. হোস্টিং ডাটা এবং সাথে রিলেশনগুলো নিয়ে আসা হলো
        $hosting = \App\Models\UserHosting::with(['plan', 'server'])
                    ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                    ->findOrFail($id);

        // ২. ডিফল্ট স্ট্যাটাস (যাতে ভিউ ফাইলে এরর না আসে)
        $stats = [
            'disk_used' => 0,
            'disk_limit' => 'Unknown',
            'disk_percent' => 0,
            'bw_used' => 0,
            'bw_limit' => 'Unknown',
            'bw_percent' => 0
        ];

        // ৩. সার্ভার থেকে লাইভ ডাটা আনার চেষ্টা
        if ($hosting->server && $hosting->username) {
            $liveData = WhmService::getAccountStats($hosting->server, $hosting->username);

            if ($liveData) {
                // ডিস্ক ডাটা (MB তে)
                $diskUsed = (float) preg_replace('/[^0-9.]/', '', $liveData['diskused'] ?? 0);
                $diskLimit = $liveData['disklimit'] ?? '0';

                $stats['disk_used'] = $diskUsed;
                $stats['disk_limit'] = $diskLimit;

                // ডিস্ক পার্সেন্টেজ হিসাব
                if ($diskLimit === 'unlimited') {
                    $stats['disk_percent'] = ($diskUsed > 1024) ? 10 : 5; 
                } else {
                    $limitNum = (float) preg_replace('/[^0-9.]/', '', $diskLimit);
                    if ($limitNum > 0) {
                        $stats['disk_percent'] = round(($diskUsed / $limitNum) * 100);
                    }
                }

                // ব্যান্ডউইথ ডাটা
                $stats['bw_limit'] = $liveData['plan'] ?? $hosting->plan->name;
            }
        }

        // ৪. ভিউতে ডাটা পাঠানো
        return view('user.hosting.details', compact('hosting', 'stats'));
    }

    public function updateHostingPassword(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|min:10|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
        ], [
            'new_password.regex' => 'পাসওয়ার্ডে বড় হাতের অক্ষর, ছোট হাতের অক্ষর, সংখ্যা এবং স্পেশাল ক্যারেক্টার থাকতে হবে।'
        ]);

        $hosting = \App\Models\UserHosting::with('server')->where('user_id', \Illuminate\Support\Facades\Auth::id())->findOrFail($id);
        $server = $hosting->server;

        if (!$server || !$hosting->username) {
            return back()->with('error', 'সার্ভার বা ইউজার তথ্য পাওয়া যায়নি।');
        }

        $whm_user = trim($server->username);
        $whm_hash = str_replace(["\r", "\n", " "], '', $server->access_hash);
        $hostname = preg_replace('#^https?://|/$#', '', trim($server->hostname));
        
        $api_url = "https://{$hostname}:2087/json-api/passwd?api.version=1";

        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->timeout(20)
                ->withHeaders(['Authorization' => "whm $whm_user:$whm_hash"])
                ->asForm() 
                ->post($api_url, [
                    'user'     => $hosting->username,
                    'pass'     => $request->new_password, 
                    'password' => $request->new_password  
                ]);

            $result = $response->json();

            if (isset($result['metadata']['result']) && $result['metadata']['result'] == 1) {
                $hosting->update(['password' => $request->new_password]); 
                return back()->with('success', 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে।');
            } 
            
            $reason = $result['metadata']['reason'] ?? 'সার্ভার রিকোয়েস্ট রিজেক্ট করেছে।';
            return back()->with('error', 'ব্যর্থ হয়েছে: ' . $reason);

        } catch (\Exception $e) {
            return back()->with('error', 'সার্ভারের সাথে যোগাযোগ করতে সমস্যা হচ্ছে।');
        }
    }

public function domains(Request $request)
{
    $user = Auth::user();
    
    // ডোমেইন কুয়েরি শুরু
    $domains = \App\Models\UserDomain::where('user_id', $user->id);

    // [NEW] যদি সার্চ ইনপুট থাকে, তবে ফিল্টার করবে
    if ($request->has('search') && $request->search != null) {
        $search = $request->search;
        $domains->where('domain', 'LIKE', "%{$search}%");
    }

    // রেজাল্ট পেজিনেশন সহ আনা
    $domains = $domains->orderBy('id', 'desc')->paginate(5);
    
    return view('user.domain.index', compact('domains'));
}

public function updateNameservers(Request $request, $id)
    {
        $request->validate([
            'ns1' => 'required|string',
            'ns2' => 'required|string',
        ]);

        // ১. ডোমেইন ভেরিফিকেশন
        $domain = \App\Models\UserDomain::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // ২. ডাটাবেস আপডেট
        $domain->update([
            'ns1' => $request->ns1,
            'ns2' => $request->ns2,
            'ns3' => $request->ns3,
            'ns4' => $request->ns4,
        ]);

        // ৩. এডমিনের কাছে নির্দিষ্ট নাম্বারে এসএমএস পাঠানো
        try {
            $user = Auth::user();
            
            // মেসেজ বডি
            $msg = "NS Update!\nUser: {$user->name}\nDomain: {$domain->domain}\nNS1: {$request->ns1}\nNS2: {$request->ns2}";
            
            // [ফিক্সড নাম্বার]
            $adminPhone = '01849832178'; 

            $this->sendAdminSms($adminPhone, $msg);

        } catch (\Exception $e) {
            Log::error("NS Update SMS Failed: " . $e->getMessage());
        }

        return back()->with('success', 'নেমসার্ভার সফলভাবে আপডেট করা হয়েছে!');
    }
	
	// প্রাইভেট হেল্পার: এডমিনকে এসএমএস পাঠানো
    private function sendAdminSms($number, $message)
    {
        try {
            $gs = DB::table('generalsettings')->first();
            
            if (!$gs || !$gs->sms_api_key || !$gs->sms_sender_id) return;

            // ৩ সেকেন্ডের মধ্যে এসএমএস পাঠানোর চেষ্টা করবে
            Http::timeout(3)->withoutVerifying()->get("http://bulksmsbd.net/api/smsapi", [
                'api_key'  => $gs->sms_api_key,
                'type'     => 'text',
                'number'   => $number,
                'senderid' => $gs->sms_sender_id,
                'message'  => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error("Admin SMS Error: " . $e->getMessage());
        }
    }
	

    // =========================================================
    // ৩. সাপোর্ট টিকেট
    // =========================================================
    public function support()
    {
        $tickets = DB::table('tickets')
                        ->where('user_id', Auth::id())
                        ->orderBy('id', 'desc')
                        ->paginate(5);

        return view('user.client.support', compact('tickets'));
    }

    public function createTicket(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required',
            'priority' => 'required'
        ]);

        DB::table('tickets')->insert([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'priority' => $request->priority,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'টিকেট সফলভাবে তৈরি হয়েছে।');
    }
    
    public function viewTicket($id)
    {
        $ticket = DB::table('tickets')->where('id', $id)->where('user_id', Auth::id())->first();
        if (!$ticket) return abort(404);

        $replies = DB::table('ticket_replies')->where('ticket_id', $id)->get();

        return view('user.client.view', compact('ticket', 'replies'));
    }

 public function replyTicket(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);

        $ticket = DB::table('tickets')->where('id', $id)->where('user_id', Auth::id())->first();

        if (!$ticket) {
            return response()->json(['error' => 'টিকেট পাওয়া যায়নি।'], 404);
        }

        // ডুপ্লিকেট চেক (Cache Lock)
        $lockKey = 'reply_lock_' . Auth::id() . '_' . $id;
        if (\Illuminate\Support\Facades\Cache::has($lockKey)) {
            return response()->json(['error' => 'মেসেজ পাঠানো হচ্ছে...'], 429);
        }
        \Illuminate\Support\Facades\Cache::put($lockKey, true, 5);

        try {
            // [পরিবর্তন-১] insertGetId ব্যবহার করে আইডি নেওয়া
            $newId = DB::table('ticket_replies')->insertGetId([
                'ticket_id' => $id,
                'user_id'   => Auth::id(),
                'message'   => $request->message,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('tickets')->where('id', $id)->update([
                'updated_at' => now(),
                'status' => 'open'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'মেসেজ পাঠানো হয়েছে।',
                'data' => [
                    'id' => $newId, // [পরিবর্তন-২] নতুন আইডি পাঠানো হচ্ছে
                    'user_id' => Auth::id(),
                    'message' => $request->message,
                    'created_at' => now()->diffForHumans(),
                    'sender' => 'আপনি'
                ]
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Cache::forget($lockKey);
            return response()->json(['error' => 'Error'], 500);
        }
    }

    public function checkNewMessages(Request $request, $id)
    {
        $last_id = (int) $request->last_id;

        $new_replies = DB::table('ticket_replies')
                        ->where('ticket_id', $id)
                        ->where('id', '>', $last_id)
                        ->orderBy('id', 'asc')
                        ->get();

        if ($new_replies->count() > 0) {
            $html = '';
            foreach ($new_replies as $reply) {
                $is_user = ($reply->user_id == Auth::id());
                $class = $is_user ? 'bubble-user' : 'bubble-admin';
                $sender = $is_user ? 'আপনি' : 'অ্যাডমিন সাপোর্ট';
                $time = \Carbon\Carbon::parse($reply->created_at)->diffForHumans();

                $html .= '<div class="bubble '.$class.'">
                            <strong>'.$sender.':</strong><br>
                            '.$reply->message.'
                            <span class="msg-meta">'.$time.'</span>
                          </div>';
            }

            return response()->json([
                'has_new' => true,
                'html' => $html,
                'last_id' => $new_replies->last()->id
            ]);
        }

        return response()->json(['has_new' => false]);
    }

/**
 * ইউজারের জন্য নতুন API Key জেনারেট বা রিসেট করা
 */
public function generateKey()
{
    $user = Auth::user();
    
    // ৬০ ক্যারেক্টারের একটি ইউনিক র‍্যান্ডম স্ট্রিং তৈরি করে সেভ করা
    $user->api_key = Str::random(60);
    $user->save();

    return back()->with('success', 'নতুন API Key সফলভাবে তৈরি হয়েছে: ' . $user->api_key);
}



    public function profile()
    {
        $user = Auth::user();
        return view('user.client.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $request->validate([
            'name'  => 'required',
            'phone' => 'required',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'photo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $input = $request->all();

        if ($file = $request->file('photo')) {
            $name = time().$file->getClientOriginalName();
            $file->move('assets/images/users/', $name);
            
            if($user->photo) {
                if (file_exists('assets/images/users/'.$user->photo)) {
                    @unlink('assets/images/users/'.$user->photo);
                }
            }
            $input['photo'] = $name;
        }

        if ($request->filled('password')) {
            $input['password'] = Hash::make($request->password);
        } else {
            unset($input['password']);
        }

        $user->update($input);

        return back()->with('success', 'প্রোফাইল সফলভাবে আপডেট করা হয়েছে।');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login')->with('success', 'আপনি সফলভাবে লগআউট করেছেন।');
    }
}