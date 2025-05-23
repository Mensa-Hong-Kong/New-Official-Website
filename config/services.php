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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'twilio' => [
        'sid' => env('TWILIO_AUTH_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'whatsapp' => [
            'from' => env('TWILIO_WHATSAPP_FROM'),
            'templateIDs' => [
                'verificationCode' => env('TWILIO_WHATSAPP_VERIFICATION_CODE_TEMPLATE_ID'),
                'newPassword' => env('TWILIO_WHATSAPP_NEW_PASSWORD_TEMPLATE_ID'),
            ],
        ],
    ],

    'stripe' => [
        'keys' => [
            'public' => env('STRIPE_PUBLIC_KEY'),
            'secret' => env('STRIPE_SECRET_KEY'),
            'webhook' => env('STRIPE_WEBHOOK_KEY'),
        ],
    ],

];
