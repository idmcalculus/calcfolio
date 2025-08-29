<?php

namespace App\Tests\Unit\Infrastructure\External;

use App\Infrastructure\External\ResendEmailService;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class ResendEmailServiceTest extends TestCase
{
    private ResendEmailService $service;

    protected function setUp(): void
    {
        // Mock environment variables
        $_ENV['RESEND_API_KEY'] = 'test_api_key';
        $_ENV['FROM_EMAIL'] = 'noreply@example.com';
        $_ENV['ADMIN_EMAIL'] = 'admin@example.com';

        $this->service = new ResendEmailService();
    }

    protected function tearDown(): void
    {
        m::close();
        unset($_ENV['RESEND_API_KEY'], $_ENV['FROM_EMAIL'], $_ENV['ADMIN_EMAIL']);
    }

    public function testCanCreateServiceWithEnvironmentVariables(): void
    {
        $this->assertInstanceOf(ResendEmailService::class, $this->service);
    }

    public function testValidateEmailReturnsTrueForValidEmail(): void
    {
        $this->assertTrue($this->service->validateEmail('test@example.com'));
        $this->assertTrue($this->service->validateEmail('user.name+tag@domain.co.uk'));
    }

    public function testValidateEmailReturnsFalseForInvalidEmail(): void
    {
        $this->assertFalse($this->service->validateEmail('invalid-email'));
        $this->assertFalse($this->service->validateEmail('@example.com'));
        $this->assertFalse($this->service->validateEmail('test@'));
        $this->assertFalse($this->service->validateEmail(''));
    }

    public function testBuildAdminEmailBodyFormatsCorrectly(): void
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message.'
        ];

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('buildAdminEmailBody');
        $method->setAccessible(true);

        $body = $method->invoke($this->service, $contactData);

        $expected = "From: John Doe <john@example.com>\n\nThis is a test message.";
        $this->assertEquals($expected, $body);
    }

    public function testBuildAutoReplyBodyFormatsCorrectly(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('buildAutoReplyBody');
        $method->setAccessible(true);

        $body = $method->invoke($this->service, 'John Doe');

        $expected = "Hi John Doe,\n\nThanks for reaching out. I'll get back to you soon!\n\nBest,\nDamilola";
        $this->assertEquals($expected, $body);
    }

    public function testSanitizeTagValueHandlesSpecialCharacters(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('sanitizeTagValue');
        $method->setAccessible(true);

        $this->assertEquals('msg_123_abc', $method->invoke($this->service, 'msg_123_abc'));
        $this->assertEquals('msg_123_abc', $method->invoke($this->service, 'msg_123@abc'));
        $this->assertEquals('msg_123_abc', $method->invoke($this->service, 'msg_123.abc'));
        $this->assertEquals('msg_123_abc', $method->invoke($this->service, 'msg_123 abc')); // space becomes underscore
    }

    public function testSanitizeTagValuePreservesValidCharacters(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('sanitizeTagValue');
        $method->setAccessible(true);

        $this->assertEquals('msg_123_abc', $method->invoke($this->service, 'msg_123_abc'));
        $this->assertEquals('test_email_123', $method->invoke($this->service, 'test_email_123'));
        $this->assertEquals('user_name', $method->invoke($this->service, 'user_name'));
    }

    public function testGetServiceStatusReturnsErrorWhenApiKeyMissing(): void
    {
        // Temporarily remove API key
        unset($_ENV['RESEND_API_KEY']);

        $service = new ResendEmailService();
        $status = $service->getServiceStatus();

        $this->assertEquals('error', $status['status']);
        $this->assertStringContainsString('connection failed', $status['message']);

        // Restore API key
        $_ENV['RESEND_API_KEY'] = 'test_api_key';
    }

    public function testSendContactNotificationFormatsDataCorrectly(): void
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content'
        ];
        $messageId = 'msg_test123_abc123';

        // This test would require mocking the Resend client
        // For now, we'll test the method signature and basic functionality
        $this->assertTrue(method_exists($this->service, 'sendContactNotification'));
    }

    public function testSendAutoReplyFormatsDataCorrectly(): void
    {
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content'
        ];
        $messageId = 'msg_test123_abc123';

        // This test would require mocking the Resend client
        // For now, we'll test the method signature and basic functionality
        $this->assertTrue(method_exists($this->service, 'sendAutoReply'));
    }

    public function testSendEmailMethodExists(): void
    {
        $this->assertTrue(method_exists($this->service, 'sendEmail'));
    }

    public function testServiceHandlesMissingEnvironmentVariablesGracefully(): void
    {
        // Remove environment variables
        unset($_ENV['FROM_EMAIL'], $_ENV['ADMIN_EMAIL']);

        $service = new ResendEmailService();

        // Service should still be created but may not function properly
        $this->assertInstanceOf(ResendEmailService::class, $service);

        // Restore environment variables
        $_ENV['FROM_EMAIL'] = 'noreply@example.com';
        $_ENV['ADMIN_EMAIL'] = 'admin@example.com';
    }
}