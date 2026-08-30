<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PortfolioCategory extends Model {
    protected $table = 'portfolio_categories'; // টেবিলের নাম
    public $timestamps = false; // আপনার টেবিলে created_at/updated_at নেই
    protected $fillable = ['name', 'slug'];

    public function portfolios() {
        return $this->hasMany(Portfolio::class, 'category_id');
    }
}