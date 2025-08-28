<?php

namespace App\Presentation\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Slim\Handlers\ErrorHandler;
use Slim\Psr7\Response;
use Throwable;

class CustomErrorHandler extends ErrorHandler
{
    private array $allowedOrigins;
    private bool $isDevelopment;

    public function __construct(array $allowedOrigins = [], bool $isDevelopment = false)
    {
        $this->allowedOrigins = $allowedOrigins;
        $this->isDevelopment = $isDevelopment;
    }

    protected function respond(): ResponseInterface
    {
        $exception = $this->exception;
        $statusCode = $this->getStatusCode();

        // Create structured error response
        $errorData = [
            'success' => false,
            'error' => [
                'type' => $this->getErrorType($statusCode),
                'code' => $this->getErrorCode($exception, $statusCode),
                'message' => $this->getPublicErrorMessage($exception, $statusCode),
                'timestamp' => date('c')
            ]
        ];

        // Add debug information in development
        if ($this->isDevelopment && $exception) {
            $errorData['error']['debug'] = [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }

        $response = new Response($statusCode);
        $response->getBody()->write(json_encode($errorData));
        $response = $response->withHeader('Content-Type', 'application/json');

        // Add CORS headers
        return $this->addCorsHeaders($response);
    }

    private function getStatusCode(): int
    {
        if ($this->exception instanceof HttpException) {
            return $this->exception->getCode();
        }

        if ($this->statusCode) {
            return $this->statusCode;
        }

        return 500;
    }

    private function getErrorType(int $statusCode): string
    {
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

    private function getErrorCode(?Throwable $exception, int $statusCode): string
    {
        if ($exception) {
            $exceptionClass = basename(str_replace('\\', '/', get_class($exception)));
            return strtoupper($exceptionClass) . '_' . $statusCode;
        }

        return 'HTTP_ERROR_' . $statusCode;
    }

    private function getPublicErrorMessage(?Throwable $exception, int $statusCode): string
    {
        // For HTTP exceptions, use their message if available
        if ($exception instanceof HttpException && $exception->getMessage()) {
            return $exception->getMessage();
        }

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
            default => $this->isDevelopment && $exception ? $exception->getMessage() : 'An unexpected error occurred.'
        };
    }

    private function addCorsHeaders(ResponseInterface $response): ResponseInterface
    {
        $origin = $this->request ? $this->request->getHeaderLine('Origin') : '';
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
}