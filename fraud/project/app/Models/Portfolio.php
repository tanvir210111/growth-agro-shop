<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model {
    protected $table = 'portfolios'; //
    protected $fillable = ['category_id', 'title', 'photo', 'url']; //

    public function category() {
        return $this->belongsTo(PortfolioCategory::class, 'category_id')->withDefault();
    }
}