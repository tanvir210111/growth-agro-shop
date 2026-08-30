<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'store_name' => Setting::get('store_name', 'Baby Fashion BD'),
            'store_phone' => Setting::get('store_phone', '01560-016740'),
            'store_email' => Setting::get('store_email', 'support@babyfashionbd.com'),
            'store_address' => Setting::get('store_address', 'Level 3, Block D, Bashundhara R/A, Dhaka-1229, Bangladesh'),
            'delivery_inside_dhaka' => Setting::get('delivery_inside_dhaka', '70'),
            'delivery_outside_dhaka' => Setting::get('delivery_outside_dhaka', '130'),
            'free_delivery_threshold' => Setting::get('free_delivery_threshold', '3000'),
            'currency_symbol' => Setting::get('currency_symbol', '৳'),
            'order_prefix' => Setting::get('order_prefix', 'BFB-'),
            'meta_title' => Setting::get('meta_title', 'Baby Fashion BD'),
            'meta_description' => Setting::get('meta_description', 'Premium Soft Baby & Toddler Outfits in Bangladesh'),
        ];

        return view('admin.pages.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', 'logo']);

        foreach ($data as $key => $val) {
            Setting::set($key, $val);
        }

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $file->move(public_path('images'), 'logo.png');
        }

        return back()->with('success', 'Store settings and delivery rates saved successfully.');
    }
}
