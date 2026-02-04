<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService {
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public static function generateToken($userId): string {
        $key = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
        $payload = [
            'iss' => 'task-manager',
            'aud' => 'task-manager-api',
            'iat' => time(),
            'exp' => time() + (86400 * 7),
            'user_id' => $userId
        ];

        return JWT::encode($payload, $key, 'HS256');
    }

    public static function verifyToken(string $token) {
        try {
            $key = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            return $decoded;
        } catch (\Exception $e) {
            error_log('Token Error: ' . $e->getMessage());
            return null;
        }
    }
}
