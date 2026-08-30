<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostingPlan extends Model
{
    /**
     * ডাটাবেজে যেসব ফিল্ডে ডাটা ইনসার্ট বা আপডেট করা যাবে
     */
    protected $fillable = [
        'server_id', 
        'name', 
        'whm_package_name', 
        'category',               // হোস্টিং ক্যাটাগরি (Shared, Reseller, VPS)
        'free_domain',            // 1 = ফ্রি ডোমেইন আছে, 0 = নেই
        'free_domain_extensions', // কোন কোন এক্সটেনশন ফ্রি (JSON Array হিসেবে সেভ হবে)
        'price_monthly', 
        'price_yearly', 
        'description',            // প্যাকেজের বিস্তারিত (যদি লাগে)
        'status'
    ];

    /**
     * ডাটা টাইপ কাস্টিং
     * 'free_domain_extensions' ডাটাবেজে টেক্সট হিসেবে থাকলেও লারাভেল একে অ্যারে হিসেবে ট্রিট করবে
     */
    protected $casts = [
        'free_domain_extensions' => 'array',
        'free_domain' => 'integer',
    ];

    /**
     * সার্ভারের সাথে রিলেশন
     */
    public function server()
    {
        return $this->belongsTo(HostingServer::class);
    }
}