<?php

namespace App\Tests\Unit\Infrastructure\External;

use App\Infrastructure\External\WebhookVerifier;
use PHPUnit\Framework\TestCase;

class WebhookVerifierTest extends TestCase
{
    private WebhookVerifier $verifier;

    protected function setUp(): void
    {
        // Set up test environment variables
        $_ENV['RESEND_WEBHOOK_SECRET'] = 'whsec_test_secret_key_for_webhook_verification';
        $this->verifier = new WebhookVerifier();
    }

    protected function tearDown(): void
    {
        unset($_ENV['RESEND_WEBHOOK_SECRET']);
    }

    public function testConstructorWithEnvironmentVariable(): void
    {
        $this->assertInstanceOf(WebhookVerifier::class, $this->verifier);
        $this->assertTrue($this->verifier->hasSecret());
    }

    public function testConstructorWithoutEnvironmentVariable(): void
    {
        unset($_ENV['RESEND_WEBHOOK_SECRET']);
        $verifier = new WebhookVerifier();
        $this->assertFalse($verifier->hasSecret());
    }

    public function testVerifySvixWithValidSignature(): void
    {
        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';
        $timestamp = '1640995200';
        $msgId = 'msg_123456';

        // Create a valid signature using the test secret
        $cleanSecret = substr($_ENV['RESEND_WEBHOOK_SECRET'], 6); // Remove 'whsec_' prefix
        $signedPayload = $timestamp . '.' . $payload;
        $expectedSignature = base64_encode(hash_hmac('sha256', $signedPayload, base64_decode($cleanSecret), true));

        $signature = 'v1,' . $expectedSignature;

        $result = $this->verifier->verifySvix($payload, $signature, $timestamp, $msgId);

        $this->assertTrue($result);
    }

    public function testVerifySvixWithValidSignatureAndMsgId(): void
    {
        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';
        $timestamp = '1640995200';
        $msgId = 'msg_123456';

        // Create a valid signature with msgId
        $cleanSecret = substr($_ENV['RESEND_WEBHOOK_SECRET'], 6);
        $signedPayload = $msgId . '.' . $timestamp . '.' . $payload;
        $expectedSignature = base64_encode(hash_hmac('sha256', $signedPayload, base64_decode($cleanSecret), true));

        $signature = 'v1,' . $expectedSignature;

        $result = $this->verifier->verifySvix($payload, $signature, $timestamp, $msgId);

        $this->assertTrue($result);
    }

    public function testVerifySvixWithInvalidSignature(): void
    {
        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';
        $timestamp = '1640995200';
        $signature = 'v1,invalid_signature';

        $result = $this->verifier->verifySvix($payload, $signature, $timestamp);

        $this->assertFalse($result);
    }

    public function testVerifySvixWithMultipleSignatures(): void
    {
        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';
        $timestamp = '1640995200';

        // Create valid signature
        $cleanSecret = substr($_ENV['RESEND_WEBHOOK_SECRET'], 6);
        $signedPayload = $timestamp . '.' . $payload;
        $validSignature = base64_encode(hash_hmac('sha256', $signedPayload, base64_decode($cleanSecret), true));

        // Create signature with multiple values (valid one first)
        $signature = 'v1,' . $validSignature . ' v1,invalid_signature';

        $result = $this->verifier->verifySvix($payload, $signature, $timestamp);

        $this->assertTrue($result);
    }

    public function testVerifySvixWithoutSecret(): void
    {
        unset($_ENV['RESEND_WEBHOOK_SECRET']);
        $verifier = new WebhookVerifier();

        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';
        $signature = 'v1,test_signature';
        $timestamp = '1640995200';

        $result = $verifier->verifySvix($payload, $signature, $timestamp);

        // Should return true when no secret is configured (skips verification)
        $this->assertTrue($result);
    }

    public function testVerifySvixWithNoV1Signature(): void
    {
        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';
        $signature = 'v2,test_signature';
        $timestamp = '1640995200';

        $result = $this->verifier->verifySvix($payload, $signature, $timestamp);

        $this->assertFalse($result);
    }

    public function testVerifyWithValidSignature(): void
    {
        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';
        $timestamp = '1640995200';

        // Create valid signature
        $cleanSecret = substr($_ENV['RESEND_WEBHOOK_SECRET'], 6);
        $signedPayload = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, base64_decode($cleanSecret));

        $signature = 't=' . $timestamp . ',v1=' . $expectedSignature;

        $result = $this->verifier->verify($payload, $signature);

        $this->assertTrue($result);
    }

    public function testVerifyWithInvalidSignature(): void
    {
        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';
        $timestamp = '1640995200';
        $signature = 't=' . $timestamp . ',v1=invalid_signature';

        $result = $this->verifier->verify($payload, $signature);

        $this->assertFalse($result);
    }

    public function testVerifyWithMissingTimestamp(): void
    {
        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';
        $signature = 'v1=test_signature';

        $result = $this->verifier->verify($payload, $signature);

        $this->assertFalse($result);
    }

    public function testVerifyWithMissingSignature(): void
    {
        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';
        $timestamp = '1640995200';
        $signature = 't=' . $timestamp;

        $result = $this->verifier->verify($payload, $signature);

        $this->assertFalse($result);
    }

    public function testVerifyWithoutSecret(): void
    {
        unset($_ENV['RESEND_WEBHOOK_SECRET']);
        $verifier = new WebhookVerifier();

        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';
        $signature = 't=1640995200,v1=test_signature';

        $result = $verifier->verify($payload, $signature);

        // Should return true when no secret is configured
        $this->assertTrue($result);
    }

    public function testVerifyAdvancedWithSha256Format(): void
    {
        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';

        // Create valid signature
        $expectedSignature = hash_hmac('sha256', $payload, $_ENV['RESEND_WEBHOOK_SECRET']);
        $signatureHeader = 'sha256=' . $expectedSignature;

        $result = $this->verifier->verifyAdvanced($payload, $signatureHeader);

        $this->assertTrue($result);
    }

    public function testVerifyAdvancedWithInvalidSha256Format(): void
    {
        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';
        $signatureHeader = 'sha256=invalid_signature';

        $result = $this->verifier->verifyAdvanced($payload, $signatureHeader);

        $this->assertFalse($result);
    }

    public function testVerifyAdvancedFallbackToBasic(): void
    {
        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';
        $timestamp = '1640995200';

        // Create valid signature in Resend format
        $cleanSecret = substr($_ENV['RESEND_WEBHOOK_SECRET'], 6);
        $signedPayload = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, base64_decode($cleanSecret));

        $signatureHeader = 't=' . $timestamp . ',v1=' . $expectedSignature;

        $result = $this->verifier->verifyAdvanced($payload, $signatureHeader);

        $this->assertTrue($result);
    }

    public function testVerifyAdvancedWithoutSecret(): void
    {
        unset($_ENV['RESEND_WEBHOOK_SECRET']);
        $verifier = new WebhookVerifier();

        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';
        $signatureHeader = 'sha256=test_signature';

        $result = $verifier->verifyAdvanced($payload, $signatureHeader);

        // Should return true when no secret is configured
        $this->assertTrue($result);
    }

    public function testHasSecretReturnsTrueWhenConfigured(): void
    {
        $this->assertTrue($this->verifier->hasSecret());
    }

    public function testHasSecretReturnsFalseWhenNotConfigured(): void
    {
        unset($_ENV['RESEND_WEBHOOK_SECRET']);
        $verifier = new WebhookVerifier();

        $this->assertFalse($verifier->hasSecret());
    }

    public function testSecretWithoutWhsecPrefix(): void
    {
        // Test with secret that doesn't have whsec_ prefix
        $_ENV['RESEND_WEBHOOK_SECRET'] = 'test_secret_key_without_prefix';
        $verifier = new WebhookVerifier();

        $payload = '{"event":"email.delivered","data":{"email_id":"test"}}';
        $timestamp = '1640995200';

        // Create signature using the secret as-is
        $signedPayload = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, base64_decode($_ENV['RESEND_WEBHOOK_SECRET']));

        $signature = 't=' . $timestamp . ',v1=' . $expectedSignature;

        $result = $verifier->verify($payload, $signature);

        $this->assertTrue($result);
    }
}