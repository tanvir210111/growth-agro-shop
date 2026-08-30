<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'link',
        'button_text',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'status' => 'boolean',
    ];
}
