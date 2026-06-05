<?php
require_once ROOT . "/src/Core/envLoader.php";

loadEnv();
return [
    'Database' => [
        'host' => $_ENV['DB_HOST'],
        'port' => $_ENV['DB_PORT'],
        'name' => $_ENV['DB_NAME'],
        'user' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASSWORD'],
        'driver' => $_ENV['DB_DRIVER'],
        ],
    'JWT' => [
        'secretKey' => $_ENV['JWT_SECRET_KEY'],
        'algorithm' => 'HS256',
        'accessExpiry' => 666,
        'refreshExpiry' => 2629744
    ]
];