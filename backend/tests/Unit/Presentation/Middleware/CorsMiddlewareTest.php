<?php

namespace App\Tests\Unit\Presentation\Middleware;

use App\Presentation\Middleware\CorsMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Mockery as m;

class CorsMiddlewareTest extends TestCase
{
    private CorsMiddleware $middleware;
    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;
    private ResponseInterface $response;

    protected function setUp(): void
    {
        $allowedOrigins = [
            'http://localhost:3000',
            'https://calcfolio.vercel.app',
            'https://www.idmcalculus.cv'
        ];

        $this->middleware = new CorsMiddleware($allowedOrigins);
        $this->request = m::mock(ServerRequestInterface::class);
        $this->handler = m::mock(RequestHandlerInterface::class);
        $this->response = m::mock(ResponseInterface::class);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testConstructorWithDefaultOrigins(): void
    {
        $middleware = new CorsMiddleware();
        $this->assertInstanceOf(CorsMiddleware::class, $middleware);
    }

    public function testConstructorWithCustomOrigins(): void
    {
        $origins = ['https://example.com', 'https://test.com'];
        $middleware = new CorsMiddleware($origins);
        $this->assertInstanceOf(CorsMiddleware::class, $middleware);
    }

    public function testOptionsRequestWithAllowedOrigin(): void
    {
        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('OPTIONS');

        $this->request->shouldReceive('getHeaderLine')
            ->with('Origin')
            ->once()
            ->andReturn('http://localhost:3000');

        $this->request->shouldReceive('getHeaderLine')
            ->with('Access-Control-Request-Headers')
            ->once()
            ->andReturn('Content-Type, Authorization');

        $result = $this->middleware->process($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(204, $result->getStatusCode());
        $this->assertEquals('http://localhost:3000', $result->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertEquals('Origin', $result->getHeaderLine('Vary'));
        $this->assertEquals('true', $result->getHeaderLine('Access-Control-Allow-Credentials'));
        $this->assertEquals('GET, POST, PATCH, DELETE, OPTIONS', $result->getHeaderLine('Access-Control-Allow-Methods'));
        $this->assertEquals('Content-Type, Authorization', $result->getHeaderLine('Access-Control-Allow-Headers'));
        $this->assertEquals('86400', $result->getHeaderLine('Access-Control-Max-Age'));
        $this->assertEquals('0', $result->getHeaderLine('Content-Length'));
    }

    public function testOptionsRequestWithDisallowedOrigin(): void
    {
        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('OPTIONS');

        $this->request->shouldReceive('getHeaderLine')
            ->with('Origin')
            ->once()
            ->andReturn('https://malicious-site.com');

        $result = $this->middleware->process($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(204, $result->getStatusCode());
        $this->assertFalse($result->hasHeader('Access-Control-Allow-Origin'));
        $this->assertEquals('GET, POST, PATCH, DELETE, OPTIONS', $result->getHeaderLine('Access-Control-Allow-Methods'));
        $this->assertEquals('Content-Type, Authorization, X-Requested-With', $result->getHeaderLine('Access-Control-Allow-Headers'));
    }

    public function testOptionsRequestWithoutOrigin(): void
    {
        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('OPTIONS');

        $this->request->shouldReceive('getHeaderLine')
            ->with('Origin')
            ->once()
            ->andReturn('');

        $result = $this->middleware->process($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(204, $result->getStatusCode());
        $this->assertFalse($result->hasHeader('Access-Control-Allow-Origin'));
    }

    public function testOptionsRequestWithDefaultHeaders(): void
    {
        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('OPTIONS');

        $this->request->shouldReceive('getHeaderLine')
            ->with('Origin')
            ->once()
            ->andReturn('https://calcfolio.vercel.app');

        $this->request->shouldReceive('getHeaderLine')
            ->with('Access-Control-Request-Headers')
            ->once()
            ->andReturn('');

        $result = $this->middleware->process($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(204, $result->getStatusCode());
        $this->assertEquals('https://calcfolio.vercel.app', $result->getHeaderLine('Access-Control-Allow-Origin'));
        $this->assertEquals('Content-Type, Authorization, X-Requested-With', $result->getHeaderLine('Access-Control-Allow-Headers'));
    }

    public function testNonOptionsRequestWithAllowedOrigin(): void
    {
        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('POST');

        $this->request->shouldReceive('getHeaderLine')
            ->with('Origin')
            ->once()
            ->andReturn('https://www.idmcalculus.cv');

        $this->handler->shouldReceive('handle')
            ->once()
            ->with($this->request)
            ->andReturn($this->response);

        $this->response->shouldReceive('withHeader')
            ->with('Access-Control-Allow-Origin', 'https://www.idmcalculus.cv')
            ->once()
            ->andReturn($this->response);

        $this->response->shouldReceive('withHeader')
            ->with('Vary', 'Origin')
            ->once()
            ->andReturn($this->response);

        $this->response->shouldReceive('withHeader')
            ->with('Access-Control-Allow-Credentials', 'true')
            ->once()
            ->andReturn($this->response);

        $result = $this->middleware->process($this->request, $this->handler);

        $this->assertSame($this->response, $result);
    }

    public function testNonOptionsRequestWithDisallowedOrigin(): void
    {
        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('GET');

        $this->request->shouldReceive('getHeaderLine')
            ->with('Origin')
            ->once()
            ->andReturn('https://unauthorized-site.com');

        $this->handler->shouldReceive('handle')
            ->once()
            ->with($this->request)
            ->andReturn($this->response);

        // For disallowed origins, no CORS headers should be added
        $result = $this->middleware->process($this->request, $this->handler);

        $this->assertSame($this->response, $result);
    }

    public function testNonOptionsRequestWithoutOrigin(): void
    {
        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('POST');

        $this->request->shouldReceive('getHeaderLine')
            ->with('Origin')
            ->once()
            ->andReturn('');

        $this->handler->shouldReceive('handle')
            ->once()
            ->with($this->request)
            ->andReturn($this->response);

        // Without origin, no CORS headers should be added
        $result = $this->middleware->process($this->request, $this->handler);

        $this->assertSame($this->response, $result);
    }

    public function testHandlerThrowsException(): void
    {
        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('POST');

        $this->request->shouldReceive('getHeaderLine')
            ->with('Origin')
            ->once()
            ->andReturn('https://calcfolio.vercel.app');

        $this->handler->shouldReceive('handle')
            ->once()
            ->with($this->request)
            ->andThrow(new \Exception('Handler error'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Handler error');

        $this->middleware->process($this->request, $this->handler);
    }

    public function testCaseInsensitiveMethodCheck(): void
    {
        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('options'); // lowercase

        $this->request->shouldReceive('getHeaderLine')
            ->with('Origin')
            ->once()
            ->andReturn('http://localhost:3000');

        $this->request->shouldReceive('getHeaderLine')
            ->with('Access-Control-Request-Headers')
            ->once()
            ->andReturn('');

        $result = $this->middleware->process($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(204, $result->getStatusCode());
    }

    public function testOriginValidationIsStrict(): void
    {
        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('OPTIONS');

        // Test with origin that has trailing slash (should not match)
        $this->request->shouldReceive('getHeaderLine')
            ->with('Origin')
            ->once()
            ->andReturn('http://localhost:3000/');

        $result = $this->middleware->process($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(204, $result->getStatusCode());
        $this->assertFalse($result->hasHeader('Access-Control-Allow-Origin'));
    }

    public function testEmptyAllowedOrigins(): void
    {
        $middleware = new CorsMiddleware([]);

        $this->request->shouldReceive('getMethod')
            ->once()
            ->andReturn('OPTIONS');

        $this->request->shouldReceive('getHeaderLine')
            ->with('Origin')
            ->once()
            ->andReturn('http://localhost:3000');

        $result = $middleware->process($this->request, $this->handler);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(204, $result->getStatusCode());
        $this->assertFalse($result->hasHeader('Access-Control-Allow-Origin'));
    }
}