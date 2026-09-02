<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'invoice_no',
        'customer_name',
        'customer_phone',
        'customer_address',
        'city_type',
        'delivery_charge',
        'subtotal',
        'discount',
        'total_amount',
        'payment_method',
        'status',
        'is_new',
        'courier_name',
        'note',
        'visitor_id',
        'session_id',
        'source_type',
        'landing_page',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'referrer_domain',
        'click_id',
        'device_type',
        'ip_address',
        // Fraud detection fields (Phase 5B)
        'fraud_score',
        'fraud_level',
        'fraud_reasons',
        'courier_success_rate',
        'courier_total_orders',
        'courier_delivered',
        'courier_cancelled',
        'courier_checked_at',
    ];

    protected $casts = [
        'is_new'               => 'boolean',
        'delivery_charge'      => 'float',
        'subtotal'             => 'float',
        'discount'             => 'float',
        'total_amount'         => 'float',
        'visitor_id'           => 'integer',
        'session_id'           => 'integer',
        // Fraud detection casts (Phase 5B)
        'fraud_score'          => 'integer',
        'fraud_reasons'        => 'array',
        'courier_success_rate' => 'float',
        'courier_total_orders' => 'integer',
        'courier_delivered'    => 'integer',
        'courier_cancelled'    => 'integer',
        'courier_checked_at'   => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function visitor()
    {
        return $this->belongsTo(TrackingVisitor::class, 'visitor_id');
    }

    public function session()
    {
        return $this->belongsTo(TrackingSession::class, 'session_id');
    }

    public static function generateInvoiceNo(): string
    {
        $prefix = 'BFB-';
        $random = strtoupper(substr(uniqid(), -5));
        $date = date('ymd');
        return $prefix . $date . '-' . $random;
    }
}
