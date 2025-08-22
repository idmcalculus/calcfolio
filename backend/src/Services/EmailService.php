<?php

namespace App\Services;

use Resend;
use Exception;

class EmailService
{
    private $resend;
    private $fromEmail;
    private $adminEmail;

    public function __construct()
    {
        $this->resend = Resend::client($_ENV['RESEND_API_KEY']);
        $this->fromEmail = $_ENV['FROM_EMAIL'];
        $this->adminEmail = $_ENV['ADMIN_EMAIL'];
    }

    /**
     * Send admin notification email for new contact form submission
     */
    public function sendContactNotification(array $data, string $messageId): array
    {
        try {
            $result = $this->resend->emails->send([
                'from' => $this->fromEmail,
                'to' => [$this->adminEmail],
                'subject' => 'New Contact Message: ' . $data['subject'],
                'text' => $this->buildAdminEmailBody($data),
                'tags' => [
                    ['name' => 'message_id', 'value' => $messageId],
                    ['name' => 'type', 'value' => 'admin_notification'],
                    ['name' => 'source', 'value' => 'contact_form']
                ]
            ]);

            return $result->toArray();
        } catch (Exception $e) {
            error_log('Resend API error (admin notification): ' . $e->getMessage());
            throw new Exception('Failed to send admin notification: ' . $e->getMessage());
        }
    }

    /**
     * Send auto-reply email to contact form submitter
     */
    public function sendAutoReply(array $data, string $messageId): array
    {
        try {
            $result = $this->resend->emails->send([
                'from' => $this->fromEmail,
                'to' => [$data['email']],
                'subject' => 'Thanks for contacting me!',
                'text' => $this->buildAutoReplyBody($data['name']),
                'tags' => [
                    ['name' => 'message_id', 'value' => $messageId],
                    ['name' => 'type', 'value' => 'auto_reply'],
                    ['name' => 'source', 'value' => 'contact_form']
                ]
            ]);

            return $result->toArray();
        } catch (Exception $e) {
            error_log('Resend API error (auto-reply): ' . $e->getMessage());
            throw new Exception('Failed to send auto-reply: ' . $e->getMessage());
        }
    }

    /**
     * Build the admin email body
     */
    private function buildAdminEmailBody(array $data): string
    {
        return "From: {$data['name']} <{$data['email']}>\n\n{$data['message']}";
    }

    /**
     * Build the auto-reply email body
     */
    private function buildAutoReplyBody(string $name): string
    {
        return "Hi {$name},\n\nThanks for reaching out. I'll get back to you soon!\n\nBest,\nDamilola";
    }

    /**
     * Get API usage statistics (optional feature)
     */
    public function getApiUsage(): array
    {
        try {
            // This would require additional API endpoints from Resend
            // For now, we'll return a placeholder
            return ['status' => 'API connection successful'];
        } catch (Exception $e) {
            error_log('Failed to get Resend API usage: ' . $e->getMessage());
            return ['error' => 'Failed to retrieve API usage'];
        }
    }
}