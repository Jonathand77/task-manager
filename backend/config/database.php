<?php

use Phalcon\Db\Adapter\Pdo\Postgresql;

// Cargar variables de entorno
$env = parse_ini_file('.env', true);

return [
    'database' => [
        'default' => [
            'adapter'  => Postgresql::class,
            'host'     => $env['DB_HOST'] ?? 'postgres',
            'port'     => $env['DB_PORT'] ?? 5432,
            'username' => $env['DB_USER'] ?? 'task_manager_user',
            'password' => $env['DB_PASSWORD'] ?? '',
            'dbname'   => $env['DB_NAME'] ?? 'task_manager_db',
            'charset'  => 'utf8',
        ]
    ]
];
