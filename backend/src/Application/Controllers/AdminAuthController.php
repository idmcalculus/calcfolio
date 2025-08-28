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

    /**
     * Admin login
     *
     * @OA\Post(
     *     path="/admin/login",
     *     summary="Admin login",
     *     tags={"Admin Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=400, description="Validation error")
     * )
     */
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
            $data = json_decode($request->getBody()->getContents(), true);

            if (!isset($data['username'], $data['password'])) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Username and password required.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            $admin = $this->authService->authenticate($data['username'], $data['password']);

            if (!$admin) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Invalid username or password.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }

            $loginSuccess = $this->authService->login($admin);

            if (!$loginSuccess) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Login failed due to session error.'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Login successful.'
            ]));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            error_log('Admin login error: ' . $e->getMessage());
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'An internal error occurred during login.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * Admin logout
     *
     * @OA\Post(
     *     path="/admin/logout",
     *     summary="Admin logout",
     *     tags={"Admin Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Not authenticated")
     * )
     */
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

    /**
     * Check authentication status
     *
     * @OA\Get(
     *     path="/admin/check",
     *     summary="Check authentication status",
     *     tags={"Admin Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="Authentication status",
     *         @OA\JsonContent(
     *             @OA\Property(property="authenticated", type="boolean"),
     *             @OA\Property(property="session_id", type="string"),
     *             @OA\Property(property="last_activity", type="string")
     *         )
     *     )
     * )
     */
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

    /**
     * Recover session after resource exhaustion
     *
     * @OA\Post(
     *     path="/admin/recover-session",
     *     summary="Recover session after resource issues",
     *     tags={"Admin Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="Session recovery status",
     *         @OA\JsonContent(
     *             @OA\Property(property="recovered", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
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