<?php
return [
    'name' => env('APP_NAME', 'BlueHR Production'),
    'url' => env('APP_URL', 'http://localhost'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', 'false') === 'true',
    'key' => env('APP_KEY', 'change_this_key'),
    'session_name' => 'bluehr_session',
];
