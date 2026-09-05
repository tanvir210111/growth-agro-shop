<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaTrackingEvent extends Model
{
    use HasFactory;

    protected $table = 'meta_tracking_events';

    // Status Constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_HELD = 'held';
    public const STATUS_SKIPPED = 'skipped';

    // Deduplication Constants
    public const DEDUP_PENDING = 'pending';
    public const DEDUP_MATCHED = 'matched';
    public const DEDUP_NOT_APPLICABLE = 'not_applicable';
    public const DEDUP_DUPLICATE = 'duplicate';
    public const DEDUP_UNKNOWN = 'unknown';

    protected $fillable = [
        'event_id',
        'event_name',
        'pixel_id',
        'order_id',
        'order_source',
        'action_source',
        'event_source_url',
        'user_data',
        'custom_data',
        'browser_status',
        'server_status',
        'deduplication_status',
        'purchase_mode',
        'hold_reason',
        'rule_id',
        'rule_name',
        'scheduled_at',
        'sent_at',
        'released_at',
        'released_by',
        'response_code',
        'attempt_count',
        'last_attempt_at',
        'response_body',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'user_data'            => 'array',
            'custom_data'          => 'array',
            'scheduled_at'         => 'datetime',
            'sent_at'              => 'datetime',
            'released_at'          => 'datetime',
            'last_attempt_at'      => 'datetime',
            'response_code'        => 'integer',
            'attempt_count'        => 'integer',
            'released_by'          => 'integer',
            'rule_id'              => 'integer',
        ];
    }

    /**
     * Check if server CAPI event was successfully sent.
     */
    public function isServerSent(): bool
    {
        return $this->server_status === self::STATUS_SENT || $this->sent_at !== null;
    }

    /**
     * Check if event is currently on hold.
     */
    public function isHeld(): bool
    {
        return $this->purchase_mode === 'hold' && $this->server_status === self::STATUS_HELD && !$this->isServerSent();
    }

    /**
     * Check if event is currently scheduled for delayed dispatch.
     */
    public function isScheduled(): bool
    {
        return $this->purchase_mode === 'delay' && in_array($this->server_status, ['scheduled', 'pending']) && !$this->isServerSent();
    }

    /**
     * Check if a scheduled event is due for execution.
     */
    public function isDue(): bool
    {
        return $this->isScheduled() && $this->scheduled_at !== null && $this->scheduled_at->isPast();
    }

    /**
     * Check if event can be manually released.
     */
    public function canRelease(): bool
    {
        return $this->event_name === 'Purchase' && $this->isHeld() && !$this->isServerSent();
    }

    /**
     * Check if a failed event can be retried (bounded retry).
     */
    public function canRetry(int $maxAttempts = 5): bool
    {
        return $this->event_name === 'Purchase' 
            && !$this->isServerSent() 
            && ($this->server_status === self::STATUS_FAILED || in_array($this->purchase_mode, ['delay', 'hold']))
            && ($this->attempt_count ?? 0) < $maxAttempts;
    }

    /**
     * Return safe array representation for Admin UI (never exposes raw PII or secrets).
     */
    public function toSafeArray(): array
    {
        return [
            'id'                   => $this->id,
            'order_id'             => $this->order_id,
            'order_source'         => $this->order_source ?: 'MAIN_WEBSITE',
            'event_id'             => $this->event_id,
            'event_name'           => $this->event_name,
            'pixel_id'             => $this->pixel_id,
            'purchase_mode'        => $this->purchase_mode ?: 'instant',
            'server_status'        => $this->server_status,
            'browser_status'       => $this->browser_status,
            'deduplication_status' => $this->deduplication_status,
            'scheduled_at'         => $this->scheduled_at?->toIso8601String(),
            'sent_at'              => $this->sent_at?->toIso8601String(),
            'released_at'          => $this->released_at?->toIso8601String(),
            'attempt_count'        => (int) ($this->attempt_count ?? 0),
            'last_attempt_at'      => $this->last_attempt_at?->toIso8601String(),
            'hold_reason'          => $this->hold_reason,
            'error_message'        => $this->error_message,
            'created_at'           => $this->created_at?->toIso8601String(),
            'updated_at'           => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Scope query to find an existing event by pixel, event name, and event ID.
     */
    public function scopeForPixelAndEvent($query, string $pixelId, string $eventName, string $eventId)
    {
        return $query->where('pixel_id', $pixelId)
            ->where('event_name', $eventName)
            ->where('event_id', $eventId);
    }

    /**
     * Mutator to scrub secrets from response_body before storage.
     */
    public function setResponseBodyAttribute($value): void
    {
        $this->attributes['response_body'] = self::scrubSecrets($value);
    }

    /**
     * Mutator to scrub secrets from error_message before storage.
     */
    public function setErrorMessageAttribute($value): void
    {
        $this->attributes['error_message'] = self::scrubSecrets($value);
    }

    /**
     * Sanitizer helper that strips sensitive tokens and credentials from logs, payloads, or strings.
     */
    public static function scrubSecrets(string|array|null $input): string|array|null
    {
        if ($input === null) {
            return null;
        }

        if (is_array($input)) {
            $scrubbed = [];
            foreach ($input as $key => $val) {
                if (preg_match('/(?:token|secret|auth|password|key)/i', (string) $key)) {
                    $scrubbed[$key] = '[REDACTED]';
                } else {
                    $scrubbed[$key] = self::scrubSecrets($val);
                }
            }
            return $scrubbed;
        }

        $str = (string) $input;

        // 1. Scrub Meta Access Tokens (EAA... pattern)
        $str = preg_replace('/EAA[A-Za-z0-9_\-]{20,}/i', '[REDACTED_CAPI_TOKEN]', $str);

        // 2. Scrub Bearer / Authorization headers
        $str = preg_replace('/(?:Bearer\s+|Authorization:\s*|x-admin-token:\s*)[A-Za-z0-9_\-\.]{8,}/i', 'Bearer [REDACTED]', $str);

        // 3. Scrub query string or JSON access_token keys
        $str = preg_replace('/([?&]access_token=)[^&]+/i', '$1[REDACTED]', $str);
        $str = preg_replace('/("access_token"\s*:\s*")[^"]+(")/i', '$1[REDACTED]$2', $str);

        // 4. Scrub any APP_KEY or Internal secrets
        $internalSecret = env('INTERNAL_API_SECRET');
        if (!empty($internalSecret) && strlen($internalSecret) > 6) {
            $str = str_replace($internalSecret, '[REDACTED_SECRET]', $str);
        }

        return $str;
    }
}
