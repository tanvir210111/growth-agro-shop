<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    // ১. fillable অ্যারে আপডেট করা হয়েছে (SEO ফিল্ডসহ)
    protected $fillable = [
        'name', 
        'slug', 
        'photo', 
        'price', 
        'description', 
        'details', 
        'demo_link', 
        'purchase_link', 
        'status',
        // নতুন SEO কলামগুলো এখানে যুক্ত করা হলো
        'meta_title', 
        'meta_keyword', 
        'meta_description'
    ];

    // ২. ডাটা টাইপ কাস্টিং (অপশনাল কিন্তু ভালো প্র্যাকটিস)
    // এটি নিশ্চিত করে যে প্রাইস সবসময় সংখ্যা এবং স্ট্যাটাস সত্য/মিথ্যা হিসেবে আসবে
    protected $casts = [
        'status' => 'boolean',
        'price'  => 'double', 
    ];

    // ৩. অটো স্লাগ তৈরির লজিক
    protected static function boot() {
        parent::boot();

        static::creating(function ($product) {
            // যদি কন্ট্রোলার থেকে স্লাগ না আসে, তবেই নাম থেকে অটো জেনারেট হবে
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function ($product) {
             // আপডেটের সময় নাম পরিবর্তন হলে এবং স্লাগ ফাঁকা থাকলে নতুন স্লাগ তৈরি হবে
             if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }
}