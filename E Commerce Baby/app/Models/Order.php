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
    ];

    protected $casts = [
        'delivery_charge' => 'float',
        'subtotal' => 'float',
        'discount' => 'float',
        'total_amount' => 'float',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function generateInvoiceNo(): string
    {
        $prefix = 'BFB-';
        $random = strtoupper(substr(uniqid(), -5));
        $date = date('ymd');
        return $prefix . $date . '-' . $random;
    }
}
