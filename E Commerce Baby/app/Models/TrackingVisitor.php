<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrackingVisitor extends Model
{
    protected $table = 'tracking_visitors';

    protected $fillable = [
        'visitor_uuid',
        'first_seen_at',
        'last_seen_at',
        'customer_phone',
        'first_source',
        'first_utm_campaign',
        'first_landing_page',
        'total_orders',
        'total_revenue',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'total_orders' => 'integer',
        'total_revenue' => 'decimal:2',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(TrackingSession::class, 'visitor_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(TrackingEvent::class, 'visitor_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'visitor_id');
    }
}
