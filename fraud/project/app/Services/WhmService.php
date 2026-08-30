<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhmService
{
    /**
     * cPanel একাউন্ট তৈরি করার ফাংশন
     */
    public static function createAccount($server, $data)
    {
        // পোর্ট ডিফল্ট ২০৮৭ সেট করা হলো যদি ডাটাবেসে না থাকে
        $port = $server->port ?? 2087;
        
        // WHM API URL
        $url = "https://{$server->hostname}:{$port}/json-api/createacct";
        
        // টোকেন বা হ্যাশ হ্যান্ডলিং (আপনার ডাটাবেসে যে নামেই থাকুক কাজ করবে)
        $apiToken = $server->api_token ?? $server->access_hash;

        // API Headers
        $headers = [
            'Authorization' => 'whm ' . $server->username . ':' . $apiToken,
        ];

        // একাউন্ট ডাটা
        $params = [
            'api.version' => 1,
            'username'    => $data['username'],
            'domain'      => $data['domain'],
            'plan'        => $data['plan_name'],
            'contactemail'=> $data['email'],
            'password'    => $data['password'],
            'language'    => 'en',
            'useregns'    => 0,
            'reseller'    => 0
        ];

        try {
            // [FIXED] timeout(120) যোগ করা হয়েছে যাতে সার্ভার স্লো হলেও ২ মিনিট অপেক্ষা করে
            $response = Http::timeout(120) 
                            ->withoutVerifying() // SSL স্কিপ
                            ->withHeaders($headers)
                            ->get($url, $params);
            
            return $response->json();
            
        } catch (\Exception $e) {
            Log::error("WHM Account Create Failed: " . $e->getMessage());
            return ['metadata' => ['result' => 0, 'reason' => 'Connection Error: ' . $e->getMessage()]];
        }
    }

    /**
     * একাউন্ট সাসপেন্ড করার ফাংশন
     */
    public static function suspendAccount($server, $username, $reason = 'Overdue Payment')
    {
        $port = $server->port ?? 2087;
        $url = "https://{$server->hostname}:{$port}/json-api/suspendacct";
        
        $apiToken = $server->api_token ?? $server->access_hash;
        $headers = ['Authorization' => 'whm ' . $server->username . ':' . $apiToken];

        try {
            $response = Http::timeout(60) // সাসপেন্ডের জন্য ৬০ সেকেন্ড যথেষ্ট
                            ->withoutVerifying()
                            ->withHeaders($headers)
                            ->get($url, [
                                'api.version' => 1,
                                'user'        => $username,
                                'reason'      => $reason
                            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error("WHM Suspend Failed: " . $e->getMessage());
            return ['metadata' => ['result' => 0, 'reason' => $e->getMessage()]];
        }
    }
	
	public static function getAccountStats($server, $username)
    {
        $port = $server->port ?? 2087;
        // listaccts এপিআই কল করা হচ্ছে কারণ এতে ডিস্ক ইউসেজ সহজে পাওয়া যায়
        $url = "https://{$server->hostname}:{$port}/json-api/listaccts";
        
        $apiToken = $server->api_token ?? $server->access_hash;
        $headers = ['Authorization' => 'whm ' . $server->username . ':' . $apiToken];

        try {
            $response = Http::timeout(5) // ৫ সেকেন্ড টাইমআউট
                            ->withoutVerifying()
                            ->withHeaders($headers)
                            ->get($url, [
                                'api.version' => 1,
                                'search' => $username,
                                'searchtype' => 'user'
                            ]);
            
            $data = $response->json();

            if (isset($data['data']['acct'][0])) {
                return $data['data']['acct'][0];
            }
            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * একাউন্ট আন-সাসপেন্ড করার ফাংশন (প্রয়োজন হতে পারে)
     */
    public static function unsuspendAccount($server, $username)
    {
        $port = $server->port ?? 2087;
        $url = "https://{$server->hostname}:{$port}/json-api/unsuspendacct";
        
        $apiToken = $server->api_token ?? $server->access_hash;
        $headers = ['Authorization' => 'whm ' . $server->username . ':' . $apiToken];

        try {
            $response = Http::timeout(60)
                            ->withoutVerifying()
                            ->withHeaders($headers)
                            ->get($url, [
                                'api.version' => 1,
                                'user'        => $username
                            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error("WHM Unsuspend Failed: " . $e->getMessage());
            return ['metadata' => ['result' => 0, 'reason' => $e->getMessage()]];
        }
    }
}