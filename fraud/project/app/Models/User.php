<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'photo',
        'zip',
        'residency',
        'city',
        'address',
        'phone',
        'fax',
        'email',
        'designation',
        'password',
        'verification_link',
        'affilate_code',
        'is_provider',
        'twofa',
        'go',
        'package_id', 
        'fraud_check_limit',
        'fraud_check_expiry',
        'fraud_check_daily_limit',
        'today_check_count',
        'last_check_date',
        'details',
        'api_key', // ফ্রড চেকিং এবং এসএমএস এপিআই (উভয় কাজেই ব্যবহার হবে)

        // [নতুন] SMS Reseller System এর জন্য যুক্ত করা হলো
        'wallet_balance',     // কাস্টমারের বর্তমান ব্যালেন্স
        'non_masking_rate',   // নন-মাস্কিং এসএমএস রেট
        'masking_rate',       // মাস্কিং এসএমএস রেট

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function customer()
    {
        // এটি ধরে নিচ্ছে যে customers টেবিলে user_id নামে একটি কলাম আছে
        return $this->hasOne(Customer::class, 'user_id', 'id');
    }

    public function posts(){
        return $this->hasMany('App\Models\Post','user_id');
    }

    /**
     * এপিআই কি জেনারেট করার ফাংশন
     * (এটি আগের মতোই থাকবে)
     */
    public function generateApiKey()
    {
        $this->api_key = \Illuminate\Support\Str::random(60);
        $this->save();
        return $this->api_key;
    }
}