<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Throwable;

class ErrorHandlingMiddleware implements MiddlewareInterface
{
    private array $allowedOrigins;
    private bool $isDevelopment;

    public function __construct(array $allowedOrigins = [], bool $isDevelopment = false)
    {
        $this->allowedOrigins = $allowedOrigins;
        $this->isDevelopment = $isDevelopment;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (Throwable $e) {
            // Create structured error response
            $response = $this->createErrorResponse($e, $request);
        }

        // Always add CORS headers to any response
        return $this->addCorsHeaders($response, $request);
    }

    private function createErrorResponse(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        // Determine error type and status code
        $statusCode = $this->getStatusCodeFromException($e);
        $errorType = $this->getErrorTypeFromException($e);
        
        // Log the error with context
        $this->logError($e, $request, $statusCode);

        // Create structured error response
        $errorData = [
            'success' => false,
            'error' => [
                'type' => $errorType,
                'code' => $this->getErrorCode($e),
                'message' => $this->getPublicErrorMessage($e),
                'timestamp' => date('c')
            ]
        ];

        // Add debug information in development
        if ($this->isDevelopment) {
            $errorData['error']['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
        }

        $response = new Response($statusCode);
        $response->getBody()->write(json_encode($errorData));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function addCorsHeaders(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        $origin = $request->getHeaderLine('Origin');
        $originAllowed = $origin && in_array($origin, $this->allowedOrigins, true);

        if ($originAllowed) {
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Vary', 'Origin')
                ->withHeader('Access-Control-Allow-Credentials', 'true')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, DELETE, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }

        return $response;
    }

    private function getStatusCodeFromException(Throwable $e): int
    {
        // Map specific exceptions to HTTP status codes
        if (method_exists($e, 'getCode') && $e->getCode() >= 400 && $e->getCode() < 600) {
            return $e->getCode();
        }

        // Default mappings based on exception type
        $exceptionClass = get_class($e);
        
        return match(true) {
            str_contains($exceptionClass, 'NotFound') => 404,
            str_contains($exceptionClass, 'MethodNotAllowed') => 405,
            str_contains($exceptionClass, 'Unauthorized') => 401,
            str_contains($exceptionClass, 'Forbidden') => 403,
            str_contains($exceptionClass, 'Validation') => 422,
            default => 500
        };
    }

    private function getErrorTypeFromException(Throwable $e): string
    {
        $statusCode = $this->getStatusCodeFromException($e);
        
        return match($statusCode) {
            400 => 'bad_request',
            401 => 'unauthorized',
            403 => 'forbidden', 
            404 => 'not_found',
            405 => 'method_not_allowed',
            422 => 'validation_error',
            429 => 'rate_limited',
            500 => 'server_error',
            502 => 'bad_gateway',
            503 => 'service_unavailable',
            default => 'unknown_error'
        };
    }

    private function getErrorCode(Throwable $e): string
    {
        $statusCode = $this->getStatusCodeFromException($e);
        $exceptionClass = basename(str_replace('\\', '/', get_class($e)));
        
        return strtoupper($exceptionClass) . '_' . $statusCode;
    }

    private function getPublicErrorMessage(Throwable $e): string
    {
        $statusCode = $this->getStatusCodeFromException($e);
        
        // Provide user-friendly messages for common errors
        return match($statusCode) {
            400 => 'Bad request. Please check your input and try again.',
            401 => 'Authentication required. Please log in.',
            403 => 'Access forbidden. You do not have permission to perform this action.',
            404 => 'The requested resource was not found.',
            405 => 'Method not allowed. Please check your request method.',
            422 => 'Validation failed. Please check your input.',
            429 => 'Too many requests. Please try again later.',
            500 => 'An internal server error occurred. Please try again later.',
            502 => 'Bad gateway. Please try again later.',
            503 => 'Service temporarily unavailable. Please try again later.',
            default => $this->isDevelopment ? $e->getMessage() : 'An unexpected error occurred.'
        };
    }

    private function logError(Throwable $e, ServerRequestInterface $request, int $statusCode): void
    {
        $context = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'status_code' => $statusCode,
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown'
        ];

        error_log('API Error: ' . json_encode($context));
    }
}