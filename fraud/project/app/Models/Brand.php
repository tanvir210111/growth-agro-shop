<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name', 'photo', 'url'];
    // যদি আপনার টেবিল নাম 'brands' না হয় তবে নিচের লাইনটি আনকমেন্ট করুন
    // protected $table = 'your_table_name';
}