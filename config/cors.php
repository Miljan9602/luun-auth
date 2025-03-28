<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://127.0.0.1:9134', 'http://127.0.0.1:9135', 'http://localhost:9134', 'http://localhost:9135', 'https://localhost:5174', 'https://127.0.0.1:5174', 'https://dragonswap-auth-front-2b222aceb76b.herokuapp.com', 'https://dragon-swap-git-feature-xdrg-nima-enterprises.vercel.app', 'https://front-test.dragonswap.app', 'https://xdrg.dragonswap.app', 'https://front-test.luun.network/', 'http://front-test.luun.network/'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // Required when using credentials (cookies, sessions)
];
