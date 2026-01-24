<?php

return [
    'keys' => [
        'public' => env('STRIPE_PUBLIC_KEY'),
        'secret' => env('STRIPE_SECRET_KEY'),
        'webhook' => env('STRIPE_WEBHOOK_KEY'),
    ],
    'lifetime' => [
        'transaction' => env('STRIPE_TRANSACTION_LIFETIME', 120),
        'webhook' => env('STRIPE_WEBHOOK_LIFETIME', 300),
    ],
    'currency' => 'hkd',
    /*
     * 4 is hkd minimum amount
     * because Stripe's not support all currency settlement yet,
     * so, need to define minimum amount here.
     */
    'minimum_amount' => 4,
];
