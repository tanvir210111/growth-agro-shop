<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    protected $table = 'pricing_plans';
    public $timestamps = false; // যেহেতু আমরা SQL এ টাইমস্ট্যাম্প রাখিনি

    protected $fillable = [
        'title', 
        'price', 
        'currency', 
        'duration', 
        'features', 
        'is_featured', 
        'order_link'
    ];
}