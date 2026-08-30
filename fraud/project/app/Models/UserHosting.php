<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UserHosting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'next_due_date' => 'date',
        'suspended_at'  => 'datetime',
    ];

    public function user() { 
        return $this->belongsTo(User::class, 'user_id'); 
    }
    
    public function plan() { 
        // ডাটাবেস অনুযায়ী 'plan_id' ব্যবহার করতে হবে
        return $this->belongsTo(HostingPlan::class, 'plan_id'); 
    }
    
    public function server() { 
        // ডাটাবেস অনুযায়ী 'server_id' ব্যবহার করতে হবে
        return $this->belongsTo(HostingServer::class, 'server_id'); 
    }
}