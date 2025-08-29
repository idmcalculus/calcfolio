<?php

namespace App\Infrastructure\External;

class WebhookVerifier
{
    private string $secret;

    public function __construct()
    {
        $this->secret = ($_ENV['RESEND_WEBHOOK_SECRET'] ?? getenv('RESEND_WEBHOOK_SECRET')) ?: '';
    }

    /**
     * Verify webhook signature from Svix (used by Resend)
     */
    public function verifySvix(string $payload, string $signature, string $timestamp, string $msgId = ''): bool
    {
        if (empty($this->secret)) {
            error_log('Warning: RESEND_WEBHOOK_SECRET not configured, skipping signature verification');
            return true; // Skip verification if no secret configured
        }

        // Parse Svix signature format: "v1,signature" (can have multiple signatures separated by space)
        $signatures = [];
        foreach (explode(' ', $signature) as $part) {
            if (str_contains($part, ',')) {
                [$version, $sig] = explode(',', $part, 2);
                if ($version === 'v1') {
                    $signatures[] = $sig;
                }
            }
        }

        if (empty($signatures)) {
            error_log('No v1 signature found in Svix header');
            return false;
        }

        // Remove 'whsec_' prefix from secret if present
        $cleanSecret = str_starts_with($this->secret, 'whsec_')
            ? substr($this->secret, 6)
            : $this->secret;

        // Try both signature formats (with and without msgId)
        $signedPayloads = [];
        if (!empty($msgId)) {
            // Svix format with msgId: msgId.timestamp.payload
            $signedPayloads[] = $msgId . '.' . $timestamp . '.' . $payload;
        }
        // Simplified format: timestamp.payload
        $signedPayloads[] = $timestamp . '.' . $payload;

        // Generate expected signatures for both formats
        foreach ($signedPayloads as $signedPayload) {
            $expectedSignature = base64_encode(hash_hmac('sha256', $signedPayload, base64_decode($cleanSecret), true));
            
            // Check if any of the provided signatures match
            foreach ($signatures as $receivedSignature) {
                if (hash_equals($expectedSignature, $receivedSignature)) {
                    return true;
                }
            }
        }

        error_log('Svix webhook signature verification failed');
        return false;
    }

    /**
     * Verify webhook signature from Resend (legacy format)
     */
    public function verify(string $payload, string $signature): bool
    {
        if (empty($this->secret)) {
            error_log('Warning: RESEND_WEBHOOK_SECRET not configured, skipping signature verification');
            return true; // Skip verification if no secret configured
        }

        // Parse Resend signature format: "t=timestamp,v1=signature"
        $parts = [];
        foreach (explode(',', $signature) as $part) {
            if (str_contains($part, '=')) {
                [$key, $value] = explode('=', $part, 2);
                $parts[$key] = $value;
            }
        }

        // Extract timestamp and signature
        $timestamp = $parts['t'] ?? '';
        $receivedSignature = $parts['v1'] ?? '';

        if (empty($timestamp) || empty($receivedSignature)) {
            error_log('Invalid signature format from Resend');
            return false;
        }

        // Create signed payload: timestamp.payload
        $signedPayload = $timestamp . '.' . $payload;

        // Remove 'whsec_' prefix from secret if present (it should be there)
        $cleanSecret = str_starts_with($this->secret, 'whsec_')
            ? substr($this->secret, 6)
            : $this->secret;

        // Generate expected signature using base64 decoded secret
        $expectedSignature = hash_hmac('sha256', $signedPayload, base64_decode($cleanSecret));

        // Use hash_equals for timing-safe comparison
        return hash_equals($expectedSignature, $receivedSignature);
    }

    /**
     * Verify webhook signature with multiple signature formats support
     */
    public function verifyAdvanced(string $payload, string $signatureHeader): bool
    {
        if (empty($this->secret)) {
            error_log('Warning: RESEND_WEBHOOK_SECRET not configured, skipping signature verification');
            return true;
        }

        // Handle different signature formats
        // Resend might send signatures in different formats, e.g., "sha256=signature"
        if (str_contains($signatureHeader, '=')) {
            $parts = explode('=', $signatureHeader, 2);
            if (count($parts) === 2) {
                $algorithm = $parts[0];
                $signature = $parts[1];

                if ($algorithm === 'sha256') {
                    $expectedSignature = hash_hmac('sha256', $payload, $this->secret);
                    return hash_equals($expectedSignature, $signature);
                }
            }
        }

        // Fallback to basic verification
        return $this->verify($payload, $signatureHeader);
    }

    /**
     * Get the configured webhook secret (for testing purposes)
     */
    public function hasSecret(): bool
    {
        return !empty($this->secret);
    }
}