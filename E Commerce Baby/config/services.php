<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | BD Courier API (Phase 5B Fraud Detection)
    |--------------------------------------------------------------------------
    | Used exclusively by BdCourierService on the server side.
    | Never expose this key in frontend JS, HTML, API responses, or logs.
    */
    'bdcourier' => [
        'key'     => env('BDCOURIER_API_KEY', env('BD_COURIER_API_KEY')),
        'api_key' => env('BDCOURIER_API_KEY', env('BD_COURIER_API_KEY')),
        'timeout' => env('BDCOURIER_TIMEOUT_SECONDS', 8),
        'mock'    => env('MOCK_BD_COURIER', false),
    ],

];
