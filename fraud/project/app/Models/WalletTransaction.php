<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    // সব কলামে ডাটা ইনসার্ট করার পারমিশন
    protected $guarded = [];

    /**
     * ইউজারের সাথে রিলেশন
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}