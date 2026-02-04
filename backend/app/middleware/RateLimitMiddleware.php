<?php

namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

class RateLimitMiddleware implements MiddlewareInterface {
    private array $requestLog = [];
    private int $maxRequests = 100;
    private int $windowSeconds = 60;

    public function __construct(int $maxRequests = 100, int $windowSeconds = 60) {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
    }

    /**
     * Process rate limiting
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        // Get identifier (use user ID if authenticated, otherwise IP)
        $identifier = $this->getIdentifier($request);
        $now = time();

        // Clean old entries
        if (!isset($this->requestLog[$identifier])) {
            $this->requestLog[$identifier] = [];
        }

        $this->requestLog[$identifier] = array_filter(
            $this->requestLog[$identifier],
            fn($timestamp) => ($now - $timestamp) < $this->windowSeconds
        );

        // Check if limit exceeded
        if (count($this->requestLog[$identifier]) >= $this->maxRequests) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'error' => 'Rate limit exceeded',
                'message' => "Too many requests. Max {$this->maxRequests} requests per {$this->windowSeconds} seconds"
            ]));
            return $response->withStatus(429)->withHeader('Content-Type', 'application/json')
                           ->withHeader('X-RateLimit-Limit', (string)$this->maxRequests)
                           ->withHeader('X-RateLimit-Remaining', '0')
                           ->withHeader('X-RateLimit-Reset', (string)($now + $this->windowSeconds));
        }

        // Log this request
        $this->requestLog[$identifier][] = $now;

        // Add rate limit info to response headers
        $response = $handler->handle($request);
        $remaining = $this->maxRequests - count($this->requestLog[$identifier]);

        return $response->withHeader('X-RateLimit-Limit', (string)$this->maxRequests)
                       ->withHeader('X-RateLimit-Remaining', (string)$remaining)
                       ->withHeader('X-RateLimit-Reset', (string)($now + $this->windowSeconds));
    }

    /**
     * Get unique identifier for rate limiting
     * @param ServerRequestInterface $request
     * @return string
     */
    private function getIdentifier(ServerRequestInterface $request): string {
        // Try to get user ID from token if authenticated
        if ($request->getAttribute('user')) {
            $user = $request->getAttribute('user');
            return 'user_' . $user->user_id;
        }

        // Fall back to IP address
        $serverParams = $request->getServerParams();
        $ip = $serverParams['REMOTE_ADDR'] ?? 'unknown';

        // Check for forwarded IPs (proxy)
        if (!empty($serverParams['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $serverParams['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($serverParams['HTTP_CLIENT_IP'])) {
            $ip = $serverParams['HTTP_CLIENT_IP'];
        }

        return 'ip_' . hash('sha256', $ip);
    }
}
