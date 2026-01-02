<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'register', '/broadcasting/auth'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // Development angular routes
        'http://localhost:4200',
        // Production Routes
        'http://' . env('OSSE_HOST', 'localhost'),
        'https://' . env('OSSE_HOST', 'localhost'),
        env('OSSE_URL_SERVER', "http://localhost")
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 600,

    'supports_credentials' => true,

];
