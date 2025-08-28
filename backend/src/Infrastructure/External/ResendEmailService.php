<?php

namespace App\Infrastructure\External;

use App\Domain\Interfaces\EmailServiceInterface;
use Resend;
use Exception;

class ResendEmailService implements EmailServiceInterface
{
    private $resend;
    private string $fromEmail;
    private string $adminEmail;

    public function __construct()
    {
        $this->resend = Resend::client(($_ENV['RESEND_API_KEY'] ?? getenv('RESEND_API_KEY')) ?: '');
        $this->fromEmail = ($_ENV['FROM_EMAIL'] ?? getenv('FROM_EMAIL')) ?: '';
        $this->adminEmail = ($_ENV['ADMIN_EMAIL'] ?? getenv('ADMIN_EMAIL')) ?: '';
    }

    public function sendContactNotification(array $contactData, string $messageId): array
    {
        try {
            $result = $this->resend->emails->send([
                'from' => $this->fromEmail,
                'to' => [$this->adminEmail],
                'subject' => 'New Contact Message: ' . $contactData['subject'],
                'text' => $this->buildAdminEmailBody($contactData),
                'tags' => [
                    ['name' => 'message_id', 'value' => $this->sanitizeTagValue($messageId)],
                    ['name' => 'type', 'value' => 'admin_notification'],
                    ['name' => 'source', 'value' => 'contact_form']
                ]
            ]);

            return $result->toArray();
        } catch (\Exception $e) {
            error_log('Resend API error (admin notification): ' . $e->getMessage());
            throw new \Exception('Failed to send admin notification: ' . $e->getMessage());
        }
    }

    public function sendAutoReply(array $contactData, string $messageId): array
    {
        try {
            $result = $this->resend->emails->send([
                'from' => $this->fromEmail,
                'to' => [$contactData['email']],
                'subject' => 'Thanks for contacting me!',
                'text' => $this->buildAutoReplyBody($contactData['name']),
                'tags' => [
                    ['name' => 'message_id', 'value' => $this->sanitizeTagValue($messageId)],
                    ['name' => 'type', 'value' => 'auto_reply'],
                    ['name' => 'source', 'value' => 'contact_form']
                ]
            ]);

            return $result->toArray();
        } catch (\Exception $e) {
            error_log('Resend API error (auto-reply): ' . $e->getMessage());
            throw new \Exception('Failed to send auto-reply: ' . $e->getMessage());
        }
    }

    public function sendEmail(
        string $to,
        string $subject,
        string $body,
        array $tags = [],
        array $options = []
    ): array {
        try {
            $emailData = array_merge([
                'from' => $this->fromEmail,
                'to' => [$to],
                'subject' => $subject,
                'text' => $body,
            ], $options);

            if (!empty($tags)) {
                $emailData['tags'] = array_map(function ($tag) {
                    return [
                        'name' => $tag['name'],
                        'value' => $this->sanitizeTagValue($tag['value'])
                    ];
                }, $tags);
            }

            $result = $this->resend->emails->send($emailData);
            return $result->toArray();
        } catch (\Exception $e) {
            error_log('Resend API error (custom email): ' . $e->getMessage());
            throw new \Exception('Failed to send email: ' . $e->getMessage());
        }
    }

    public function getServiceStatus(): array
    {
        try {
            // Test the connection by getting account info
            $account = $this->resend->domains->list();
            return [
                'status' => 'connected',
                'message' => 'Resend API connection successful',
                'domains_count' => count($account->toArray()['data'] ?? [])
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Resend API connection failed: ' . $e->getMessage()
            ];
        }
    }

    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function buildAdminEmailBody(array $contactData): string
    {
        return "From: {$contactData['name']} <{$contactData['email']}>\n\n{$contactData['message']}";
    }

    private function buildAutoReplyBody(string $name): string
    {
        return "Hi {$name},\n\nThanks for reaching out. I'll get back to you soon!\n\nBest,\nDamilola";
    }

    private function sanitizeTagValue(string $value): string
    {
        // Replace any character that isn't a letter, number, underscore, or dash with underscore
        return preg_replace('/[^A-Za-z0-9_-]/', '_', $value);
    }
}