<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainExtension extends Model
{
    protected $guarded = [];
    
    // ডাটাবেজ টেবিল নাম যদি 'domain_extensions' হয় তবে লারাভেল অটোমেটিক ধরে নিবে।
    // তবুও নিশ্চিত হওয়ার জন্য:
    protected $table = 'domain_extensions';
}