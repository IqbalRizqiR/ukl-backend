<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Environment
    |--------------------------------------------------------------------------
    |
    | Set to true for production, false for sandbox.
    |
    */
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Server Key
    |--------------------------------------------------------------------------
    */
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Client Key
    |--------------------------------------------------------------------------
    */
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Midtrans API URLs
    |--------------------------------------------------------------------------
    */
    'base_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com'
        : 'https://app.sandbox.midtrans.com',

    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/v1/transactions'
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions',

    /*
    |--------------------------------------------------------------------------
    | Snap Redirect
    |--------------------------------------------------------------------------
    |
    | Enable/disable 3DS for credit card payments.
    |
    */
    'is_3ds' => env('MIDTRANS_IS_3DS', true),

    /*
    |--------------------------------------------------------------------------
    | Payment Expiry
    |--------------------------------------------------------------------------
    |
    | Time in minutes before unpaid orders expire.
    |
    */
    'payment_expiry_minutes' => env('MIDTRANS_PAYMENT_EXPIRY', 1440), // 24 hours

];
