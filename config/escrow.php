<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auto-Complete Timeout
    |--------------------------------------------------------------------------
    |
    | Number of hours after estimated delivery before the escrow
    | automatically releases funds to the seller.
    | Default: 48 hours (2×24 jam)
    |
    */
    'auto_complete_hours' => env('ESCROW_AUTO_COMPLETE_HOURS', 48),

    /*
    |--------------------------------------------------------------------------
    | Service Fee Percentage
    |--------------------------------------------------------------------------
    |
    | Platform fee as a percentage of the product price.
    | Example: 5.0 means 5% service fee.
    |
    */
    'service_fee_percentage' => env('ESCROW_SERVICE_FEE_PERCENTAGE', 5.0),

    /*
    |--------------------------------------------------------------------------
    | Minimum Service Fee
    |--------------------------------------------------------------------------
    |
    | Minimum service fee in IDR.
    |
    */
    'minimum_service_fee' => env('ESCROW_MINIMUM_SERVICE_FEE', 1000),

    /*
    |--------------------------------------------------------------------------
    | Seller Shipping Deadline
    |--------------------------------------------------------------------------
    |
    | Number of hours a seller has to ship the item after payment.
    | If exceeded, the order may be auto-cancelled.
    |
    */
    'shipping_deadline_hours' => env('ESCROW_SHIPPING_DEADLINE_HOURS', 72), // 3 days

    /*
    |--------------------------------------------------------------------------
    | Withdrawal Settings
    |--------------------------------------------------------------------------
    */
    'withdrawal' => [
        'minimum_amount' => env('WITHDRAWAL_MINIMUM_AMOUNT', 10000),
        'fee' => env('WITHDRAWAL_FEE', 0),
        'processing_days' => env('WITHDRAWAL_PROCESSING_DAYS', 1),
    ],

];
