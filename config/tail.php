<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tail Client Token
    |--------------------------------------------------------------------------
    */
    'client_token' => env('TAIL_CLIENT_TOKEN', null),

    /*
    |--------------------------------------------------------------------------
    | APM Enabled
    |--------------------------------------------------------------------------
    */
    'apm_enabled' => env('TAIL_APM_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | APM Enabled
    |--------------------------------------------------------------------------
    */
    'logs_enabled' => env('TAIL_LOGS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Service Name
    |--------------------------------------------------------------------------
    |
    | Name used to identify this service.
    |
    */
    'service_name' => env('TAIL_SERVICE_NAME', config('app.name')),

    /*
    |--------------------------------------------------------------------------
    | HTTP request headers to be dropped from APM transactions.
    |--------------------------------------------------------------------------
    */
    'drop_request_headers' => [
        'authorization',
        'php-auth-pw',
    ],

    /*
    |--------------------------------------------------------------------------
    | Enabled APM trackers
    |--------------------------------------------------------------------------
    */
    'apm_trackers' => [
        Tail\Laravel\Trackers\FrameworkStartupTracker::class,
        Tail\Laravel\Trackers\FrameworkBootTracker::class,
        Tail\Laravel\Trackers\HttpTracker::class,
        Tail\Laravel\Trackers\JobTracker::class,
        Tail\Laravel\Trackers\QueryTracker::class,

        // Track artisan commands
        // Tail\Laravel\Trackers\ArtisanTracker::class,
    ],
];
