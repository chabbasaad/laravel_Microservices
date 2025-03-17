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

    /*
    |--------------------------------------------------------------------------
    | Microservices Configuration
    |--------------------------------------------------------------------------
    */
    'events' => [
        'base_url' =>  env('EVENT_SERVICE_URL', 'http://localhost:8003'),
        'routes' => [
            'show' => '/api/{id}',
            'update' => '/api/{id}',
            'list' => '/api',
            'create' => '/api',
            'delete' => '/api/{id}'
        ]
    ],

    'auth' => [
        'base_url' => env('AUTH_SERVICE_URL', 'http://localhost:8001'),
        'routes' => [
            'validate' => '/api/user',
            'login' => '/api/login',
            'register' => '/api/register'
        ]
    ],

    'notifications' => [
        'base_url' => env('NOTIFICATION_SERVICE_URL', 'http://localhost:8005'),
        'routes' => [
            'purchase' => '/api/purchase',
            'cancellation' => '/api/cancellation',
            'test' => '/api/test-queue'
        ]
    ],

];
