<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'checkout/payment/*',
        'cflutter/notify',
        'user/paytm-notify',
        'user/razorpay-notify',
        'uflutter/notify',
        'user/ssl-notify',
        'user/deposit/*',
        'dflutter/notify',

        'domain-payment/success',
        'domain-payment/cancel',
        'domain-payment/webhook',

    ];
}
