<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173', 'https://tavosvetainė.lt'],
    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false, // Bearer tokenams -> false
];
