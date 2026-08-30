<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    /**
     * Show Privacy Policy page
     */
    public function privacyPolicy()
    {
        $gs = DB::table('generalsettings')->first();
        return view('pages.privacy-policy', compact('gs'));
    }

    /**
     * Show Terms of Service page
     */
    public function termsOfService()
    {
        $gs = DB::table('generalsettings')->first();
        return view('pages.terms-of-service', compact('gs'));
    }

    /**
     * Show About Us page
     */
    public function aboutUs()
    {
        $gs = DB::table('generalsettings')->first();
        return view('pages.about-us', compact('gs'));
    }

    /**
     * Show Contact Us page
     */
    public function contactUs()
    {
        $gs = DB::table('generalsettings')->first();
        return view('pages.contact-us', compact('gs'));
    }

    /**
     * Data Deletion Instructions (Facebook App Review এর জন্য প্রয়োজন)
     */
    public function dataDeletion()
    {
        $gs = DB::table('generalsettings')->first();
        return view('pages.data-deletion', compact('gs'));
    }
}
