<?php

declare(strict_types=1);

namespace Tests\Integration;

class AuthTasksTest extends ApiTestCase
{
    public function testAuthAndTasksFlow(): void
    {
        $email = 'test_' . uniqid() . '@example.com';
        $password = 'P@ssw0rd!';

        $register = $this->jsonRequest('POST', '/api/register', [
            'name' => 'Test User',
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertSame(201, $register['status']);
        $this->assertArrayHasKey('token', $register['json']);

        $login = $this->jsonRequest('POST', '/api/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $this->assertSame(200, $login['status']);
        $this->assertArrayHasKey('token', $login['json']);

        $token = $login['json']['token'];
        $authHeader = ['Authorization' => 'Bearer ' . $token];

        $create = $this->jsonRequest('POST', '/api/tasks', [
            'title' => 'Mi primera tarea',
            'description' => 'Descripción opcional',
            'status' => 'pending'
        ], $authHeader);

        $this->assertSame(201, $create['status']);
        $this->assertArrayHasKey('id', $create['json']);

        $taskId = $create['json']['id'];

        $list = $this->jsonRequest('GET', '/api/tasks', [], $authHeader);
        $this->assertSame(200, $list['status']);
        $this->assertArrayHasKey('data', $list['json']);

        $update = $this->jsonRequest('PUT', '/api/tasks/' . $taskId, [
            'status' => 'in_progress'
        ], $authHeader);

        $this->assertSame(200, $update['status']);

        $delete = $this->jsonRequest('DELETE', '/api/tasks/' . $taskId, [], $authHeader);
        $this->assertSame(200, $delete['status']);
    }
}
