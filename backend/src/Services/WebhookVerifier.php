<?php

namespace App\Services;

class WebhookVerifier
{
    private $secret;

    public function __construct()
    {
        $this->secret = $_ENV['RESEND_WEBHOOK_SECRET'] ?? '';
    }

    /**
     * Verify webhook signature from Resend
     */
    public function verify(string $payload, string $signature): bool
    {
        if (empty($this->secret)) {
            error_log('Warning: RESEND_WEBHOOK_SECRET not configured, skipping signature verification');
            return true; // Skip verification if no secret configured
        }

        // Remove 'whsec_' prefix if present
        $cleanSignature = str_starts_with($signature, 'whsec_') 
            ? substr($signature, 6) 
            : $signature;

        // Generate expected signature
        $expectedSignature = hash_hmac('sha256', $payload, $this->secret);
        
        // Use hash_equals for timing-safe comparison
        return hash_equals($expectedSignature, $cleanSignature);
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