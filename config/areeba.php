<?php

return [
    'api_key' => env('AREEBA_API_KEY', 'TESTKEYIQ1100000101'),
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
];