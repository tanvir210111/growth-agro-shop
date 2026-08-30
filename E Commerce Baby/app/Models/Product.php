<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'sku',
        'category_id',
        'category_handle',
        'regular_price',
        'sale_price',
        'cost_price',
        'stock',
        'featured_image',
        'hover_image',
        'gallery_images',
        'sizes',
        'short_description',
        'description',
        'is_featured',
        'is_new_arrival',
        'is_bestseller',
        'is_clearance',
        'status',
    ];

    protected $casts = [
        'regular_price' => 'float',
        'sale_price' => 'float',
        'cost_price' => 'float',
        'stock' => 'integer',
        'gallery_images' => 'array',
        'sizes' => 'array',
        'is_featured' => 'boolean',
        'is_new_arrival' => 'boolean',
        'is_bestseller' => 'boolean',
        'is_clearance' => 'boolean',
        'status' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getDiscountPercentAttribute(): int
    {
        if ($this->regular_price > $this->sale_price && $this->regular_price > 0) {
            return (int) round((($this->regular_price - $this->sale_price) / $this->regular_price) * 100);
        }
        return 0;
    }
}
