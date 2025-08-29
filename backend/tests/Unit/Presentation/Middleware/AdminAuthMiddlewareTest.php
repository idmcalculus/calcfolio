<?php

namespace App\Tests\Unit\Presentation\Middleware;

use App\Application\Services\AdminAuthenticationService;
use App\Presentation\Middleware\AdminAuthMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mockery as m;

class AdminAuthMiddlewareTest extends TestCase
{
    private AdminAuthMiddleware $middleware;
    private AdminAuthenticationService $authService;
    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;

    protected function setUp(): void
    {
        $this->authService = m::mock(AdminAuthenticationService::class);
        $this->middleware = new AdminAuthMiddleware($this->authService);
        $this->request = m::mock(ServerRequestInterface::class);
        $this->handler = m::mock(RequestHandlerInterface::class);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(AdminAuthMiddleware::class, $this->middleware);
    }

    public function testProcessWhenNotAuthenticated(): void
    {
        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(false);

        $result = $this->middleware->process($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(401, $result->getStatusCode());
        $this->assertEquals('application/json', $result->getHeaderLine('Content-Type'));

        $body = json_decode($result->getBody()->getContents(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Authentication required', $body['message']);
    }

    public function testProcessWhenSessionExpired(): void
    {
        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(true);

        $this->authService->shouldReceive('validateSessionTimeout')
            ->once()
            ->andReturn(false);

        $result = $this->middleware->process($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(401, $result->getStatusCode());
        $this->assertEquals('application/json', $result->getHeaderLine('Content-Type'));

        $body = json_decode($result->getBody()->getContents(), true);
        $this->assertFalse($body['success']);
        $this->assertEquals('Session expired. Please log in again.', $body['message']);
    }

    public function testProcessWhenAuthenticatedAndSessionValid(): void
    {
        $expectedResponse = m::mock(ResponseInterface::class);

        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(true);

        $this->authService->shouldReceive('validateSessionTimeout')
            ->once()
            ->andReturn(true);

        $this->authService->shouldReceive('regenerateSession')
            ->once();

        $this->handler->shouldReceive('handle')
            ->once()
            ->with($this->request)
            ->andReturn($expectedResponse);

        $result = $this->middleware->process($this->request, $this->handler);

        $this->assertSame($expectedResponse, $result);
    }

    public function testProcessCallsRegenerateSession(): void
    {
        $expectedResponse = m::mock(ResponseInterface::class);

        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(true);

        $this->authService->shouldReceive('validateSessionTimeout')
            ->once()
            ->andReturn(true);

        $this->authService->shouldReceive('regenerateSession')
            ->once();

        $this->handler->shouldReceive('handle')
            ->once()
            ->with($this->request)
            ->andReturn($expectedResponse);

        $this->middleware->process($this->request, $this->handler);

        // Verify that regenerateSession was called
        $this->authService->shouldHaveReceived('regenerateSession')->once();
    }

    public function testProcessPassesRequestToHandler(): void
    {
        $expectedResponse = m::mock(ResponseInterface::class);

        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(true);

        $this->authService->shouldReceive('validateSessionTimeout')
            ->once()
            ->andReturn(true);

        $this->authService->shouldReceive('regenerateSession')
            ->once();

        $this->handler->shouldReceive('handle')
            ->once()
            ->with($this->request)
            ->andReturn($expectedResponse);

        $result = $this->middleware->process($this->request, $this->handler);

        $this->assertSame($expectedResponse, $result);
    }

    public function testProcessHandlesExceptionFromHandler(): void
    {
        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(true);

        $this->authService->shouldReceive('validateSessionTimeout')
            ->once()
            ->andReturn(true);

        $this->authService->shouldReceive('regenerateSession')
            ->once();

        $this->handler->shouldReceive('handle')
            ->once()
            ->with($this->request)
            ->andThrow(new \Exception('Handler error'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Handler error');

        $this->middleware->process($this->request, $this->handler);
    }

    public function testProcessReturnsProperJsonResponseForAuthFailure(): void
    {
        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(false);

        $result = $this->middleware->process($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(401, $result->getStatusCode());
        $this->assertEquals('application/json', $result->getHeaderLine('Content-Type'));

        $body = json_decode($result->getBody()->getContents(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('success', $body);
        $this->assertArrayHasKey('message', $body);
    }

    public function testProcessReturnsProperJsonResponseForSessionTimeout(): void
    {
        $this->authService->shouldReceive('isAuthenticated')
            ->once()
            ->andReturn(true);

        $this->authService->shouldReceive('validateSessionTimeout')
            ->once()
            ->andReturn(false);

        $result = $this->middleware->process($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(401, $result->getStatusCode());
        $this->assertEquals('application/json', $result->getHeaderLine('Content-Type'));

        $body = json_decode($result->getBody()->getContents(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('success', $body);
        $this->assertArrayHasKey('message', $body);
        $this->assertStringContainsString('expired', $body['message']);
    }
}