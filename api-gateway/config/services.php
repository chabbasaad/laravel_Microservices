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

    'microservices' => [
        'auth' => [
            'base_url' => env('AUTH_SERVICE_URL', 'http://localhost:8001'),
            'prefix' => 'auth',
            'routes' => [
                'health' => 'GET /health',
                'login' => 'POST /login',
                'register' => 'POST /api/register',
                'validate' => 'GET /api/user'
            ]
        ],
        'users' => [
            'base_url' => env('USER_SERVICE_URL', 'http://localhost:8002'),
            'prefix' => 'users',
            'routes' => [
                'index' => 'GET /',
                'store' => 'POST /',
                'show' => 'GET /{id}',
                'update' => 'PUT /{id}',
                'delete' => 'DELETE /{id}',
                'by-email' => 'GET /by-email/{email}'
            ]
        ],
        'events' => [
            'base_url' => env('EVENT_SERVICE_URL', 'http://localhost:8003'),
            'prefix' => 'events',
            'routes' => [
                'health' => 'GET /health',
                'index' => 'GET /',
                'store' => 'POST /',
                'show' => 'GET /{id}',
                'update' => 'PUT /{id}',
                'delete' => 'DELETE /{id}',
                'public-events' => 'GET /public'
            ]
        ],
        'tickets' => [
            'base_url' => env('TICKET_SERVICE_URL', 'http://localhost:8004'),
            'prefix' => 'tickets',
            'routes' => [
                'health' => 'GET /health',
                'purchase' => 'POST /purchase',
                'user-tickets' => 'GET /user/{userId}',
                'show' => 'GET /{ticketId}',
                'validate' => 'POST /{ticketId}/validate',
                'cancel' => 'POST /{ticketId}/cancel',
                'list' => 'GET /'
            ]
        ],
        'notifications' => [
            'base_url' => env('NOTIFICATION_SERVICE_URL', 'http://localhost:8005'),
            'prefix' => 'notifications',
            'routes' => [
                'health' => 'GET /health',
                'purchase' => 'POST /purchase',
                'cancellation' => 'POST /cancellation',
                'test-queue' => 'GET /test-queue'
            ]
        ],
    ]
];
