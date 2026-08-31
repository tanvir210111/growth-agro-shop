<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsDailyAttribution extends Model
{
    protected $table = 'analytics_daily_attribution';

    protected $fillable = [
        'report_date',
        'channel',
        'utm_source',
        'utm_campaign',
        'landing_page',
        'visitors_count',
        'page_views',
        'cta_clicks',
        'add_to_cart_count',
        'checkout_started_count',
        'orders_count',
        'total_revenue',
        'conversion_rate',
    ];

    protected $casts = [
        'report_date' => 'date',
        'visitors_count' => 'integer',
        'page_views' => 'integer',
        'cta_clicks' => 'integer',
        'add_to_cart_count' => 'integer',
        'checkout_started_count' => 'integer',
        'orders_count' => 'integer',
        'total_revenue' => 'decimal:2',
        'conversion_rate' => 'decimal:4',
    ];
}
