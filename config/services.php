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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    'turnstile' => [
        'key' => env('TURNSTILE_SITE_KEY'),
        'secret' => env('TURNSTILE_SECRET_KEY'),
    ],

    'iprogsms' => [
        'api_token' => env('IPROG_SMS_API_TOKEN', env('IPROGSMS_API_TOKEN', env('SMS_API_TOKEN', ''))),
        'url' => env('IPROG_SMS_URL', 'https://www.iprogsms.com/api/v1/sms_messages'),
        'enabled' => env('SMS_ENABLED', true),
    ],

    'recommender' => [
        'url' => env('ML_RECOMMENDER_URL', 'http://127.0.0.1:5002'),
        'key' => env('ML_API_KEY'),
        'timeout' => env('ML_REQUEST_TIMEOUT_SECONDS', 30),
        'evaluation_path' => env('ML_EVALUATION_PATH', '/api/metrics'),
    ],

];
