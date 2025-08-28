<?php

namespace App\Presentation\Middleware;

use App\Application\Services\AdminAuthenticationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AdminAuthMiddleware implements MiddlewareInterface
{
    private AdminAuthenticationService $authService;

    public function __construct(AdminAuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Check if user is authenticated
        if (!$this->authService->isAuthenticated()) {
            $response = new Response(401);
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Authentication required'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        // Validate session timeout
        if (!$this->authService->validateSessionTimeout()) {
            $response = new Response(401);
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Session expired. Please log in again.'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        // Regenerate session periodically for security (every 30 minutes)
        // This prevents session fixation attacks while maintaining session continuity
        $this->authService->regenerateSession();

        // Continue with the request
        return $handler->handle($request);
    }
}