<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrackingSession extends Model
{
    protected $table = 'tracking_sessions';

    protected $fillable = [
        'session_uuid',
        'visitor_id',
        'session_start',
        'session_end',
        'duration_seconds',
        'entry_url',
        'landing_page_path',
        'page_type',
        'referrer_url',
        'referrer_domain',
        'channel',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'click_id',
        'device_type',
        'browser',
        'os',
        'ip_address',
        'user_agent',
        'is_converted',
        'order_id',
    ];

    protected $casts = [
        'session_start' => 'datetime',
        'session_end' => 'datetime',
        'duration_seconds' => 'integer',
        'is_converted' => 'boolean',
        'order_id' => 'integer',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(TrackingVisitor::class, 'visitor_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(TrackingEvent::class, 'session_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
