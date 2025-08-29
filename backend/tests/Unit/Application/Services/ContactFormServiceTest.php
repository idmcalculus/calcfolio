<?php

namespace App\Tests\Unit\Application\Services;

use App\Application\Services\ContactFormService;
use App\Domain\Entities\Message;
use App\Domain\Interfaces\MessageRepositoryInterface;
use App\Domain\Interfaces\EmailServiceInterface;
use App\Domain\Interfaces\ValidationInterface;
use App\Domain\ValueObjects\EmailAddress;
use App\Domain\ValueObjects\MessageStatus;
use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class ContactFormServiceTest extends TestCase
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

    public function testProcessContactFormSuccess(): void
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content'
        ];

        // Mock validation success
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($contactData)
            ->andReturn($contactData);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        // Mock email services
        $this->emailService->shouldReceive('sendContactNotification')
            ->once()
            ->andReturn(['id' => 'email_123']);

        $this->emailService->shouldReceive('sendAutoReply')
            ->once()
            ->andReturn(['id' => 'reply_123']);

        // Mock repository
        $message = new Message(
            1,
            'John Doe',
            new EmailAddress('john@example.com'),
            'Test Subject',
            'Test message content',
            'msg_test123_abc123',
            MessageStatus::pending(),
            false
        );

        $this->messageRepository->shouldReceive('create')
            ->once()
            ->andReturn($message);

        $result = $this->service->processContactForm($contactData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Message received successfully', $result['message']);
        $this->assertStringStartsWith('msg_', $result['message_id']);
        $this->assertArrayHasKey('data', $result);
    }

    public function testProcessContactFormValidationFailure(): void
    {
        $contactData = [
            'name' => '',
            'email' => 'invalid-email',
            'subject' => '',
            'message' => ''
        ];

        // Mock validation failure
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($contactData)
            ->andReturn([]);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        $this->validator->shouldReceive('getErrors')
            ->once()
            ->andReturn(['name' => ['Name is required']]);

        $result = $this->service->processContactForm($contactData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function testProcessContactFormEmailServiceFailure(): void
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content'
        ];

        // Mock validation success
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($contactData)
            ->andReturn($contactData);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        // Mock email service failure
        $this->emailService->shouldReceive('sendContactNotification')
            ->once()
            ->andThrow(new Exception('Email service error'));

        $result = $this->service->processContactForm($contactData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to process contact form', $result['message']);
        $this->assertArrayHasKey('error', $result);
    }

    public function testGetMessageStatusSuccess(): void
    {
        $messageId = 'msg_test123_abc123';
        $message = new Message(
            1,
            'John Doe',
            new EmailAddress('john@example.com'),
            'Test Subject',
            'Test message content',
            $messageId,
            MessageStatus::delivered(),
            true,
            new DateTimeImmutable('2023-01-01 12:00:00')
        );

        $this->messageRepository->shouldReceive('findByMessageId')
            ->once()
            ->with($messageId)
            ->andReturn($message);

        $result = $this->service->getMessageStatus($messageId);

        $this->assertTrue($result['success']);
        $this->assertEquals($messageId, $result['message_id']);
        $this->assertEquals('delivered', $result['status']);
        $this->assertTrue($result['is_read']);
        $this->assertArrayHasKey('created_at', $result);
    }

    public function testGetMessageStatusNotFound(): void
    {
        $messageId = 'msg_nonexistent';

        $this->messageRepository->shouldReceive('findByMessageId')
            ->once()
            ->with($messageId)
            ->andReturn(null);

        $result = $this->service->getMessageStatus($messageId);

        $this->assertFalse($result['success']);
        $this->assertEquals('Message not found', $result['message']);
    }

    public function testMessageIdGeneration(): void
    {
        // Test that message IDs are generated with the correct format
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateMessageId');
        $method->setAccessible(true);

        $messageId = $method->invoke($this->service);

        $this->assertStringStartsWith('msg_', $messageId);
        $this->assertStringContainsString('_', $messageId);
        $this->assertGreaterThan(10, strlen($messageId)); // Should be reasonably long
    }

    public function testProcessContactFormWithRecaptchaToken(): void
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content',
            'recaptcha_token' => 'recaptcha_token_123'
        ];

        // Mock validation success
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($contactData)
            ->andReturn($contactData);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        // Mock email services
        $this->emailService->shouldReceive('sendContactNotification')
            ->once()
            ->andReturn(['id' => 'email_123']);

        $this->emailService->shouldReceive('sendAutoReply')
            ->once()
            ->andReturn(['id' => 'reply_123']);

        // Mock repository
        $message = new Message(
            1,
            'John Doe',
            new EmailAddress('john@example.com'),
            'Test Subject',
            'Test message content',
            'msg_test123_abc123',
            MessageStatus::pending(),
            false
        );

        $this->messageRepository->shouldReceive('create')
            ->once()
            ->andReturn($message);

        $result = $this->service->processContactForm($contactData);

        $this->assertTrue($result['success']);
        $this->assertStringStartsWith('msg_', $result['message_id']);
    }

    public function testProcessContactFormHandlesRepositoryException(): void
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content'
        ];

        // Mock validation success
        $this->validator->shouldReceive('validateContactForm')
            ->once()
            ->with($contactData)
            ->andReturn($contactData);

        $this->validator->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        // Mock email services success
        $this->emailService->shouldReceive('sendContactNotification')
            ->once()
            ->andReturn(['id' => 'email_123']);

        $this->emailService->shouldReceive('sendAutoReply')
            ->once()
            ->andReturn(['id' => 'reply_123']);

        // Mock repository failure
        $this->messageRepository->shouldReceive('create')
            ->once()
            ->andThrow(new Exception('Database connection failed'));

        $result = $this->service->processContactForm($contactData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to process contact form', $result['message']);
        $this->assertArrayHasKey('error', $result);
    }
}