<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "ixopay" (Areeba IXOPAY gateway), "mpgs" (Areeba ePayment / MPGS
    | hosted checkout). The default remains "ixopay" for backwards compatibility.
    |
    */

    'driver' => env('AREEBA_DRIVER', 'ixopay'),

    /*
    |--------------------------------------------------------------------------
    | Areeba IXOPAY Gateway (default driver)
    |--------------------------------------------------------------------------
    */

    'api_key' => env('AREEBA_API_KEY'),
    'username' => env('AREEBA_USERNAME'),
    'password' => env('AREEBA_PASSWORD'),
    'base_url' => env('AREEBA_BASE_URL', 'https://gateway.areebapayment.com/api/v3'),
    'language' => env('AREEBA_LANGUAGE', 'ar'),
    'currency' => env('AREEBA_CURRENCY', 'IQD'),
    'transaction_prefix' => env('AREEBA_TRANSACTION_PREFIX', 'MYAPP-'),

    'redirect_url' => [
        'success'  => env('AREEBA_SUCCESS_REDIRECT_URL', ''),
        'error'    => env('AREEBA_ERROR_REDIRECT_URL', ''),
        'cancel'   => env('AREEBA_CANCEL_REDIRECT_URL', ''),
        'callback' => env('AREEBA_CALLBACK_REDIRECT_URL', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Areeba MPGS / ePayment Gateway
    |--------------------------------------------------------------------------
    |
    | Settings for the hosted checkout (https://epayment.areeba.com).
    | Namespaced under "mpgs" so they do not collide with the IXOPAY keys above.
    |
    */

    'mpgs' => [
        'base_url'         => env('AREEBA_MPGS_BASE_URL', 'https://epayment.areeba.com'),
        'api_version'      => env('AREEBA_MPGS_API_VERSION', '100'),
        'merchant_id'      => env('AREEBA_MPGS_MERCHANT_ID'),
        'username'         => env('AREEBA_MPGS_USERNAME'),
        'password'         => env('AREEBA_MPGS_PASSWORD'),
        'currency'         => env('AREEBA_MPGS_CURRENCY', 'USD'),
        'checkout_version' => env('AREEBA_MPGS_CHECKOUT_VERSION', '1.0.0'),

        'redirect_url' => [
            'return' => env('AREEBA_MPGS_RETURN_REDIRECT_URL', ''),
            'cancel'  => env('AREEBA_MPGS_CANCEL_REDIRECT_URL', ''),
            'timeout' => env('AREEBA_MPGS_TIMEOUT_REDIRECT_URL', ''),
        ],
    ],
];
