<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:8000',
        'http://localhost:8080',
        'https://homielaundry.netlify.app',
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost:2209',
        'http://172.168.0.19:8000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['X-CSRF-TOKEN', 'X-XSRF-TOKEN'],

    'max_age' => 0,

    'supports_credentials' => true,

];
