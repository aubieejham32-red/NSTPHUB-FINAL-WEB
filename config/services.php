<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | Existing Google Login
    |--------------------------------------------------------------------------
    |
    | Keep these credentials separate from the Gmail API.
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | NSTP HUB Gmail API
    |--------------------------------------------------------------------------
    |
    | Uses the second Google account for outgoing emails.
    |
    */

    'gmail_api' => [
        'client_id' => env('GMAIL_API_CLIENT_ID'),
        'client_secret' => env('GMAIL_API_CLIENT_SECRET'),
        'redirect' => env('GMAIL_API_REDIRECT_URI'),

        // Enable only while connecting the sender Gmail account.
        'setup_enabled' => env('GMAIL_API_SETUP_ENABLED', false),
    ],

];