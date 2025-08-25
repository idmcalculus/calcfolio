# Backend Error Handling Improvement Plan

## Problem Statement

Currently, exact server errors (like "405 Method Not Allowed") are not reaching the frontend. Instead, all server errors are being categorized as CORS errors because:

1. **Missing CORS Headers on Error Responses**: When the server returns HTTP errors, these responses don't include CORS headers, causing browsers to block them
2. **Incomplete Middleware Coverage**: Current CORS middleware only handles successful requests and preflight OPTIONS requests
3. **Framework-Level Errors**: Slim framework routing errors occur before route handlers execute, bypassing custom middleware

## Solution Architecture

### Phase 1: Enhanced Server-Side Error Handling

#### 1. Create Enhanced Error Handling Middleware

**File: `backend/src/Middleware/ErrorHandlingMiddleware.php`**

```php
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
```

#### 2. Create Custom Error Handler for Slim Framework

**File: `backend/src/Handlers/CustomErrorHandler.php`**

```php
<?php

namespace App\Handlers;

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
```

#### 3. Update Main Application File

**Updates to `backend/src/index.php`:**

```php
// Add these imports at the top
use App\Middleware\ErrorHandlingMiddleware;
use App\Handlers\CustomErrorHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpMethodNotAllowedException;

// Replace the existing error middleware section (around line 67) with:
$allowedOrigins = array_map('trim', explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:3000'));
$isDevelopment = ($_ENV['APP_ENV'] ?? 'development') !== 'production';

// Add custom error handler
$customErrorHandler = new CustomErrorHandler($allowedOrigins, $isDevelopment);
$errorMiddleware = $app->addErrorMiddleware(true, true, true, $customErrorHandler);

// Add enhanced error handling middleware
$app->add(new ErrorHandlingMiddleware($allowedOrigins, $isDevelopment));

// Remove or simplify the existing CORS middleware since it's now handled in ErrorHandlingMiddleware
// Keep only the preflight OPTIONS handler:
$app->add(function (Request $request, $handler) use ($allowedOrigins) {
    // Handle preflight OPTIONS requests
    if (strtoupper($request->getMethod()) === 'OPTIONS') {
        $origin = $request->getHeaderLine('Origin');
        $originAllowed = $origin && in_array($origin, $allowedOrigins, true);
        
        $response = new \Slim\Psr7\Response(204);
        
        if ($originAllowed) {
            $allowHeaders = $request->getHeaderLine('Access-Control-Request-Headers') ?: 'Content-Type, Authorization, X-Requested-With';
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Vary', 'Origin')
                ->withHeader('Access-Control-Allow-Credentials', 'true')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, DELETE, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', $allowHeaders)
                ->withHeader('Access-Control-Max-Age', '86400')
                ->withHeader('Content-Length', '0');
        }
        return $response;
    }

    // For non-OPTIONS requests, just pass through - CORS headers will be added by ErrorHandlingMiddleware
    return $handler->handle($request);
});

// Add route-level error handling for specific scenarios
$app->map(['GET', 'POST', 'PATCH', 'DELETE'], '/admin/login', function (Request $request, Response $response) {
    // Only allow POST method
    if ($request->getMethod() !== 'POST') {
        throw new HttpMethodNotAllowedException($request, ['POST']);
    }
    
    // Existing login logic here...
    $data = json_decode($request->getBody()->getContents(), true);
    // ... rest of login logic
});

// Add a catch-all route for better 404 handling
$app->any('[/{path:.*}]', function (Request $request, Response $response) {
    throw new HttpNotFoundException($request);
});
```

### Phase 2: Enhanced Frontend Error Handling

#### Update API Composable

**Updates to `frontend/composables/useApi.ts`:**

```typescript
// Add enhanced error types
export interface ServerError {
  type: string
  code: string
  message: string
  timestamp: string
  debug?: {
    exception: string
    file: string
    line: number
    trace: string
  }
}

export interface ApiErrorResponse {
  success: false
  error: ServerError
}

// Add error handling utility
const handleApiError = (error: any): never => {
  // Check if it's a server error response with structured error data
  if (error.data && !error.data.success && error.data.error) {
    const serverError = error.data.error as ServerError;
    
    // Create a descriptive error message
    const errorMessage = serverError.message || 'An error occurred';
    const errorDetails = process.dev && serverError.debug 
      ? `\n\nDebug: ${serverError.debug.exception} in ${serverError.debug.file}:${serverError.debug.line}`
      : '';
    
    const enhancedError = new Error(`${errorMessage}${errorDetails}`);
    (enhancedError as any).serverError = serverError;
    (enhancedError as any).statusCode = error.statusCode || 500;
    
    throw enhancedError;
  }
  
  // Check for network/CORS errors
  if (error.name === 'FetchError' || error.message?.includes('fetch')) {
    // Try to provide more helpful error messages
    if (error.message?.includes('CORS')) {
      throw new Error('Network error: Unable to connect to server. Please check your connection and try again.');
    }
    
    if (error.statusCode) {
      const statusMessage = getStatusMessage(error.statusCode);
      throw new Error(`Server error (${error.statusCode}): ${statusMessage}`);
    }
    
    throw new Error('Network error: Unable to connect to server. Please check your connection and try again.');
  }
  
  // Fallback for other types of errors
  throw error instanceof Error ? error : new Error('An unexpected error occurred');
}

const getStatusMessage = (statusCode: number): string => {
  const statusMessages: Record<number, string> = {
    400: 'Bad request - please check your input',
    401: 'Authentication required - please log in',
    403: 'Access forbidden - insufficient permissions',
    404: 'Resource not found',
    405: 'Method not allowed - invalid request type',
    422: 'Validation error - please check your input',
    429: 'Too many requests - please try again later',
    500: 'Internal server error - please try again later',
    502: 'Bad gateway - server temporarily unavailable',
    503: 'Service unavailable - please try again later'
  };
  
  return statusMessages[statusCode] || 'Unknown error occurred';
}

// Update API methods to use enhanced error handling
const auth = {
  login: async (credentials: LoginRequest): Promise<ApiResponse> => {
    try {
      return await $fetch<ApiResponse>('/admin/login', {
        ...getBaseOptions(),
        baseURL,
        method: 'POST',
        body: credentials,
      });
    } catch (error) {
      handleApiError(error);
    }
  },

  logout: async (): Promise<ApiResponse> => {
    try {
      return await $fetch<ApiResponse>('/admin/logout', {
        ...getBaseOptions(),
        baseURL,
        method: 'POST',
      });
    } catch (error) {
      handleApiError(error);
    }
  },

  // ... other methods with similar error handling
}
```

#### Add Development Debugging

**Create `frontend/utils/debug.ts`:**

```typescript
export const logApiRequest = (url: string, options: any) => {
  if (process.dev) {
    console.group(`🔄 API Request: ${options.method || 'GET'} ${url}`);
    console.log('Options:', options);
    console.log('Timestamp:', new Date().toISOString());
    console.groupEnd();
  }
}

export const logApiResponse = (url: string, response: any, error?: any) => {
  if (process.dev) {
    console.group(`${error ? '❌' : '✅'} API Response: ${url}`);
    if (error) {
      console.error('Error:', error);
      if (error.serverError) {
        console.error('Server Error Details:', error.serverError);
      }
    } else {
      console.log('Response:', response);
    }
    console.log('Timestamp:', new Date().toISOString());
    console.groupEnd();
  }
}
```

### Phase 3: Testing Strategy

#### Test Scenarios

1. **405 Method Not Allowed**: Send GET request to POST-only endpoint
2. **404 Not Found**: Request non-existent endpoint  
3. **401 Unauthorized**: Access protected endpoint without authentication
4. **422 Validation Error**: Send invalid data to form endpoints
5. **500 Server Error**: Trigger application error
6. **Network Error**: Test with server down

#### Expected Results

Each test should verify:
- ✅ **CORS headers present** on error responses
- ✅ **Structured error format** returned
- ✅ **Specific error messages** displayed in frontend
- ✅ **Proper HTTP status codes** returned
- ✅ **Debug information available** in development mode

### Phase 4: Implementation Benefits

This solution provides:

1. **Exact Error Propagation**: Server errors now reach the frontend with specific details
2. **CORS Compliance**: All responses include proper CORS headers
3. **Structured Error Format**: Consistent error structure across all endpoints
4. **Enhanced Debugging**: Detailed error information in development mode
5. **User-Friendly Messages**: Clear error messages for end users
6. **Comprehensive Logging**: Server-side error logging with context
7. **Type Safety**: Enhanced TypeScript types for error handling

## Implementation Notes

### Security Considerations

- Debug information is only shown in development mode
- Sensitive server details are not exposed in production
- Error messages are sanitized to prevent information leakage

### Performance Impact

- Minimal performance overhead
- Error handling only activates when errors occur
- Efficient CORS header management

### Maintenance

- Centralized error handling logic
- Easy to extend for new error types
- Consistent error format across application

This comprehensive solution will resolve the issue where frontend categorizes all server errors as CORS errors by ensuring proper error propagation with CORS compliance.