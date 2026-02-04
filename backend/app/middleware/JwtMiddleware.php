<?php

namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;
use App\Services\AuthService;

class JwtMiddleware implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader)) {
            $res = new Response();
            $res->getBody()->write(json_encode(['error' => 'Missing authorization token']));
            return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $parts = explode(' ', $authHeader);
        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            $res = new Response();
            $res->getBody()->write(json_encode(['error' => 'Invalid authorization header']));
            return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $decoded = AuthService::verifyToken($parts[1]);
        if (!$decoded) {
            $res = new Response();
            $res->getBody()->write(json_encode(['error' => 'Invalid or expired token']));
            return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $request = $request->withAttribute('user', $decoded);
        return $handler->handle($request);
    }
}
