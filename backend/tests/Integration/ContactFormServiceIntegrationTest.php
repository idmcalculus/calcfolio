<?php

namespace App\Tests\Integration;

use App\Application\Services\ContactFormService;
use App\Domain\Interfaces\MessageRepositoryInterface;
use App\Domain\Interfaces\EmailServiceInterface;
use App\Domain\Interfaces\ValidationInterface;
use App\Domain\Entities\Message;
use App\Domain\ValueObjects\EmailAddress;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class ContactFormServiceIntegrationTest extends TestCase
{
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
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testSuccessfulContactFormProcessing(): void
    {
        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Project Inquiry',
            'message' => 'I am interested in your services.',
            'recaptchaToken' => 'valid_token'
        ];

        // Mock validation success
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($formData)
            ->andReturn(['success' => true]);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        // Mock message creation
        $this->messageRepository->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($data) {
                $message = m::mock(Message::class);
                $message->shouldReceive('getId')->andReturn(1);
                $message->shouldReceive('getMessageId')->andReturn('msg_123');
                $message->shouldReceive('toArray')->andReturn([
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'subject' => 'Project Inquiry',
                    'message' => 'I am interested in your services.',
                    'message_id' => 'msg_123',
                    'status' => 'pending',
                    'is_read' => false,
                    'created_at' => '2023-01-01T10:00:00Z',
                    'updated_at' => '2023-01-01T10:00:00Z'
                ]);
                return $message;
            });

        // Mock email sending
        $this->emailService->shouldReceive('sendContactNotification')
            ->once()
            ->andReturn(['success' => true, 'message_id' => 'msg_123']);

        $this->emailService->shouldReceive('sendAutoReply')
            ->once()
            ->andReturn(['success' => true]);

        $result = $this->service->processContactForm($formData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Message received successfully', $result['message']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals(1, $result['data']['id']);
    }

    public function testContactFormWithValidationFailure(): void
    {
        $formData = [
            'name' => '',
            'email' => 'invalid-email',
            'subject' => '',
            'message' => '',
            'recaptchaToken' => 'token'
        ];

        // Mock validation failure
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($formData)
            ->andReturn(['success' => false]);

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

        $result = $this->service->processContactForm($formData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertCount(3, $result['errors']);
    }

    public function testContactFormWithEmailServiceFailure(): void
    {
        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test',
            'message' => 'Test message',
            'recaptchaToken' => 'valid_token'
        ];

        // Mock validation success
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($formData)
            ->andReturn(['success' => true]);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        // Mock email service failure
        $this->emailService->shouldReceive('sendContactNotification')
            ->once()
            ->andReturn(['success' => false, 'error' => 'Email service unavailable']);

        $result = $this->service->processContactForm($formData);

        $this->assertFalse($result['success']);
        $this->assertStringContains('Failed to process contact form', $result['message'] ?? $result['error'] ?? '');
    }

    public function testContactFormWithDatabaseError(): void
    {
        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test',
            'message' => 'Test message',
            'recaptchaToken' => 'valid_token'
        ];

        // Mock validation success
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($formData)
            ->andReturn(['success' => true]);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        // Mock email success
        $this->emailService->shouldReceive('sendContactNotification')
            ->once()
            ->andReturn(['success' => true]);

        $this->emailService->shouldReceive('sendAutoReply')
            ->once()
            ->andReturn(['success' => true]);

        // Mock database error
        $this->messageRepository->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('Database connection failed'));

        $result = $this->service->processContactForm($formData);

        $this->assertFalse($result['success']);
        $this->assertStringContains('Failed to process contact form', $result['message']);
    }

    public function testContactFormWithMissingRequiredFields(): void
    {
        $formData = [
            'name' => '',
            'email' => '',
            'subject' => '',
            'message' => '',
            'recaptchaToken' => ''
        ];

        // Mock validation failure for all empty fields
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($formData)
            ->andReturn(['success' => false]);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        $this->validator->shouldReceive('getErrors')
            ->once()
            ->andReturn([
                'name' => 'Name is required',
                'email' => 'Email is required',
                'message' => 'Message is required'
            ]);

        $result = $this->service->processContactForm($formData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertCount(3, $result['errors']);
    }

    public function testSuccessfulContactFormWithAllFields(): void
    {
        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Optional Subject',
            'message' => 'This is a test message with all fields filled.',
            'recaptchaToken' => 'valid_token'
        ];

        // Mock validation success
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($formData)
            ->andReturn(['success' => true]);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        // Mock message creation
        $this->messageRepository->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($data) {
                $message = m::mock(Message::class);
                $message->shouldReceive('getId')->andReturn(1);
                $message->shouldReceive('getMessageId')->andReturn('msg_123');
                $message->shouldReceive('toArray')->andReturn([
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'subject' => 'Optional Subject',
                    'message' => 'This is a test message with all fields filled.',
                    'message_id' => 'msg_123',
                    'status' => 'pending',
                    'is_read' => false,
                    'created_at' => '2023-01-01T10:00:00Z',
                    'updated_at' => '2023-01-01T10:00:00Z'
                ]);
                return $message;
            });

        // Mock email sending
        $this->emailService->shouldReceive('sendContactNotification')
            ->once()
            ->andReturn(['success' => true, 'message_id' => 'msg_123']);

        $this->emailService->shouldReceive('sendAutoReply')
            ->once()
            ->andReturn(['success' => true]);

        $result = $this->service->processContactForm($formData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Message received successfully', $result['message']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('message_id', $result);
    }

    public function testGetMessageStatusSuccess(): void
    {
        $messageId = 'msg_123';

        // Mock message retrieval
        $this->messageRepository->shouldReceive('findByMessageId')
            ->once()
            ->with($messageId)
            ->andReturnUsing(function () {
                $message = m::mock(Message::class);
                $message->shouldReceive('getStatus')->andReturn(\App\Domain\ValueObjects\MessageStatus::delivered());
                $message->shouldReceive('isRead')->andReturn(true);
                $message->shouldReceive('getCreatedAt')->andReturn(new \DateTimeImmutable('2023-01-01 10:00:00'));
                return $message;
            });

        $result = $this->service->getMessageStatus($messageId);

        $this->assertTrue($result['success']);
        $this->assertEquals('delivered', $result['status']);
        $this->assertTrue($result['is_read']);
        $this->assertArrayHasKey('created_at', $result);
    }

    public function testGetMessageStatusNotFound(): void
    {
        $messageId = 'nonexistent';

        // Mock message not found
        $this->messageRepository->shouldReceive('findByMessageId')
            ->once()
            ->with($messageId)
            ->andReturn(null);

        $result = $this->service->getMessageStatus($messageId);

        $this->assertFalse($result['success']);
        $this->assertEquals('Message not found', $result['message']);
    }
}