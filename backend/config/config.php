<?php

use Dotenv\Dotenv;

// Cargar archivo .env
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

return [
    'app' => [
        'debug'  => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'env'    => $_ENV['APP_ENV'] ?? 'production',
        'url'    => $_ENV['APP_URL'] ?? 'http://localhost:8000',
    ],
    'database' => [
        'adapter'  => 'postgresql',
        'host'     => $_ENV['DB_HOST'] ?? 'localhost',
        'port'     => $_ENV['DB_PORT'] ?? 5432,
        'username' => $_ENV['DB_USER'] ?? 'postgres',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'dbname'   => $_ENV['DB_NAME'] ?? 'task_manager_db',
        'charset'  => 'utf8',
    ],
    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'] ?? 'change-me-in-production',
    ],
    'cors' => [
        'allowed_origins' => explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? '*'),
    ],
];
