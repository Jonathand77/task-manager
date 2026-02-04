<?php

namespace App\Controllers;

use Phalcon\Mvc\Controller;
use App\Models\User;
use Firebase\JWT\JWT;

class AuthController extends Controller
{
    /**
     * Registrar nuevo usuario
     */
    public function registerAction()
    {
        if (!$this->request->isPost()) {
            return $this->sendError('Method not allowed', 405);
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $name = $this->request->getPost('name', 'string', '');

        // Validaciones
        if (empty($email) || empty($password)) {
            return $this->sendError('Email and password are required', 400);
        }

        if (strlen($password) < 6) {
            return $this->sendError('Password must be at least 6 characters', 400);
        }

        // Verificar si usuario ya existe
        $existingUser = User::findFirst([
            'conditions' => 'email = ?1',
            'bind'       => [1 => $email]
        ]);

        if ($existingUser) {
            return $this->sendError('User already exists', 409);
        }

        try {
            // Crear usuario
            $user = new User();
            $user->email = $email;
            $user->password = password_hash($password, PASSWORD_BCRYPT);
            $user->name = $name ?: explode('@', $email)[0];

            if (!$user->save()) {
                return $this->sendError('Error creating user', 500);
            }

            return $this->sendResponse([
                'user' => [
                    'id'    => $user->id,
                    'email' => $user->email,
                    'name'  => $user->name,
                ]
            ], 'User registered successfully', 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Iniciar sesión
     */
    public function loginAction()
    {
        if (!$this->request->isPost()) {
            return $this->sendError('Method not allowed', 405);
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Validaciones
        if (empty($email) || empty($password)) {
            return $this->sendError('Email and password are required', 400);
        }

        try {
            // Buscar usuario
            $user = User::findFirst([
                'conditions' => 'email = ?1',
                'bind'       => [1 => $email]
            ]);

            if (!$user) {
                return $this->sendError('Invalid credentials', 401);
            }

            // Verificar contraseña
            if (!password_verify($password, $user->password)) {
                return $this->sendError('Invalid credentials', 401);
            }

            // Generar JWT
            $token = $this->generateJWT($user);

            return $this->sendResponse([
                'token' => $token,
                'user'  => [
                    'id'    => $user->id,
                    'email' => $user->email,
                    'name'  => $user->name,
                ]
            ], 'Login successful');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Generar JWT
     */
    private function generateJWT($user)
    {
        $secret = $this->di->get('config')['jwt']['secret'];
        $payload = [
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24 * 7), // 7 días
            'sub' => $user->id,
            'email' => $user->email,
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }

    /**
     * Enviar respuesta de éxito
     */
    protected function sendResponse($data, $message = 'Success', $code = 200)
    {
        $this->response->setStatusCode($code);
        $this->response->setContentType('application/json');
        $this->response->setContent(json_encode([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ]));

        return $this->response;
    }

    /**
     * Enviar respuesta de error
     */
    protected function sendError($message, $code = 400)
    {
        $this->response->setStatusCode($code);
        $this->response->setContentType('application/json');
        $this->response->setContent(json_encode([
            'success' => false,
            'message' => $message
        ]));

        return $this->response;
    }
}
