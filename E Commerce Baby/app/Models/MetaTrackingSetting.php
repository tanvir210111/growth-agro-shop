<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaTrackingSetting extends Model
{
    use HasFactory;

    protected $table = 'meta_tracking_settings';

    protected $fillable = [
        'is_enabled',
        'active_pixel_id',
        'browser_pageview_enabled',
        'browser_add_to_cart_enabled',
        'browser_initiate_checkout_enabled',
        'browser_purchase_enabled',
        'server_pageview_enabled',
        'server_add_to_cart_enabled',
        'server_initiate_checkout_enabled',
        'server_purchase_enabled',
        'purchase_event_mode',
        'purchase_delay_minutes',
        'auto_rules_enabled',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_enabled'                        => 'boolean',
            'active_pixel_id'                   => 'integer',
            'browser_pageview_enabled'         => 'boolean',
            'browser_add_to_cart_enabled'       => 'boolean',
            'browser_initiate_checkout_enabled' => 'boolean',
            'browser_purchase_enabled'         => 'boolean',
            'server_pageview_enabled'          => 'boolean',
            'server_add_to_cart_enabled'       => 'boolean',
            'server_initiate_checkout_enabled' => 'boolean',
            'server_purchase_enabled'          => 'boolean',
            'purchase_delay_minutes'           => 'integer',
            'auto_rules_enabled'               => 'boolean',
        ];
    }

    /**
     * The active Meta Pixel relation.
     */
    public function activePixel(): BelongsTo
    {
        return $this->belongsTo(MetaPixel::class, 'active_pixel_id');
    }

    /**
     * Singleton accessor to fetch or initialize the primary settings row.
     */
    public static function current(): self
    {
        $settings = static::with('activePixel')->first();
        if (!$settings) {
            $defaultPixel = MetaPixel::default()->first() ?: MetaPixel::active()->first();
            $settings = static::create([
                'is_enabled'                        => true,
                'active_pixel_id'                   => $defaultPixel?->id,
                'browser_pageview_enabled'          => true,
                'browser_add_to_cart_enabled'       => true,
                'browser_initiate_checkout_enabled' => true,
                'browser_purchase_enabled'          => true,
                'server_pageview_enabled'           => true,
                'server_add_to_cart_enabled'        => true,
                'server_initiate_checkout_enabled'  => true,
                'server_purchase_enabled'           => true,
                'purchase_event_mode'               => 'instant',
                'purchase_delay_minutes'            => 30,
                'auto_rules_enabled'                => false,
            ]);
            $settings->load('activePixel');
        }

        return $settings;
    }

    /**
     * Safe array representation for Admin UI & API endpoints.
     */
    public function toSafeArray(): array
    {
        return [
            'id'                       => $this->id,
            'is_enabled'               => (bool) $this->is_enabled,
            'active_pixel_id'          => $this->active_pixel_id,
            'active_pixel'             => $this->activePixel ? $this->activePixel->toSafeArray() : null,
            'browser_events'           => [
                'pageview'          => (bool) $this->browser_pageview_enabled,
                'add_to_cart'       => (bool) $this->browser_add_to_cart_enabled,
                'initiate_checkout' => (bool) $this->browser_initiate_checkout_enabled,
                'purchase'          => (bool) $this->browser_purchase_enabled,
            ],
            'server_events'            => [
                'pageview'          => (bool) $this->server_pageview_enabled,
                'add_to_cart'       => (bool) $this->server_add_to_cart_enabled,
                'initiate_checkout' => (bool) $this->server_initiate_checkout_enabled,
                'purchase'          => (bool) $this->server_purchase_enabled,
            ],
            'purchase_control'         => [
                'mode'          => $this->purchase_event_mode ?: 'instant',
                'delay_minutes' => (int) ($this->purchase_delay_minutes ?: 30),
            ],
            'auto_rules_enabled'       => (bool) $this->auto_rules_enabled,
            'updated_at'               => $this->updated_at?->toIso8601String(),
        ];
    }
}
