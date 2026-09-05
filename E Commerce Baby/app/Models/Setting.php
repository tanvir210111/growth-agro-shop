<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        if ($key === 'facebook_pixel') {
            try {
                if (class_exists(\App\Models\MetaPixel::class) && class_exists(\App\Models\MetaTrackingSetting::class)) {
                    $trimmed = trim((string) $value);
                    if ($trimmed === '') {
                        $settings = \App\Models\MetaTrackingSetting::current();
                        if ($settings) {
                            $settings->update(['active_pixel_id' => null]);
                        }
                    } elseif (preg_match('/^\d{14,18}$/', $trimmed)) {
                        $pixel = \App\Models\MetaPixel::where('pixel_id', $trimmed)->first();
                        if (!$pixel) {
                            $pixel = \App\Models\MetaPixel::create([
                                'pixel_name'      => 'Default Pixel',
                                'pixel_id'        => $trimmed,
                                'is_active'       => true,
                                'is_default'      => true,
                            ]);
                        }
                        $settings = \App\Models\MetaTrackingSetting::current();
                        if ($settings) {
                            $settings->update(['active_pixel_id' => $pixel->id]);
                        }
                    }
                    if (app()->bound(\App\Services\MetaTrackingConfigService::class)) {
                        app(\App\Services\MetaTrackingConfigService::class)->invalidateCache();
                    }
                }
            } catch (\Throwable $e) {
                // Non-blocking sync
            }
        }
    }
}
