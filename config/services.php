<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'dientuconglinh' => [
        'url' => env('DIENTUCONGLINH_URL'),
        'woo_key' => env('DIENTUCONGLINH_WOO_KEY'),
        'woo_secret' => env('DIENTUCONGLINH_WOO_SECRET'),
        'wp_user' => env('DIENTUCONGLINH_WP_USER'),
        'wp_secret' => env('DIENTUCONGLINH_WP_SECRET')
    ],
    'telegram' => [
        'token' => env('TELE_TOKEN'),
        'group_id' => env('TELE_ADMIN_DEPOSIT_GROUP'),
    ],
    'sms' => [
        'url' => env("SMS_URL", ''),
        'key' => env("SMS_KEY", ''),
        'type' => env("SMS_TYPE", ''),
        'name' => env("SMS_NAME", ''),
    ],
    'azprodotnet' => [
        'url' => env('AZPRODOTNET_URL')
    ],
    'facebook' => [
        'client_id' => env("FACEBOOK_ID", ""),
        'client_secret' => env("FACEBOOK_SECRET", ""),
        'redirect' => env("FACEBOOK_CALLBACK_URL", ""),
    ],
    'google' => [
        'client_id' => env("GOOGLE_ID", ""),
        'client_secret' => env("GOOGLE_SECRET", ""),
        'redirect' => env("GOOGLE_CALLBACK_URL", ""),
    ],
    'esms' => [
        'url' => env('ESMS_URL', 'http://rest.esms.vn/MainService.svc/json/'),
        'api_key' => env('ESMS_API_KEY', ''),
        'api_secret' => env('ESMS_API_SECRET', '')
    ],
    'activation' => [
        'url' => env('VIETTEL_ACTIVATION_URL'),
        'partner_code' => env('VIETTEL_ACTIVATION_PARTNER_CODE'),
        'secret' => env('VIETTEL_ACTIVATION_SECRET'),
        'tenant_code' => env('VIETTEL_TENANT'),
    ]
];
