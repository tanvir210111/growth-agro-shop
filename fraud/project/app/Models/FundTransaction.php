<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundTransaction extends Model
{
    use HasFactory;

    /**
     * যে ফিল্ডগুলোতে ডাটা ইনসার্ট করা যাবে।
     * * @var array
     */
    protected $fillable = [
        'type',         // লেনদেনের ধরন: income, expense, withdraw, due_collection
        'amount',       // টাকার পরিমাণ
        'reference_id', // সংশ্লিষ্ট Order ID বা Expense ID (ঐচ্ছিক)
        'note'          // লেনদেনের সংক্ষিপ্ত বিবরণ
    ];

    /**
     * এই মডেলটি স্বয়ংক্রিয়ভাবে টাইমস্ট্যাম্প (created_at, updated_at) মেইনটেইন করবে।
     */
    public $timestamps = true;
}