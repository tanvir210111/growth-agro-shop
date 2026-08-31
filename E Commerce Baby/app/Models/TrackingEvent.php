<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingEvent extends Model
{
    protected $table = 'tracking_events';

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'visitor_id',
        'event_name',
        'entity_type',
        'entity_id',
        'cta_identifier',
        'page_path',
        'event_value',
        'properties',
        'created_at',
    ];

    protected $casts = [
        'event_value' => 'decimal:2',
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(TrackingVisitor::class, 'visitor_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TrackingSession::class, 'session_id');
    }
}
