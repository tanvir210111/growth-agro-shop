<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // এসএমএস এর জন্য
use Illuminate\Support\Facades\Log;
use App\Models\GeneralSettings; // সেটিংস এর জন্য
use Yajra\DataTables\Facades\DataTables;

class AdminTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    // =========================================================
    // ১. টিকেটের তালিকা (AJAX & Paginated)
    // =========================================================
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $datas = DB::table('tickets')
                        ->join('users', 'tickets.user_id', '=', 'users.id')
                        ->select('tickets.*', 'users.name as user_name')
                        ->orderBy('tickets.updated_at', 'desc')
                        ->get();

            return DataTables::of($datas)
                ->addIndexColumn()
                ->addColumn('updated_at', function($data) {
                    return \Carbon\Carbon::parse($data->updated_at)->diffForHumans();
                })
                ->addColumn('action', function($data) {
                    return '<div class="btn-group">
                                <a href="' . route('admin.tickets.view', $data->id) . '" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                                <button type="button" data-url="' . route('admin.tickets.delete', $data->id) . '" class="btn btn-sm btn-danger delete-ticket"><i class="fas fa-trash"></i></button>
                            </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        
        return view('admin.tickets.index');
    }

    // =========================================================
    // ২. ডিটেইলস এবং চ্যাট ভিউ
    // =========================================================
    public function view($id)
    {
        $ticket = DB::table('tickets')
                    ->join('users', 'tickets.user_id', '=', 'users.id')
                    ->select('tickets.*', 'users.name as user_name', 'users.photo as user_photo')
                    ->where('tickets.id', $id)
                    ->first();

        if (!$ticket) return redirect()->route('admin.tickets.index')->with('error', 'টিকেট পাওয়া যায়নি');

        $replies = DB::table('ticket_replies')
                     ->where('ticket_id', $id)
                     ->orderBy('id', 'asc')
                     ->get();

        return view('admin.tickets.view', compact('ticket', 'replies'));
    }

    // =========================================================
    // ৩. অ্যাডমিন রিপ্লাই সেভ করা + এসএমএস পাঠানো (Updated)
    // =========================================================
    public function reply(Request $request, $id)
    {
        $request->validate(['message' => 'required']);

        try {
            // ১. রিপ্লাই ইনসার্ট করা
            $replyId = DB::table('ticket_replies')->insertGetId([
                'ticket_id' => $id,
                'admin_id'  => Auth::guard('admin')->id(),
                'user_id'   => null,
                'message'   => $request->message,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // ২. টিকেট স্ট্যাটাস আপডেট (Replied)
            DB::table('tickets')->where('id', $id)->update([
                'status' => 'replied',
                'updated_at' => now()
            ]);

            // ==================================================
            // [NEW] কাস্টমারকে এসএমএস পাঠানো
            // ==================================================
            
            // কাস্টমারের তথ্য বের করা
            $ticket = DB::table('tickets')->where('id', $id)->first();
            
            if ($ticket) {
                $user = DB::table('users')->where('id', $ticket->user_id)->first();
                
                if ($user && $user->phone) {
                    $gs = GeneralSettings::first();
                    $shortMsg = \Illuminate\Support\Str::limit($request->message, 30); // মেসেজ ছোট করা
                    $smsText = "প্রিয় {$user->name}, আপনার টিকেট #{$id} এর রিপ্লাই দেওয়া হয়েছে: '{$shortMsg}'... বিস্তারিত ড্যাশবোর্ডে দেখুন। - {$gs->title}";
                    
                    // এসএমএস ফাংশন কল
                    $this->sendSms($user->phone, $smsText);
                }
            }
            // ==================================================

            return response()->json([
                'status' => 'success',
                'message' => 'রিপ্লাই পাঠানো হয়েছে এবং কাস্টমারকে জানানো হয়েছে।',
                'last_id' => $replyId 
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================
    // ৪. নতুন মেসেজ চেক করা (AJAX Polling)
    // =========================================================
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
                $is_admin = !empty($reply->admin_id); 
                
                $class  = $is_admin ? 'bubble-admin-me' : 'bubble-customer';
                $sender = $is_admin ? 'আপনি (Support)' : 'কাস্টমার';
                $time   = \Carbon\Carbon::parse($reply->created_at)->diffForHumans();

                $html .= '<div class="message-row '.($is_admin ? 'admin-row' : '').'">
                            <div class="chat-avatar '.($is_admin ? 'avatar-admin' : 'avatar-customer').'">
                                '.($is_admin ? 'A' : 'C').'
                            </div>
                            <div class="bubble '.$class.'">
                                '.$reply->message.'
                                <div class="msg-meta">'.$time.'</div>
                            </div>
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

    // =========================================================
    // ৫. টিকেটের স্ট্যাটাস পরিবর্তন করা (AJAX)
    // =========================================================
    public function updateStatus(Request $request, $id)
    {
        DB::table('tickets')->where('id', $id)->update([
            'status' => $request->status,
            'updated_at' => now()
        ]);
        return response()->json(['status' => 'success', 'message' => 'স্ট্যাটাস আপডেট করা হয়েছে!']);
    }

    // =========================================================
    // ৬. ডিলিট মেথড
    // =========================================================
    public function delete($id)
    {
        try {
            DB::transaction(function () use ($id) {
                DB::table('ticket_replies')->where('ticket_id', $id)->delete();
                DB::table('tickets')->where('id', $id)->delete();
            });
            return response()->json(['status' => 'success', 'message' => 'টিকেট মুছে ফেলা হয়েছে।']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'এরর: ' . $e->getMessage()]);
        }
    }

    // =========================================================
    // ৭. এসএমএস পাঠানোর প্রাইভেট ফাংশন
    // =========================================================
    private function sendSms($number, $message)
    {
        try {
            $gs = GeneralSettings::first(); 

            if (!$gs->sms_api_key || !$gs->sms_sender_id) {
                return; // এপিআই কি না থাকলে রিটার্ন করবে
            }

            Http::timeout(5)->get("http://bulksmsbd.net/api/smsapi", [
                'api_key'  => $gs->sms_api_key,    
                'type'     => 'text',
                'number'   => $number,
                'senderid' => $gs->sms_sender_id, 
                'message'  => $message
            ]);
        } catch (\Exception $e) {
            Log::error("SMS Error: " . $e->getMessage());
        } 
    }
}