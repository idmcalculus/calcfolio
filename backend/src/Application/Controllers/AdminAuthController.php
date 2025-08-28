<?php

namespace App\Application\Controllers;

use OpenApi\Attributes as OA;
use App\Application\Services\AdminAuthenticationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminAuthController
{
    private AdminAuthenticationService $authService;

    public function __construct(AdminAuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    #[OA\Post(
        path: "/admin/login",
        summary: "Admin login",
        tags: ["Admin Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["username","password"],
                properties: [
                    new OA\Property(property: "username", type: "string"),
                    new OA\Property(property: "password", type: "string", format: "password"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Invalid credentials"),
            new OA\Response(response: 400, description: "Validation error"),
        ]
    )]
    public function login(Request $request, Response $response): Response
    {
        try {
            // Log request details for debugging
            error_log('Admin login request received');
            error_log('Request method: ' . $request->getMethod());
            error_log('Request headers: ' . json_encode($request->getHeaders()));
            
            $body = $request->getBody()->getContents();
            error_log('Request body: ' . $body);
            
            $data = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('JSON decode error: ' . json_last_error_msg());
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Invalid JSON in request body.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            if (!isset($data['username'], $data['password'])) {
                error_log('Missing username or password in request');
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Username and password required.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            error_log('Attempting to authenticate user: ' . $data['username']);
            $admin = $this->authService->authenticate($data['username'], $data['password']);

            if (!$admin) {
                error_log('Authentication failed for user: ' . $data['username']);
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Invalid username or password.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }

            error_log('Authentication successful, attempting login');
            $loginSuccess = $this->authService->login($admin);

            if (!$loginSuccess) {
                error_log('Session login failed');
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Login failed due to session error.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }

            error_log('Login successful for user: ' . $data['username']);
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Login successful.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            error_log('Admin login exception: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'An internal error occurred during login.',
                'error' => $e->getMessage() // Include error in development
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    #[OA\Post(
        path: "/admin/logout",
        summary: "Admin logout",
        tags: ["Admin Authentication"],
        security: [["sessionAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Logout successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Not authenticated"),
        ]
    )]
    public function logout(Request $request, Response $response): Response
    {
        if (!$this->authService->isAuthenticated()) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Not logged in.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $logoutSuccess = $this->authService->logout();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Logout successful.'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    #[OA\Get(
        path: "/admin/check",
        summary: "Check authentication status",
        tags: ["Admin Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Authentication status",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "authenticated", type: "boolean"),
                        new OA\Property(property: "session_id", type: "string"),
                        new OA\Property(property: "last_activity", type: "string"),
                    ]
                )
            ),
        ]
    )]
    public function checkAuth(Request $request, Response $response): Response
    {
        $isAuthenticated = $this->authService->isAuthenticated();

        $sessionInfo = [
            'authenticated' => $isAuthenticated
        ];

        // Add session debugging info if authenticated
        if ($isAuthenticated && session_status() === PHP_SESSION_ACTIVE) {
            $sessionInfo['session_id'] = session_id();
            $sessionInfo['last_activity'] = isset($_SESSION['last_regen'])
                ? date('c', $_SESSION['last_regen'])
                : 'unknown';
        }

        $response->getBody()->write(json_encode($sessionInfo));
        return $response->withHeader('Content-Type', 'application/json');
    }

    #[OA\Post(
        path: "/admin/recover-session",
        summary: "Recover session after resource issues",
        tags: ["Admin Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Session recovery status",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "recovered", type: "boolean"),
                        new OA\Property(property: "message", type: "string"),
                    ]
                )
            ),
        ]
    )]
    public function recoverSession(Request $request, Response $response): Response
    {
        try {
            // Check if session is active
            $sessionActive = session_status() === PHP_SESSION_ACTIVE;

            if (!$sessionActive) {
                // Try to start session
                if (!headers_sent()) {
                    session_start();
                    $sessionActive = true;
                }
            }

            $isAuthenticated = $this->authService->isAuthenticated();

            $response->getBody()->write(json_encode([
                'recovered' => $sessionActive,
                'authenticated' => $isAuthenticated,
                'message' => $sessionActive
                    ? ($isAuthenticated ? 'Session recovered and authenticated' : 'Session recovered but not authenticated')
                    : 'Could not recover session'
            ]));

            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            error_log('Session recovery error: ' . $e->getMessage());
            $response->getBody()->write(json_encode([
                'recovered' => false,
                'message' => 'Session recovery failed'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}