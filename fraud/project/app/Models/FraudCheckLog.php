<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FraudCheckLog extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'phone',
        'result',
        'aggregate_success',
        'aggregate_cancel',
        'aggregate_total',
        'success_ratio',
        'cancel_ratio',
        'checked_by',
    ];

    protected $casts = [
        'result' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
