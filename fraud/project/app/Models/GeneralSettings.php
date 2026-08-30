<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSettings extends Model
{
    protected $fillable = [
        'logo',
        'footer_logo',
        'photo_card',
        'photo_card_logo',
        'photo_card_ads',
        'favicon',
        'loader',
        'admin_loader',
        'title',
        'theme_color',
        'footer_color',
        'tawk_to',
        'is_talkto',
        'is_capcha',
		'is_notice',
		'is_live_tv',
		'is_pool_on',
'footer_details',
'uddoktapay_api_key', 
        'uddoktapay_api_url',
		
        'sms_api_key', 
        'sms_sender_id',
		'phone',
		'address',
		
		'bd_courier_api_key', // এটি নতুন যোগ করুন
		
		
			'facebook_page_link',
			'is_facebook',
		'is_national',
		
		
		
		'email',
        'captcha_site_key',
        'captcha_secret_key',
        'disqus',
        'is_disqus',
        'time_zone',
        'copyright_text',
        'copyright_color',
        'footer_text',
        'tags',
        'error_photo',
        'error_title',
        'error_text',
        'driver',
        'smtp_host',
        'smtp_port',
        'email_encryption',
        'smtp_user',
        'smtp_pass',
        'from_email',
        'from_name',
        'is_smtp',
		'header1_728',
'header2_728',
'header3_728',
'header4_728',
'adsense_code',
'search_console',
'homepageads1_970',
'homepageads2_970',
'homepageads3_970',
'homepageads4_970',
'sidebar_ads',
'horizontal_adds1',
'live_tv',
'notice_text',
'adsense_code',
'search_console',


		'lazy_baner',
		'og_baner',
        'is_verification_email',
        'version'
    ];
    protected $table    = 'generalsettings';
    public $timestamps  = false;
}
