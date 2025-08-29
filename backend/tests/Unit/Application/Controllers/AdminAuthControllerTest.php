<?php

namespace App\Tests\Unit\Application\Controllers;

use App\Application\Controllers\AdminAuthController;
use App\Application\Services\AdminAuthenticationService;
use App\Domain\Entities\Admin;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Mockery as m;

class AdminAuthControllerTest extends TestCase
{
    private AdminAuthController $controller;
    private AdminAuthenticationService $authService;
    private ServerRequestInterface $request;
    private ResponseInterface $response;
    private StreamInterface $stream;

    protected function setUp(): void
    {
        $this->authService = m::mock(AdminAuthenticationService::class);
        $this->request = m::mock(ServerRequestInterface::class);
        $this->response = m::mock(ResponseInterface::class);
        $this->stream = m::mock(StreamInterface::class);

        $this->controller = new AdminAuthController($this->authService);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testLoginSuccess(): void
    {
        $loginData = [
            'username' => 'admin',
            'password' => 'password123'
        ];

        $admin = new Admin(1, 'admin', 'hashed_password', new \DateTimeImmutable(), new \DateTimeImmutable());

        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('POST');

        $this->request->shouldReceive('getHeaders')
            ->once()
            ->andReturn(['Content-Type' => ['application/json']]);

        $this->request->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode($loginData));

        $this->authService->shouldReceive('authenticate')
            ->once()
            ->with('admin', 'password123')
            ->andReturn($admin);

        $this->authService->shouldReceive('login')
            ->once()
            ->with($admin)
            ->andReturn(true);

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'success' => true,
                'message' => 'Login successful.'
            ]));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(200)
            ->andReturn($this->response);

        $result = $this->controller->login($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $loginData = [
            'username' => 'admin',
            'password' => 'wrong_password'
        ];

        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('POST');

        $this->request->shouldReceive('getHeaders')
            ->once()
            ->andReturn(['Content-Type' => ['application/json']]);

        $this->request->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode($loginData));

        $this->authService->shouldReceive('authenticate')
            ->once()
            ->with('admin', 'wrong_password')
            ->andReturn(null);

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'success' => false,
                'message' => 'Invalid username or password.'
            ]));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(401)
            ->andReturn($this->response);

        $result = $this->controller->login($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testLoginWithMissingUsername(): void
    {
        $loginData = [
            'password' => 'password123'
            // Missing username
        ];

        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('POST');

        $this->request->shouldReceive('getHeaders')
            ->once()
            ->andReturn(['Content-Type' => ['application/json']]);

        $this->request->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode($loginData));

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'success' => false,
                'message' => 'Username and password required.'
            ]));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(400)
            ->andReturn($this->response);

        $result = $this->controller->login($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testLoginWithMissingPassword(): void
    {
        $loginData = [
            'username' => 'admin'
            // Missing password
        ];

        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('POST');

        $this->request->shouldReceive('getHeaders')
            ->once()
            ->andReturn(['Content-Type' => ['application/json']]);

        $this->request->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode($loginData));

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'success' => false,
                'message' => 'Username and password required.'
            ]));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(400)
            ->andReturn($this->response);

        $result = $this->controller->login($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testLoginWithInvalidJson(): void
    {
        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('POST');

        $this->request->shouldReceive('getHeaders')
            ->once()
            ->andReturn(['Content-Type' => ['application/json']]);

        $this->request->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn('invalid json');

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'success' => false,
                'message' => 'Invalid JSON in request body.'
            ]));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(400)
            ->andReturn($this->response);

        $result = $this->controller->login($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testLoginWithSessionFailure(): void
    {
        $loginData = [
            'username' => 'admin',
            'password' => 'password123'
        ];

        $admin = new Admin(1, 'admin', 'hashed_password', new \DateTimeImmutable(), new \DateTimeImmutable());

        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('POST');

        $this->request->shouldReceive('getHeaders')
            ->once()
            ->andReturn(['Content-Type' => ['application/json']]);

        $this->request->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode($loginData));

        $this->authService->shouldReceive('authenticate')
            ->once()
            ->with('admin', 'password123')
            ->andReturn($admin);

        $this->authService->shouldReceive('login')
            ->once()
            ->with($admin)
            ->andReturn(false);

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'success' => false,
                'message' => 'Login failed due to session error.'
            ]));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(500)
            ->andReturn($this->response);

        $result = $this->controller->login($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testLoginWithException(): void
    {
        $loginData = [
            'username' => 'admin',
            'password' => 'password123'
        ];

        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('POST');

        $this->request->shouldReceive('getHeaders')
            ->once()
            ->andReturn(['Content-Type' => ['application/json']]);

        $this->request->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode($loginData));

        $this->authService->shouldReceive('authenticate')
            ->once()
            ->with('admin', 'password123')
            ->andThrow(new \Exception('Database connection failed'));

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(m::on(function ($json) {
                $data = json_decode($json, true);
                return $data['success'] === false &&
                       strpos($data['message'], 'internal error') !== false &&
                       isset($data['error']);
            }));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(500)
            ->andReturn($this->response);

        $result = $this->controller->login($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testLogoutWhenAuthenticated(): void
    {
        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(true);

        $this->authService->shouldReceive('logout')
            ->once()
            ->andReturn(true);

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'success' => true,
                'message' => 'Logout successful.'
            ]));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $result = $this->controller->logout($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testLogoutWhenNotAuthenticated(): void
    {
        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(false);

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'success' => false,
                'message' => 'Not logged in.'
            ]));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(401)
            ->andReturn($this->response);

        $result = $this->controller->logout($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testCheckAuthWhenAuthenticated(): void
    {
        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(true);

        // Mock session functions
        if (!session_id()) {
            session_start();
        }
        $_SESSION['last_regen'] = time();

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(m::on(function ($json) {
                $data = json_decode($json, true);
                return $data['authenticated'] === true &&
                       isset($data['session_id']) &&
                       isset($data['last_activity']);
            }));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $result = $this->controller->checkAuth($this->request, $this->response);

        $this->assertSame($this->response, $result);

        // Clean up
        session_destroy();
    }

    public function testCheckAuthWhenNotAuthenticated(): void
    {
        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(false);

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'authenticated' => false
            ]));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $result = $this->controller->checkAuth($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testRecoverSessionSuccess(): void
    {
        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(true);

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'recovered' => true,
                'authenticated' => true,
                'message' => 'Session recovered and authenticated'
            ]));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $result = $this->controller->recoverSession($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testRecoverSessionNotAuthenticated(): void
    {
        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(false);

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'recovered' => true,
                'authenticated' => false,
                'message' => 'Session recovered but not authenticated'
            ]));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $result = $this->controller->recoverSession($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testRecoverSessionWithException(): void
    {
        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andThrow(new \Exception('Session error'));

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'recovered' => false,
                'message' => 'Session recovery failed'
            ]));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(500)
            ->andReturn($this->response);

        $result = $this->controller->recoverSession($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testControllerConstructor(): void
    {
        $newController = new AdminAuthController($this->authService);

        $this->assertInstanceOf(AdminAuthController::class, $newController);
    }
}