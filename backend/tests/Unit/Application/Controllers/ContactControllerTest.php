<?php

namespace App\Tests\Unit\Application\Controllers;

use App\Application\Controllers\ContactController;
use App\Application\Services\ContactFormService;
use App\Application\Services\AdminMessageService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Mockery as m;

class ContactControllerTest extends TestCase
{
    private ContactController $controller;
    private ContactFormService $contactFormService;
    private AdminMessageService $adminMessageService;
    private ServerRequestInterface $request;
    private ResponseInterface $response;
    private StreamInterface $stream;

    protected function setUp(): void
    {
        $this->contactFormService = m::mock(ContactFormService::class);
        $this->adminMessageService = m::mock(AdminMessageService::class);
        $this->request = m::mock(ServerRequestInterface::class);
        $this->response = m::mock(ResponseInterface::class);
        $this->stream = m::mock(StreamInterface::class);

        $this->controller = new ContactController(
            $this->contactFormService,
            $this->adminMessageService
        );

        // Mock environment variables
        $_ENV['RECAPTCHA_V3_SECRET_KEY'] = 'test_recaptcha_secret';
    }

    protected function tearDown(): void
    {
        m::close();
        unset($_ENV['RECAPTCHA_V3_SECRET_KEY']);
    }

    public function testSubmitContactFormSuccess(): void
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content',
            'recaptcha_token' => 'valid_recaptcha_token'
        ];

        $expectedResult = [
            'success' => true,
            'message' => 'Message received successfully',
            'message_id' => 'msg_123456'
        ];

        // Mock request body
        $this->request->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode($contactData));

        $this->request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => '127.0.0.1']);

        // Mock contact form service
        $this->contactFormService->shouldReceive('processContactForm')
            ->once()
            ->with($contactData)
            ->andReturn($expectedResult);

        // Mock response - need to handle both success and error cases
        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode($expectedResult));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(200)
            ->andReturn($this->response);

        $result = $this->controller->submit($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testSubmitContactFormWithMissingRecaptchaToken(): void
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content'
            // Missing recaptcha_token
        ];

        $this->request->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode($contactData));

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'success' => false,
                'message' => 'reCAPTCHA token missing.'
            ]));

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(400)
            ->andReturn($this->response);

        $result = $this->controller->submit($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testSubmitContactFormWithValidationFailure(): void
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content',
            'recaptcha_token' => 'valid_recaptcha_token'
        ];

        $expectedResult = [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => ['name' => ['Name is required']]
        ];

        $this->request->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode($contactData));

        $this->request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => '127.0.0.1']);

        $this->contactFormService->shouldReceive('processContactForm')
            ->once()
            ->with($contactData)
            ->andReturn($expectedResult);

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode($expectedResult));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(400)
            ->andReturn($this->response);

        $result = $this->controller->submit($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testSubmitContactFormWithServerError(): void
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content',
            'recaptcha_token' => 'valid_recaptcha_token'
        ];

        $this->request->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode($contactData));

        $this->request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => '127.0.0.1']);

        $this->contactFormService->shouldReceive('processContactForm')
            ->once()
            ->with($contactData)
            ->andThrow(new \Exception('Database connection failed'));

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'success' => false,
                'message' => 'An error occurred while processing your request.'
            ]));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(500)
            ->andReturn($this->response);

        $result = $this->controller->submit($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testSubmitContactFormWithMissingRecaptchaSecret(): void
    {
        // Remove recaptcha secret
        unset($_ENV['RECAPTCHA_V3_SECRET_KEY']);

        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content',
            'recaptcha_token' => 'valid_recaptcha_token'
        ];

        $this->request->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode($contactData));

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'success' => false,
                'message' => 'Server configuration error.'
            ]));

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(500)
            ->andReturn($this->response);

        $result = $this->controller->submit($this->request, $this->response);

        $this->assertSame($this->response, $result);

        // Restore recaptcha secret
        $_ENV['RECAPTCHA_V3_SECRET_KEY'] = 'test_recaptcha_secret';
    }

    public function testGetMessageStatusSuccess(): void
    {
        $messageId = 'msg_123456';
        $expectedResult = [
            'success' => true,
            'message_id' => $messageId,
            'status' => 'pending',
            'is_read' => false,
            'created_at' => '2023-01-01T12:00:00+00:00'
        ];

        $this->contactFormService->shouldReceive('getMessageStatus')
            ->once()
            ->with($messageId)
            ->andReturn($expectedResult);

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode($expectedResult));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(200)
            ->andReturn($this->response);

        $args = ['messageId' => $messageId];
        $result = $this->controller->getMessageStatus($this->request, $this->response, $args);

        $this->assertSame($this->response, $result);
    }

    public function testGetMessageStatusWithEmptyMessageId(): void
    {
        $args = ['messageId' => ''];

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'success' => false,
                'message' => 'Message ID is required'
            ]));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(400)
            ->andReturn($this->response);

        $result = $this->controller->getMessageStatus($this->request, $this->response, $args);

        $this->assertSame($this->response, $result);
    }

    public function testGetMessageStatusWithMissingMessageId(): void
    {
        $args = []; // No messageId in args

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode([
                'success' => false,
                'message' => 'Message ID is required'
            ]));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(400)
            ->andReturn($this->response);

        $result = $this->controller->getMessageStatus($this->request, $this->response, $args);

        $this->assertSame($this->response, $result);
    }

    public function testGetMessageStatusNotFound(): void
    {
        $messageId = 'non_existent_message';
        $expectedResult = [
            'success' => false,
            'message' => 'Message not found'
        ];

        $this->contactFormService->shouldReceive('getMessageStatus')
            ->once()
            ->with($messageId)
            ->andReturn($expectedResult);

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(json_encode($expectedResult));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(404)
            ->andReturn($this->response);

        $args = ['messageId' => $messageId];
        $result = $this->controller->getMessageStatus($this->request, $this->response, $args);

        $this->assertSame($this->response, $result);
    }

    public function testSubmitContactFormWithInvalidJson(): void
    {
        $this->request->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn('invalid json');

        $this->request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => '127.0.0.1']);

        $this->response->shouldReceive('getBody->write')
            ->once()
            ->with(m::on(function ($json) {
                $data = json_decode($json, true);
                return $data['success'] === false &&
                       strpos($data['message'], 'error occurred') !== false;
            }));

        $this->response->shouldReceive('withHeader')
            ->once()
            ->with('Content-Type', 'application/json')
            ->andReturn($this->response);

        $this->response->shouldReceive('withStatus')
            ->once()
            ->with(500)
            ->andReturn($this->response);

        $result = $this->controller->submit($this->request, $this->response);

        $this->assertSame($this->response, $result);
    }

    public function testControllerConstructor(): void
    {
        $newController = new ContactController(
            $this->contactFormService,
            $this->adminMessageService
        );

        $this->assertInstanceOf(ContactController::class, $newController);
    }
}