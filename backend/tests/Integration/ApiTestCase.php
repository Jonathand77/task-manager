<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PDO;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;

abstract class ApiTestCase extends TestCase
{
    protected App $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = require __DIR__ . '/../../public/index.php';

        if (!$this->canConnectToDatabase()) {
            $this->markTestSkipped('Database not available for integration tests.');
        }
    }

    protected function jsonRequest(string $method, string $uri, array $payload = [], array $headers = []): array
    {
        $requestFactory = new ServerRequestFactory();
        $streamFactory = new StreamFactory();

        $request = $requestFactory->createServerRequest($method, $uri);
        $request = $request->withHeader('Content-Type', 'application/json');

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if (!empty($payload)) {
            $stream = $streamFactory->createStream(json_encode($payload));
            $request = $request->withBody($stream);
        }

        $response = $this->app->handle($request);
        $body = (string)$response->getBody();

        return [
            'status' => $response->getStatusCode(),
            'body' => $body,
            'json' => json_decode($body, true)
        ];
    }

    protected function canConnectToDatabase(): bool
    {
        try {
            $host = getenv('DB_HOST') ?: 'localhost';
            $port = getenv('DB_PORT') ?: '5432';
            $db = getenv('DB_NAME') ?: 'task_manager_db';
            $user = getenv('DB_USER') ?: 'task_manager_user';
            $pass = getenv('DB_PASSWORD') ?: '';
            $dsn = sprintf('pgsql:host=%s;dbname=%s;port=%s', $host, $db, $port);

            $pdo = new PDO($dsn, $user, $pass);
            $pdo->query('SELECT 1');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
