<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    /**
     * যে ফিল্ডগুলো ডাটাবেসে সেভ করা যাবে।
     * এখানে 'user_id' যুক্ত করা হয়েছে রিলেশনশিপ মেইনটেইন করার জন্য।
     */
    protected $fillable = [
        'user_id', 
        'name',
        'phone',
        'address'
    ];

    /**
     * একজন কাস্টমার একটি ইউজার অ্যাকাউন্টের সাথে যুক্ত (Inverse Relationship)
     * এর ফলে $customer->user->fraud_check_limit এভাবে ডাটা এক্সেস করা যাবে।
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * একজন কাস্টমারের অনেকগুলো অর্ডার থাকতে পারে।
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }
}