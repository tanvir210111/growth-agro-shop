<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'title',
        'handle',
        'image',
        'banner_image',
        'description',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
