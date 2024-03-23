<?php

return [
    'providers' => [
        'onepay' => [
            'url' => env('ONEPAY_URL', 'https://mtf.onepay.vn/paygate/vpcpay.op'),
            'merchant_id' => env('ONEPAY_MERCHANT_ID', 'TESTONEPAY30'),
            'access_code' => env('ONEPAY_ACCESS_CODE', '6BEB2566'),
            'hash_key' => env('ONEPAY_HASH_KEY', '6D0870CDE5F24F34F3915FB0045120D6'),
            'installment_merchant_id' => env('ONEPAY_INSTALLMENT_MERCHANT_ID', 'TESTTRAGOP'),
            'installment_access_code' => env('ONEPAY_INSTALLMENT_ACCESS_CODE', 'D51C5CD6'),
            'installment_hash_key' => env('ONEPAY_INSTALLMENT_HASH_KEY', 'EB1B7F75EBB2FAABD6763FC37A3628AF'),
        ],
        'viettelmoney' => [
            'url' => env('VIETTEL_URL', ''),
            'merchant_code' => env('VIETTEL_MERCHANT_CODE', ''),
            'access_code' => env('VIETTEL_ACCESS_CODE', ''),
            'key' => env('VIETTEL_HASH_KEY', ''),
            'version' => env('VIETTEL_VERSION', ''),
        ],
        'vnpay' => [
            'url' => env('VNPAY_URL', ''),
            'vnp_TmnCode' => env('VNPAY_TMNCODE', ''),
            'vnp_HashSecret' => env('VNPAY_HASHSECRET', ''),
            'version' => env('VNPAY_VERSION', ''),
        ]

    ],
    'provider' => 'onepay',
];