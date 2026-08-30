<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\UserHosting;
use App\Models\HostingServer;
use App\Models\HostingPlan;
use App\Models\User;
use App\Models\Order;
use App\Services\WhmService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessHosting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $hostingId;

    public function __construct($hostingId)
    {
        $this->hostingId = $hostingId;
    }

    public function handle()
    {
        // ব্যাকগ্রাউন্ড প্রসেসের জন্য সময়সীমা বাড়ানো হলো (২ মিনিট)
        set_time_limit(120);

        try {
            $hosting = UserHosting::find($this->hostingId);
            
            // হোস্টিং না পেলে বা অলরেডি একটিভ থাকলে প্রসেস বন্ধ
            if (!$hosting || $hosting->status == 'Active') return;

            $server = HostingServer::find($hosting->server_id);
            $plan = HostingPlan::find($hosting->plan_id);
            $user = User::find($hosting->user_id);

            // সার্ভার ডাটা মিসিং থাকলে লগ করে রিটার্ন
            if (!$server || !$plan || !$user) {
                Log::error("Job Failed: Missing Server/Plan/User for Hosting ID {$this->hostingId}");
                return;
            }

            // পাসওয়ার্ড ডিক্রিপ্ট করা (ফলব্যাক সহ)
            try {
                $password = decrypt($hosting->password);
            } catch (\Exception $e) {
                $password = Str::random(10) . 'A1!'; 
            }

            // প্যাকেজ নাম সেট করা
            $fullPackage = trim($server->username) . '_' . trim($plan->whm_package_name);

            // WHM API কল (WhmService এ টাইমআউট সেট করা আছে)
            $whmResult = WhmService::createAccount($server, [
                'username' => $hosting->username,
                'domain'   => $hosting->domain,
                'plan_name'=> $fullPackage,
                'email'    => $user->email,
                'password' => $password
            ]);

            // সফল হলে
            if (isset($whmResult['metadata']['result']) && $whmResult['metadata']['result'] == 1) {
                
                // ১. হোস্টিং স্ট্যাটাস Active করা
                $hosting->update([
                    'status' => 'Active',
                    'ip_address' => $whmResult['data']['ip'] ?? $server->hostname
                ]);

                // ২. এসএমএস পাঠানো
                $this->sendSms($user, $hosting->domain, $hosting->username, $password);

                Log::info("Success: cPanel Created for {$hosting->domain}");
            } else {
                Log::error("WHM Failed for {$hosting->domain}: " . json_encode($whmResult));
            }

        } catch (\Exception $e) {
            Log::error("Job Critical Error: " . $e->getMessage());
        }
    }

    private function sendSms($user, $domain, $username, $password)
    {
        try {
            $gs = DB::table('generalsettings')->first();
            $message = "Congrats! Your hosting for $domain is active. User: $username, Pass: $password. Login: $domain/cpanel";

            if ($gs && $gs->sms_api_key) {
                // SSL বাইপাস ও টাইমআউট সেট করা হয়েছে
                Http::timeout(20)->withoutVerifying()->get("https://bulksmsbd.net/api/smsapi", [
                    'api_key'  => $gs->sms_api_key,
                    'type'     => 'text',
                    'number'   => $user->phone,
                    'senderid' => $gs->sms_sender_id,
                    'message'  => $message
                ]);
            }
        } catch (\Exception $e) {
            Log::error("SMS Sending Failed: " . $e->getMessage());
        }
    }
}