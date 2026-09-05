<?php

namespace App\Services;

use App\Models\MetaPixel;
use App\Models\MetaTrackingEvent;
use App\Models\MetaTrackingSetting;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MetaTrackingConfigService
{
    protected const CACHE_KEY = 'meta_tracking_runtime_config';
    protected const CACHE_TTL_SECONDS = 300; // 5 minutes (invalidated on every save)

    protected ?array $runtimeCache = null;

    /**
     * Get the active Meta Pixel model instance.
     */
    public function getActivePixel(): ?MetaPixel
    {
        $settings = $this->getSettings();
        if ($settings && $settings->activePixel) {
            return $settings->activePixel;
        }

        // Fallback to default or first active pixel
        $pixel = MetaPixel::default()->first() ?: MetaPixel::active()->first();
        if ($pixel) {
            return $pixel;
        }

        // Backward compatibility fallback: check legacy settings table
        $legacyPixel = Setting::get('facebook_pixel');
        if (!empty($legacyPixel) && preg_match('/^\d{14,18}$/', trim($legacyPixel))) {
            $pixel = new MetaPixel([
                'pixel_name'      => 'Legacy Database Pixel',
                'pixel_id'        => trim($legacyPixel),
                'access_token'    => null,
                'test_event_code' => null,
                'is_active'       => true,
                'is_default'      => true,
            ]);
            return $pixel;
        }

        return null;
    }

    /**
     * Get active numeric Pixel ID string.
     */
    public function getActivePixelId(): ?string
    {
        $pixel = $this->getActivePixel();
        return $pixel ? $pixel->pixel_id : null;
    }

    /**
     * Get decrypted CAPI Access Token (Strictly backend only).
     * Never return this to frontend, API, or public responses.
     */
    public function getCapiAccessToken(): ?string
    {
        $pixel = $this->getActivePixel();
        return $pixel ? $pixel->getDecryptedAccessToken() : null;
    }

    /**
     * Get Test Event Code (Single source of truth: meta_pixels.test_event_code).
     */
    public function getTestEventCode(): ?string
    {
        $pixel = $this->getActivePixel();
        return $pixel ? $pixel->test_event_code : null;
    }

    /**
     * Get current MetaTrackingSetting model.
     */
    public function getSettings(): MetaTrackingSetting
    {
        return MetaTrackingSetting::current();
    }

    /**
     * Check if master tracking is globally enabled.
     */
    public function isTrackingEnabled(): bool
    {
        $settings = $this->getSettings();
        return (bool) ($settings->is_enabled ?? true);
    }

    /**
     * Check if a specific browser event is enabled.
     */
    public function isBrowserEventEnabled(string $event): bool
    {
        if (!$this->isTrackingEnabled()) {
            return false;
        }

        $settings = $this->getSettings();
        $normalized = strtolower(str_replace([' ', '_', '-'], '', $event));

        return match ($normalized) {
            'pageview'         => (bool) $settings->browser_pageview_enabled,
            'addtocart'        => (bool) $settings->browser_add_to_cart_enabled,
            'initiatecheckout' => (bool) $settings->browser_initiate_checkout_enabled,
            'purchase'         => (bool) $settings->browser_purchase_enabled,
            default            => true,
        };
    }

    /**
     * Check if a specific server (CAPI) event is enabled.
    /**
     * Check if a specific server (CAPI) event toggle is enabled in settings.
     */
    public function isServerEventToggleEnabled(string $event): bool
    {
        $settings = $this->getSettings();
        $normalized = strtolower(str_replace([' ', '_', '-'], '', $event));

        return match ($normalized) {
            'pageview'         => (bool) $settings->server_pageview_enabled,
            'addtocart'        => (bool) $settings->server_add_to_cart_enabled,
            'initiatecheckout' => (bool) $settings->server_initiate_checkout_enabled,
            'purchase'         => (bool) $settings->server_purchase_enabled,
            default            => true,
        };
    }

    /**
     * Check if a specific server (CAPI) event is enabled (tracking active, token present, toggle enabled).
     */
    public function isServerEventEnabled(string $event): bool
    {
        if (!$this->isTrackingEnabled()) {
            return false;
        }

        $pixel = $this->getActivePixel();
        if (!$pixel || !$pixel->has_token) {
            return false;
        }

        return $this->isServerEventToggleEnabled($event);
    }

    /**
     * Get Purchase Event Control Mode: instant, delay, hold.
     */
    public function getPurchaseEventMode(): string
    {
        $settings = $this->getSettings();
        return $settings->purchase_event_mode ?: 'instant';
    }

    /**
     * Get Purchase Event delay in minutes.
     */
    public function getPurchaseDelayMinutes(): int
    {
        $settings = $this->getSettings();
        return (int) ($settings->purchase_delay_minutes ?: 30);
    }

    /**
     * Check if automatic customer history rules are enabled.
     */
    public function isAutoRulesEnabled(): bool
    {
        $settings = $this->getSettings();
        return (bool) ($settings->auto_rules_enabled ?? false);
    }

    /**
     * Get full runtime configuration.
     * When $includeCredentials is true, includes decrypted token for backend CAPI service only.
     */
    public function getConfig(bool $includeCredentials = false): array
    {
        $pixel = $this->getActivePixel();
        $settings = $this->getSettings();

        $config = [
            'is_enabled'               => (bool) $settings->is_enabled,
            'pixel_id'                 => $pixel?->pixel_id,
            'pixel_name'               => $pixel?->pixel_name,
            'test_event_code'          => $pixel?->test_event_code,
            'has_token'                => $pixel?->has_token ?? false,
            'browser_events'           => [
                'pageview'          => (bool) $settings->browser_pageview_enabled,
                'add_to_cart'       => (bool) $settings->browser_add_to_cart_enabled,
                'initiate_checkout' => (bool) $settings->browser_initiate_checkout_enabled,
                'purchase'          => (bool) $settings->browser_purchase_enabled,
            ],
            'server_events'            => [
                'pageview'          => (bool) $settings->server_pageview_enabled,
                'add_to_cart'       => (bool) $settings->server_add_to_cart_enabled,
                'initiate_checkout' => (bool) $settings->server_initiate_checkout_enabled,
                'purchase'          => (bool) $settings->server_purchase_enabled,
            ],
            'purchase_control'         => [
                'mode'          => $settings->purchase_event_mode ?: 'instant',
                'delay_minutes' => (int) ($settings->purchase_delay_minutes ?: 30),
            ],
            'auto_rules_enabled'       => (bool) $settings->auto_rules_enabled,
        ];

        if ($includeCredentials) {
            $config['access_token'] = $pixel?->getDecryptedAccessToken();
        } else {
            $config['masked_token'] = $pixel?->masked_token;
        }

        return $config;
    }

    /**
     * Return public client-safe configuration for frontend browser tracking.
     * Guaranteed to NEVER include tokens, secrets, or internal flags.
     */
    public function getPublicClientConfig(): array
    {
        $pixel = $this->getActivePixel();
        $settings = $this->getSettings();

        return [
            'is_enabled'               => (bool) $settings->is_enabled,
            'pixel_id'                 => $pixel?->pixel_id,
            'browser_events'           => [
                'pageview'          => (bool) $settings->browser_pageview_enabled,
                'add_to_cart'       => (bool) $settings->browser_add_to_cart_enabled,
                'initiate_checkout' => (bool) $settings->browser_initiate_checkout_enabled,
                'purchase'          => (bool) $settings->browser_purchase_enabled,
            ],
        ];
    }

    /**
     * Scrub sensitive credentials from any data before logging or storing.
     */
    public function scrubSecrets(string|array|null $data): string|array|null
    {
        return MetaTrackingEvent::scrubSecrets($data);
    }

    /**
     * Invalidate cached runtime configuration.
     * Must be called whenever Admin updates Pixel or Tracking settings.
     */
    public function invalidateCache(): void
    {
        $this->runtimeCache = null;
        Cache::forget(self::CACHE_KEY);
    }
}
