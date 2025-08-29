<?php

namespace App\Tests\Integration;

use App\Application\Controllers\ContactController;
use App\Application\Services\ContactFormService;
use App\Domain\Interfaces\MessageRepositoryInterface;
use App\Domain\Interfaces\EmailServiceInterface;
use App\Domain\Interfaces\ValidationInterface;
use App\Infrastructure\Database\EloquentMessageRepository;
use App\Infrastructure\External\ResendEmailService;
use App\Application\Validators\RequestValidator;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Mockery as m;

class ContactFormIntegrationTest extends TestCase
{
    private ContactController $controller;
    private ContactFormService $service;
    private MessageRepositoryInterface $messageRepository;
    private EmailServiceInterface $emailService;
    private ValidationInterface $validator;

    protected function setUp(): void
    {
        $this->messageRepository = m::mock(MessageRepositoryInterface::class);
        $this->emailService = m::mock(EmailServiceInterface::class);
        $this->validator = m::mock(ValidationInterface::class);

        $this->service = new ContactFormService(
            $this->messageRepository,
            $this->emailService,
            $this->validator
        );

        $this->controller = new ContactController($this->service);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testSuccessfulContactFormSubmission(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Project Inquiry',
            'message' => 'I am interested in your services.',
            'recaptchaToken' => 'valid_token'
        ];

        // Mock validation
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($requestData)
            ->andReturn(true);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        // Mock reCAPTCHA verification
        $this->validator->shouldReceive('verifyRecaptcha')
            ->once()
            ->with('valid_token')
            ->andReturn(['success' => true, 'score' => 0.9]);

        // Mock message creation
        $this->messageRepository->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($data) {
                $message = m::mock(\App\Domain\Entities\Message::class);
                $message->shouldReceive('getId')->andReturn(1);
                $message->shouldReceive('getMessageId')->andReturn('msg_123');
                return $message;
            });

        // Mock email sending
        $this->emailService->shouldReceive('sendContactNotification')
            ->once()
            ->andReturn(['success' => true, 'message_id' => 'msg_123']);

        // Create PSR-7 request
        $request = $this->createRequest('POST', '/contact', $requestData);
        $response = new Response();

        // Execute the controller
        $result = $this->controller->submit($request, $response, []);

        $this->assertEquals(200, $result->getStatusCode());

        $responseBody = (string) $result->getBody();
        $responseData = json_decode($responseBody, true);

        $this->assertTrue($responseData['success']);
        $this->assertEquals('Contact form submitted successfully', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    public function testContactFormWithInvalidData(): void
    {
        $requestData = [
            'name' => '',
            'email' => 'invalid-email',
            'subject' => '',
            'message' => '',
            'recaptchaToken' => 'invalid_token'
        ];

        // Mock validation failure
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($requestData)
            ->andReturn(false);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        $this->validator->shouldReceive('getErrors')
            ->once()
            ->andReturn([
                'name' => 'Name is required',
                'email' => 'Invalid email format',
                'message' => 'Message is required'
            ]);

        // Create PSR-7 request
        $request = $this->createRequest('POST', '/contact', $requestData);
        $response = new Response();

        // Execute the controller
        $result = $this->controller->submit($request, $response, []);

        $this->assertEquals(400, $result->getStatusCode());

        $responseBody = (string) $result->getBody();
        $responseData = json_decode($responseBody, true);

        $this->assertFalse($responseData['success']);
        $this->assertEquals('Validation failed', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testContactFormWithFailedRecaptcha(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test',
            'message' => 'Test message',
            'recaptchaToken' => 'invalid_token'
        ];

        // Mock validation
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($requestData)
            ->andReturn(true);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        // Mock reCAPTCHA failure
        $this->validator->shouldReceive('verifyRecaptcha')
            ->once()
            ->with('invalid_token')
            ->andReturn(['success' => false, 'error' => 'Invalid reCAPTCHA token']);

        // Create PSR-7 request
        $request = $this->createRequest('POST', '/contact', $requestData);
        $response = new Response();

        // Execute the controller
        $result = $this->controller->submit($request, $response, []);

        $this->assertEquals(400, $result->getStatusCode());

        $responseBody = (string) $result->getBody();
        $responseData = json_decode($responseBody, true);

        $this->assertFalse($responseData['success']);
        $this->assertStringContains('reCAPTCHA', $responseData['message']);
    }

    public function testContactFormWithEmailServiceFailure(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test',
            'message' => 'Test message',
            'recaptchaToken' => 'valid_token'
        ];

        // Mock validation
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($requestData)
            ->andReturn(true);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        // Mock reCAPTCHA success
        $this->validator->shouldReceive('verifyRecaptcha')
            ->once()
            ->with('valid_token')
            ->andReturn(['success' => true, 'score' => 0.9]);

        // Mock message creation
        $this->messageRepository->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($data) {
                $message = m::mock(\App\Domain\Entities\Message::class);
                $message->shouldReceive('getId')->andReturn(1);
                $message->shouldReceive('getMessageId')->andReturn('msg_123');
                return $message;
            });

        // Mock email service failure
        $this->emailService->shouldReceive('sendContactNotification')
            ->once()
            ->andReturn(['success' => false, 'error' => 'Email service unavailable']);

        // Create PSR-7 request
        $request = $this->createRequest('POST', '/contact', $requestData);
        $response = new Response();

        // Execute the controller
        $result = $this->controller->submit($request, $response, []);

        $this->assertEquals(500, $result->getStatusCode());

        $responseBody = (string) $result->getBody();
        $responseData = json_decode($responseBody, true);

        $this->assertFalse($responseData['success']);
        $this->assertStringContains('Failed to send email', $responseData['message']);
    }

    public function testGetMessageStatus(): void
    {
        // Mock message retrieval
        $this->messageRepository->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturnUsing(function () {
                $message = m::mock(\App\Domain\Entities\Message::class);
                $message->shouldReceive('toArray')
                    ->once()
                    ->andReturn([
                        'id' => 1,
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                        'subject' => 'Test',
                        'message' => 'Test message',
                        'status' => 'delivered',
                        'is_read' => false,
                        'created_at' => '2023-01-01T10:00:00Z',
                        'updated_at' => '2023-01-01T11:00:00Z'
                    ]);
                return $message;
            });

        // Create PSR-7 request
        $request = $this->createRequest('GET', '/message/1');
        $response = new Response();

        // Execute the controller
        $result = $this->controller->getMessage($request, $response, ['messageId' => '1']);

        $this->assertEquals(200, $result->getStatusCode());

        $responseBody = (string) $result->getBody();
        $responseData = json_decode($responseBody, true);

        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('delivered', $responseData['data']['status']);
    }

    public function testGetMessageStatusNotFound(): void
    {
        // Mock message not found
        $this->messageRepository->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andReturn(null);

        // Create PSR-7 request
        $request = $this->createRequest('GET', '/message/999');
        $response = new Response();

        // Execute the controller
        $result = $this->controller->getMessage($request, $response, ['messageId' => '999']);

        $this->assertEquals(404, $result->getStatusCode());

        $responseBody = (string) $result->getBody();
        $responseData = json_decode($responseBody, true);

        $this->assertFalse($responseData['success']);
        $this->assertEquals('Message not found', $responseData['message']);
    }

    public function testContactFormWithDatabaseError(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test',
            'message' => 'Test message',
            'recaptchaToken' => 'valid_token'
        ];

        // Mock validation
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($requestData)
            ->andReturn(true);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        // Mock reCAPTCHA success
        $this->validator->shouldReceive('verifyRecaptcha')
            ->once()
            ->with('valid_token')
            ->andReturn(['success' => true, 'score' => 0.9]);

        // Mock database error
        $this->messageRepository->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('Database connection failed'));

        // Create PSR-7 request
        $request = $this->createRequest('POST', '/contact', $requestData);
        $response = new Response();

        // Execute the controller
        $result = $this->controller->submit($request, $response, []);

        $this->assertEquals(500, $result->getStatusCode());

        $responseBody = (string) $result->getBody();
        $responseData = json_decode($responseBody, true);

        $this->assertFalse($responseData['success']);
        $this->assertStringContains('Failed to save message', $responseData['message']);
    }

    public function testContactFormWithLowRecaptchaScore(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test',
            'message' => 'Test message',
            'recaptchaToken' => 'suspicious_token'
        ];

        // Mock validation
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($requestData)
            ->andReturn(true);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        // Mock low reCAPTCHA score
        $this->validator->shouldReceive('verifyRecaptcha')
            ->once()
            ->with('suspicious_token')
            ->andReturn(['success' => true, 'score' => 0.3]);

        // Create PSR-7 request
        $request = $this->createRequest('POST', '/contact', $requestData);
        $response = new Response();

        // Execute the controller
        $result = $this->controller->submit($request, $response, []);

        $this->assertEquals(400, $result->getStatusCode());

        $responseBody = (string) $result->getBody();
        $responseData = json_decode($responseBody, true);

        $this->assertFalse($responseData['success']);
        $this->assertStringContains('reCAPTCHA', $responseData['message']);
    }

    private function createRequest(string $method, string $path, array $data = []): Request
    {
        $stream = fopen('php://temp', 'r+');
        if (!empty($data)) {
            fwrite($stream, json_encode($data));
            rewind($stream);
        }

        return new Request(
            $method,
            $path,
            [],
            $stream,
            ['Content-Type' => 'application/json']
        );
    }
}