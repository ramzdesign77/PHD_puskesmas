<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['*'], // Ubah dari [], null, atau domain spesifik menjadi ['*'] untuk sementara
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => false,
];
