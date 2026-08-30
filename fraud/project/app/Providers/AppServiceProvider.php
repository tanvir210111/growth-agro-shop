<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\GeneralSettings;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App;
use App\Models\Font;
use App\Models\Language;
use App\Models\SocialLink;
use App\Models\View;
use Session;
use Illuminate\Foundation\AliasLoader;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // UddoktaPay সার্ভিস প্রোভাইডার ম্যানুয়ালি রেজিস্টার করা হচ্ছে
        if (class_exists(\UddoktaPay\LaravelSDK\UddoktaPayServiceProvider::class)) {
            $this->app->register(\UddoktaPay\LaravelSDK\UddoktaPayServiceProvider::class);
            
            // কন্ট্রোলারে সরাসরি 'UddoktaPay' নাম ব্যবহার করার জন্য Alias যোগ করা হলো
            $loader = AliasLoader::getInstance();
            $loader->alias('UddoktaPay', \UddoktaPay\LaravelSDK\Facades\UddoktaPay::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer(['*'], function ($view) {

            if (Session::has('language')) {
                if (\Request::is('admin/*')) {
                    $data = DB::table('admin_languages')->where('is_default', '=', 1)->first();
                    App::setlocale($data->name);
                } else {
                    $data = DB::table('languages')->find(Session::get('language'));
                    App::setlocale($data->name);
                }
            } else {
                if (\Request::is('admin/*')) {
                    $a_lang = DB::table('admin_languages')->where('is_default', '=', 1)->first();
                    App::setlocale($a_lang->name);
                } else {
                    $language = DB::table('languages')->where('is_default', '=', 1)->first();
                    App::setlocale($language->name);
                }
            }


            $gs = GeneralSettings::find(1);
            $seo = DB::table('seotools')->first();
            if (session()->has('language')) {
                $default_language = Language::find(session()->get('language'));
            } else {

                $default_language = Language::where('is_default', 1)->first();
            }
            $social_links = SocialLink::orderBy('id', 'desc')->get();
            $languages    = Language::orderBy('id', 'desc')->get();


            $top_views    = DB::table('views')
                ->select(DB::raw('count(*) as top_viwes, post_id'))
                ->groupBy('post_id')
                ->orderBy('top_viwes', 'desc')
                ->take(6)
                ->get();

            $default_font = Font::where('is_default', 1)->first();
            $tags = explode(',', $gs->tags);
            $view->with('gs', $gs);
            $view->with('seo', $seo);

            $view->with('social_links', $social_links);
            $view->with('top_views', $top_views);
            $view->with('tags', $tags);
            $view->with('default_language', $default_language);
            $view->with('default_font', $default_font);
            $view->with('languages', $languages);
        });
    }
}