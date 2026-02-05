<?php

namespace App\WebSocket;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use App\Services\AuthService;

class TaskWebSocketHandler implements MessageComponentInterface
{
    protected \SplObjectStorage $clients;
    protected array $userConnections = [];

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $conn->userId = null;
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $payload = json_decode($msg, true);

        if (!is_array($payload)) {
            $this->send($from, 'error', ['message' => 'Invalid message format']);
            return;
        }

        $event = $payload['event'] ?? null;
        $data = $payload['data'] ?? [];

        if ($event === 'auth') {
            $this->handleAuth($from, $data);
            return;
        }

        if (empty($from->userId)) {
            $this->send($from, 'error', ['message' => 'Unauthenticated']);
            return;
        }

        if (empty($event)) {
            $this->send($from, 'error', ['message' => 'Missing event']);
            return;
        }

        $this->broadcast($event, $data, $from);
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $this->removeUserConnection($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->send($conn, 'error', ['message' => $e->getMessage()]);
        $conn->close();
    }

    protected function handleAuth(ConnectionInterface $conn, array $data): void
    {
        $token = $data['token'] ?? null;
        $userId = $data['userId'] ?? null;

        if (empty($token)) {
            $this->send($conn, 'auth.error', ['message' => 'Missing token']);
            $conn->close();
            return;
        }

        $decoded = AuthService::verifyToken($token);
        $tokenUserId = $decoded->user_id ?? null;

        if (!$decoded || !$tokenUserId) {
            $this->send($conn, 'auth.error', ['message' => 'Invalid token']);
            $conn->close();
            return;
        }

        if (!empty($userId) && (int)$userId !== (int)$tokenUserId) {
            $this->send($conn, 'auth.error', ['message' => 'Token does not match user']);
            $conn->close();
            return;
        }

        $conn->userId = (int)$tokenUserId;
        $this->addUserConnection($conn);
        $this->send($conn, 'auth.ok', ['userId' => $conn->userId]);
    }

    protected function addUserConnection(ConnectionInterface $conn): void
    {
        $userId = $conn->userId;
        if (empty($userId)) {
            return;
        }

        if (!isset($this->userConnections[$userId])) {
            $this->userConnections[$userId] = new \SplObjectStorage();
        }

        $this->userConnections[$userId]->attach($conn);
    }

    protected function removeUserConnection(ConnectionInterface $conn): void
    {
        $userId = $conn->userId;
        if (empty($userId) || !isset($this->userConnections[$userId])) {
            return;
        }

        $this->userConnections[$userId]->detach($conn);
        if ($this->userConnections[$userId]->count() === 0) {
            unset($this->userConnections[$userId]);
        }
    }

    protected function broadcast(string $event, array $data, ?ConnectionInterface $exclude = null): void
    {
        $message = json_encode(['event' => $event, 'data' => $data]);

        foreach ($this->clients as $client) {
            if (!empty($client->userId) && $client !== $exclude) {
                $client->send($message);
            }
        }
    }

    protected function send(ConnectionInterface $conn, string $event, array $data): void
    {
        $conn->send(json_encode(['event' => $event, 'data' => $data]));
    }
}
