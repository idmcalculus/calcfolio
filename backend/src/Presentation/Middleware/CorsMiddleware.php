<?php

namespace App\Presentation\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class CorsMiddleware implements MiddlewareInterface
{
    private array $allowedOrigins;

    public function __construct(array $allowedOrigins = [])
    {
        $this->allowedOrigins = $allowedOrigins;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $origin = $request->getHeaderLine('Origin');
        
        // Log CORS debug info
        error_log('CORS Middleware - Origin: ' . $origin);
        error_log('CORS Middleware - Allowed Origins: ' . json_encode($this->allowedOrigins));
        
        $originAllowed = $origin && in_array($origin, $this->allowedOrigins, true);
        
        error_log('CORS Middleware - Origin Allowed: ' . ($originAllowed ? 'YES' : 'NO'));

        // Handle preflight OPTIONS requests
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            $response = new Response(204);

            if ($originAllowed) {
                $allowHeaders = $request->getHeaderLine('Access-Control-Request-Headers')
                    ?: 'Content-Type, Authorization, X-Requested-With';

                $response = $response
                    ->withHeader('Access-Control-Allow-Origin', $origin)
                    ->withHeader('Vary', 'Origin')
                    ->withHeader('Access-Control-Allow-Credentials', 'true')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers', $allowHeaders)
                    ->withHeader('Access-Control-Max-Age', '86400')
                    ->withHeader('Content-Length', '0');
            } else {
                // Even for non-allowed origins, return a proper response to avoid browser errors
                $response = $response
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                    ->withHeader('Content-Length', '0');
            }

            return $response;
        }

        try {
            // For non-OPTIONS requests, process normally and add CORS headers to response
            $response = $handler->handle($request);
        } catch (\Exception $e) {
            // If an error occurs, ensure we still return a proper response
            error_log('Request handling error: ' . $e->getMessage());
            throw $e;
        }

        if ($originAllowed) {
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Vary', 'Origin')
                ->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}