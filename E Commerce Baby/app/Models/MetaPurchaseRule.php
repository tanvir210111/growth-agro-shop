<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaPurchaseRule extends Model
{
    use HasFactory;

    protected $table = 'meta_purchase_rules';

    protected $fillable = [
        'rule_name',
        'priority',
        'condition_field',
        'operator',
        'condition_value',
        'condition_value_high',
        'action_mode',
        'delay_minutes',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'priority'      => 'integer',
            'delay_minutes' => 'integer',
            'is_active'     => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active rules ordered by priority.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('priority', 'asc');
    }
}
