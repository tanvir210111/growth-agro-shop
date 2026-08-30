<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Counter extends Model {
    protected $fillable = ['title', 'count_value', 'icon'];
}