<?php

return [
    /**
     * UddoktaPay API Key
     * Get it from your UddoktaPay Panel
     */
    'api_key' => env('UDDOKTAPAY_API_KEY'),

    /**
     * UddoktaPay API URL
     * Default: https://checkout.uddoktapay.com/api/checkout-v2
     */
    'api_url' => env('UDDOKTAPAY_API_URL', 'https://checkout.uddoktapay.com/api/checkout-v2'),
];