<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'register'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // ganti port sesuai FE
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];

