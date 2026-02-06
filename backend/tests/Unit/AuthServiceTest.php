<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\AuthService;

class AuthServiceTest extends TestCase
{
    public function testHashAndVerifyPassword(): void
    {
        $password = 'P@ssw0rd!';
        $hash = AuthService::hashPassword($password);

        $this->assertNotEmpty($hash);
        $this->assertTrue(AuthService::verifyPassword($password, $hash));
        $this->assertFalse(AuthService::verifyPassword('wrong', $hash));
    }

    public function testGenerateAndVerifyToken(): void
    {
        $userId = 123;
        $token = AuthService::generateToken($userId);

        $this->assertNotEmpty($token);

        $decoded = AuthService::verifyToken($token);

        $this->assertNotNull($decoded);
        $this->assertSame($userId, $decoded->user_id ?? null);
    }
}
