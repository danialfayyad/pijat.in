<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'xendit' => [
        'secret_key' => env('XENDIT_SECRET_KEY'),
        'webhook_token' => env('XENDIT_WEBHOOK_TOKEN', 'pijatin_webhook_secret_key'),
    ],

    'vapid' => [
        'public_key' => env('VAPID_PUBLIC_KEY', 'BABvODv5LBkv3E_oOkCsH6ntg108NeciREk2_RSES_QCZ9iKbM6b1XqcO628KjLgteCcqMzKFYKOOkutMQGiEys'),
        'private_key' => env('VAPID_PRIVATE_KEY', 'AXII_EIp1dBQ3_1h5W8pycYGiCoC1bVQ4E2GExeMdI0'),
    ],

];
