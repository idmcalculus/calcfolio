<?php

namespace App\Domain\Interfaces;

interface EmailServiceInterface
{
    /**
     * Send contact notification to admin
     */
    public function sendContactNotification(array $contactData, string $messageId): array;

    /**
     * Send auto-reply to contact submitter
     */
    public function sendAutoReply(array $contactData, string $messageId): array;

    /**
     * Send custom email with template
     */
    public function sendEmail(
        string $to,
        string $subject,
        string $body,
        array $tags = [],
        array $options = []
    ): array;

    /**
     * Get email service status/health
     */
    public function getServiceStatus(): array;

    /**
     * Validate email address format
     */
    public function validateEmail(string $email): bool;
}