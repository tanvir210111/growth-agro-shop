<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultancy extends Model
{
    use HasFactory;

    // কোন কোন কলামে ডাটা ইনসার্ট করা যাবে তা ডিফাইন করা
    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'status'
    ];
}