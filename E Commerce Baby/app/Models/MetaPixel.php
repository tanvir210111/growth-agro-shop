<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaPixel extends Model
{
    use HasFactory;

    protected $table = 'meta_pixels';

    protected $fillable = [
        'pixel_name',
        'pixel_id',
        'access_token',
        'test_event_code',
        'is_active',
        'is_default',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Ensures CAPI access token is NEVER exposed in toArray() or toJson().
     */
    protected $hidden = [
        'access_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'is_active'    => 'boolean',
            'is_default'   => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active pixels.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include the default pixel.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Return safe masked token representation (e.g. EAAG...****).
     */
    public function getMaskedTokenAttribute(): ?string
    {
        $token = $this->access_token;
        if (empty($token)) {
            return null;
        }

        $length = strlen($token);
        if ($length <= 8) {
            return '********';
        }

        $prefix = substr($token, 0, 4);
        $suffix = substr($token, -4);
        return "{$prefix}..." . str_repeat('*', 4) . "{$suffix}";
    }

    /**
     * Check if a valid token is configured without exposing it.
     */
    public function getHasTokenAttribute(): bool
    {
        return !empty($this->access_token);
    }

    /**
     * Explicit backend-only decrypted token retrieval.
     */
    public function getDecryptedAccessToken(): ?string
    {
        return $this->access_token;
    }

    /**
     * Safe serialized array for public API and Admin UI responses.
     */
    public function toSafeArray(): array
    {
        return [
            'id'              => $this->id,
            'pixel_name'      => $this->pixel_name,
            'pixel_id'        => $this->pixel_id,
            'has_token'       => $this->has_token,
            'masked_token'    => $this->masked_token,
            'test_event_code' => $this->test_event_code,
            'is_active'       => (bool) $this->is_active,
            'is_default'      => (bool) $this->is_default,
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
