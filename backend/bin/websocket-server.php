#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSocket\TaskWebSocketHandler;
use Dotenv\Dotenv;

// Load environment variables (root of backend)
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$port = $_ENV['WEBSOCKET_PORT'] ?? 8080;
$host = $_ENV['WEBSOCKET_HOST'] ?? '0.0.0.0';

echo "Iniciando servidor WebSocket en {$host}:{$port}\n";

try {
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new TaskWebSocketHandler()
            )
        ),
        $port,
        $host
    );

    $server->run();
} catch (\Exception $e) {
    echo "Error al iniciar servidor WebSocket: " . $e->getMessage() . "\n";
    exit(1);
}
