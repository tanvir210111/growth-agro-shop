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
    ];

    protected $casts = [
        'delivery_charge' => 'float',
        'subtotal' => 'float',
        'discount' => 'float',
        'total_amount' => 'float',
        'visitor_id' => 'integer',
        'session_id' => 'integer',
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
