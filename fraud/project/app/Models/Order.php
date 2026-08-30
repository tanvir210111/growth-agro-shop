<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * যে কলামগুলোতে ডাটা সেভ করার অনুমতি দেওয়া হয়েছে।
     * এখানে description, payment_method এবং support_note নতুন যোগ করা হয়েছে।
     */
    protected $fillable = [
        'customer_id',
        'description',      // পণ্যের বিবরণ বা সাধারণ নোট
        'support_note',     // সার্ভিস অ্যান্ড সাপোর্ট সংক্রান্ত নোট
        'order_number',
        'total_amount',
        'paid_amount',
        'payment_method',   // পেমেন্ট মাধ্যম (Cash, Bkash, etc.)
        'due_amount',
        'hash_token',
        'status'            // Active, Pending ইত্যাদি
    ];

    /**
     * রিলেশনশিপ: প্রতিটি অর্ডার একজন নির্দিষ্ট কাস্টমারের আন্ডারে থাকে।
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * ঐচ্ছিক: তারিখ ফরম্যাট করার জন্য হেল্পার (ব্লেড ফাইলে ব্যবহারের জন্য)
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d M, Y');
    }
}