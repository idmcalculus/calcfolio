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
            $htmlContent = $this->buildAutoReplyHtmlBody($contactData);

            $result = $this->resend->emails->send([
                'from' => $this->fromEmail,
                'to' => [$contactData['email']],
                'subject' => 'Thank you for contacting me!',
                'html' => $htmlContent,
                'text' => $this->buildAutoReplyTextBody($contactData), // Fallback for email clients that don't support HTML
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

    private function buildAutoReplyHtmlBody(array $contactData): string
    {
        $templatePath = __DIR__ . '/email-templates/auto-reply.html';

        if (!file_exists($templatePath)) {
            // Fallback to simple HTML if template file doesn't exist
            return $this->buildFallbackHtmlBody($contactData);
        }

        $template = file_get_contents($templatePath);

        // Replace template variables
        $replacements = [
            '{{name}}' => htmlspecialchars($contactData['name']),
            '{{subject}}' => htmlspecialchars($contactData['subject']),
            '{{portfolio_url}}' => $_ENV['PORTFOLIO_URL'] ?? getenv('PORTFOLIO_URL') ?? 'https://www.idmcalculus.cv',
            '{{linkedin_url}}' => $_ENV['LINKEDIN_URL'] ?? getenv('LINKEDIN_URL') ?? 'https://linkedin.com/in/damilola-michael-ige',
            '{{github_url}}' => $_ENV['GITHUB_URL'] ?? getenv('GITHUB_URL') ?? 'https://github.com/idmcalculus',
            '{{twitter_url}}' => $_ENV['TWITTER_URL'] ?? getenv('TWITTER_URL') ?? 'https://twitter.com/idmcalculus',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    private function buildAutoReplyTextBody(array $contactData): string
    {
        $name = $contactData['name'];
        $subject = $contactData['subject'];

        return "Hi {$name},

Thank you for contacting me through my portfolio website. I truly appreciate you reaching out!

I've received your message regarding \"{$subject}\" and I wanted to personally acknowledge that I've seen it. I typically respond to all messages within 24-48 hours, though sometimes it might take a bit longer depending on my current workload.

What happens next?
I'll carefully review your message and craft a thoughtful response. Whether you're interested in collaboration, have questions about my work, or just want to connect, I'm looking forward to our conversation!

In the meantime, feel free to explore more of my work on my portfolio or connect with me on social media.

Visit my portfolio: https://your-portfolio-url.com

This is an automated response to confirm I've received your message. Please don't reply to this email directly.

Best regards,
Damilola Michael Ige";
    }

    private function buildFallbackHtmlBody(array $contactData): string
    {
        $name = htmlspecialchars($contactData['name']);
        $subject = htmlspecialchars($contactData['subject']);

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Manrope', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
                .header { background: linear-gradient(135deg, #9845E8 0%, #33D2FF 50%, #DD5789 100%); color: white; padding: 30px; text-align: center; }
                .content { padding: 30px; background: #fff; }
                .highlight { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; border-radius: 8px; padding: 20px; margin: 20px 0; }
                .highlight h3 { color: #1d4ed8; margin: 0 0 10px 0; font-size: 18px; }
                .highlight p { color: #1e40af; margin: 0; }
                .cta-button { display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; margin: 20px 0; }
                .footer { background: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Thank You for Reaching Out!</h1>
                    <p>I've received your message and appreciate you contacting me</p>
                </div>
                <div class='content'>
                    <h2>Hi {$name},</h2>
                    <p>Thank you for taking the time to contact me. I've received your message regarding \"<strong>{$subject}</strong>\" and will get back to you soon!</p>
                    <div class='highlight'>
                        <h3>📧 What happens next?</h3>
                        <p>I'll carefully review your message and craft a thoughtful response within 24-48 hours.</p>
                    </div>
                    <div style='text-align: center;'>
                        <a href='" . ($_ENV['PORTFOLIO_URL'] ?? getenv('PORTFOLIO_URL') ?? 'https://www.idmcalculus.cv') . "' class='cta-button'>Visit My Portfolio</a>
                    </div>
                    <p>Best regards,<br>Damilola Michael Ige</p>
                </div>
                <div class='footer'>
                    <p>This is an automated response. Please don't reply to this email directly.</p>
                </div>
            </div>
        </body>
        </html>";
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