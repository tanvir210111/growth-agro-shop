<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HostingServer;
use App\Models\HostingPlan;
use App\Models\DomainExtension;
use App\Models\UserHosting;
use App\Models\UserDomain;
use Illuminate\Support\Facades\Http;

class HostingManageController extends Controller
{
    // ==================== ১. সার্ভার সেকশন (WHM Servers) ====================
    public function serverIndex()
    {
        $servers = HostingServer::latest()->get();
        return view('admin.hosting.servers', compact('servers'));
    }

    public function serverStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'hostname' => 'required|string',
            'username' => 'required|string',
            'access_hash' => 'required|string',
        ]);

        HostingServer::create($request->all());
        return back()->with('success', 'নতুন সার্ভার সফলভাবে যুক্ত হয়েছে।');
    }

    public function serverUpdate(Request $request, $id)
    {
        $server = HostingServer::findOrFail($id);
        $server->update($request->all());
        return back()->with('success', 'সার্ভার তথ্য সফলভাবে আপডেট হয়েছে।');
    }

    public function serverDelete($id)
    {
        HostingServer::findOrFail($id)->delete();
        return back()->with('success', 'সার্ভারটি মুছে ফেলা হয়েছে।');
    }

    // WHM Root Login (সরাসরি WHM প্যানেলে প্রবেশের জন্য)
    public function serverLogin($id)
    {
        $server = HostingServer::findOrFail($id);
        $hostname = preg_replace('#^https?://|/$#', '', trim($server->hostname));
        
        $api_url = "https://{$hostname}:2087/json-api/create_user_session?api.version=1&user={$server->username}&service=whostmgrd";

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'whm ' . $server->username . ':' . $server->access_hash,
            ])->get($api_url);

            $result = $response->json();
            if (isset($result['data']['url'])) {
                return redirect()->away($result['data']['url']);
            }
            return back()->with('error', 'সার্ভার সেশন তৈরি ব্যর্থ।');
        } catch (\Exception $e) {
            return back()->with('error', 'কানেকশন এরর: ' . $e->getMessage());
        }
    }

    // ==================== ২. হোস্টিং প্ল্যান সেকশন ====================
    public function planIndex()
    {
        $servers = HostingServer::where('status', 1)->get();
        $extensions = DomainExtension::all(); 
        $plans = HostingPlan::with('server')->latest()->get();
        return view('admin.hosting.plans', compact('servers', 'plans', 'extensions'));
    }

    public function planStore(Request $request)
    {
        $input = $request->all();
        if ($request->has('free_domain')) {
            $input['free_domain'] = 1;
            $input['free_domain_extensions'] = json_encode($request->free_domain_extensions); 
        }
        HostingPlan::create($input);
        return back()->with('success', 'নতুন প্যাকেজ তৈরি হয়েছে।');
    }

    public function planUpdate(Request $request, $id)
    {
        $plan = HostingPlan::findOrFail($id);
        $input = $request->all();
        $input['free_domain'] = $request->has('free_domain') ? 1 : 0;
        $input['free_domain_extensions'] = $request->has('free_domain') ? json_encode($request->free_domain_extensions) : null;
        $plan->update($input);
        return back()->with('success', 'প্যাকেজ তথ্য আপডেট হয়েছে।');
    }

    public function planDelete($id)
    {
        HostingPlan::findOrFail($id)->delete();
        return back()->with('success', 'প্যাকেজ মুছে ফেলা হয়েছে।');
    }

    // ==================== ৩. ডোমেইন প্রাইসিং সেকশন ====================
    public function domainIndex()
    {
        $extensions = DomainExtension::all();
        return view('admin.hosting.domains', compact('extensions'));
    }

    public function domainStore(Request $request)
    {
        DomainExtension::create($request->all());
        return back()->with('success', 'ডোমেইন এক্সটেনশন যুক্ত হয়েছে।');
    }

    public function domainUpdate(Request $request, $id)
    {
        $domain = DomainExtension::findOrFail($id);
        $domain->update($request->all());
        return back()->with('success', 'ডোমেইন প্রাইসিং আপডেট হয়েছে।');
    }

    public function domainDelete($id)
    {
        DomainExtension::findOrFail($id)->delete();
        return back()->with('success', 'ডোমেইন মুছে ফেলা হয়েছে।');
    }

    // ==================== ৪. কাস্টমার হোস্টিং ম্যানেজমেন্ট (মেইন) ====================
    
    public function userHostings()
    {
        $servers = HostingServer::all(); 
        $plans = HostingPlan::all(); 
        $hostings = UserHosting::with(['user', 'plan', 'server'])->latest()->get();
        return view('admin.hosting.index', compact('hostings', 'servers', 'plans')); 
    }

    // আপডেট মেথড (৪০৪ সমাধান করতে আইডি চেক যুক্ত করা হয়েছে)
   public function updateHostingStatus(Request $request, $id)
{
    $hosting = UserHosting::with('server')->find($id);
    if (!$hosting) {
        return back()->with('error', 'হোস্টিং রেকর্ডটি খুঁজে পাওয়া যায়নি।');
    }

    $server = $hosting->server;
    $newStatus = $request->status; // Active, Suspended, ইত্যাদি

    // যদি সার্ভার তথ্য থাকে এবং ইউজারনেম থাকে তবেই API কল হবে
    if ($server && $hosting->username) {
        
        $whm_user = trim($server->username);
        $whm_hash = str_replace(["\r", "\n", " "], '', $server->access_hash);
        $hostname = preg_replace('#^https?://|/$#', '', trim($server->hostname));
        $cp_user  = trim($hosting->username);

        // স্ট্যাটাস অনুযায়ী API ফাংশন নির্ধারণ
        $api_function = '';
        if ($newStatus == 'Suspended') {
            $api_function = 'suspendacct';
        } elseif ($newStatus == 'Active') {
            $api_function = 'unsuspendacct';
        }

        if ($api_function != '') {
            $api_url = "https://{$hostname}:2087/json-api/{$api_function}?api.version=1&user={$cp_user}";

            try {
                $response = Http::withoutVerifying()
                    ->timeout(15)
                    ->withHeaders(['Authorization' => "whm $whm_user:$whm_hash"])
                    ->get($api_url);

                $result = $response->json();

                // সার্ভার যদি এরর দেয় তবে ডাটাবেস আপডেট না করে ফিরে যাবে
                if (isset($result['metadata']['result']) && $result['metadata']['result'] == 0) {
                    return back()->with('error', 'সার্ভার রিজেক্ট করেছে: ' . $result['metadata']['reason']);
                }
            } catch (\Exception $e) {
                return back()->with('error', 'সার্ভার কানেকশন এরর: ' . $e->getMessage());
            }
        }
    }

    // সার্ভারে কাজ সফল হলে বা কোনো স্পেশাল স্ট্যাটাস না থাকলে ডাটাবেস আপডেট হবে
    $hosting->update($request->all());

    return back()->with('success', 'হোস্টিং স্ট্যাটাস সার্ভার এবং ডাটাবেসে সফলভাবে আপডেট হয়েছে।');
}

    // সিপ্যানেল অটো-লগইন (SSO) - সংশোধিত API Endpoint
    public function cpanelLogin($id)
    {
        $hosting = UserHosting::with('server')->findOrFail($id);
        $server = $hosting->server;

        if (!$server || !$hosting->username) {
            return back()->with('error', 'সার্ভার তথ্য বা সিপ্যানেল ইউজারনেম পাওয়া যায়নি। আগে সেভ করুন।');
        }

        $whm_user = trim($server->username);
        $whm_hash = str_replace(["\r", "\n", " "], '', $server->access_hash);
        $cp_user  = trim($hosting->username);
        $hostname = preg_replace('#^https?://|/$#', '', trim($server->hostname));

        // WHM API Endpoint: create_user_session
        $api_url = "https://{$hostname}:2087/json-api/create_user_session?api.version=1&user={$cp_user}&service=cpaneld";

        try {
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->withHeaders(['Authorization' => "whm $whm_user:$whm_hash"])
                ->get($api_url);

            $result = $response->json();

            if (isset($result['data']['url'])) {
                return redirect()->away($result['data']['url']);
            } else {
                $reason = $result['metadata']['reason'] ?? 'Unknown Error';
                return back()->with('error', 'সিপ্যানেল লগইন ব্যর্থ। WHM এরর: ' . $reason);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'সার্ভার কানেকশন এরর: আপনার সার্ভারের ২০৮৭ পোর্ট কি ওপেন আছে?');
        }
    }

    public function deleteUserHosting($id)
    {
        $hosting = UserHosting::findOrFail($id);
        $hosting->delete();
        return back()->with('success', 'হোস্টিং সার্ভিসটি স্থায়ীভাবে মুছে ফেলা হয়েছে।');
    }

    // ==================== ৫. কাস্টমার ডোমেইন ম্যানেজমেন্ট ====================
    public function userDomains(Request $request)
    {
        $domains = UserDomain::with('user');
        if ($request->has('search')) {
            $domains->where('domain', 'LIKE', "%{$request->search}%");
        }
        $domains = $domains->orderBy('id', 'desc')->paginate(10); 
        return view('admin.hosting.user_domains', compact('domains'));
    }

    public function deleteUserDomain($id)
    {
        UserDomain::findOrFail($id)->delete();
        return back()->with('success', 'ডোমেইনটি সফলভাবে মুছে ফেলা হয়েছে।');
    }

    public function updateUserDomainNS(Request $request, $id)
    {
        $domain = UserDomain::findOrFail($id);
        $domain->update($request->all());
        return back()->with('success', 'নেমসার্ভার এবং ডোমেইন তথ্য আপডেট হয়েছে।');
    }
}