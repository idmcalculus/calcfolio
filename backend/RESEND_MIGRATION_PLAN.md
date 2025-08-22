# Resend Migration Architecture Plan

## Overview
This document outlines the migration from Amazon SES to Resend for email operations in the backend portfolio contact form application.

## Current Architecture Analysis

### Current Implementation (SES)
- **Email Transport**: Symfony Mailer with SMTP transport
- **Configuration**: SMTP DSN with AWS credentials
- **Tracking**: SES-specific headers (X-SES-MESSAGE-TAGS, X-SES-CONFIGURATION-SET)
- **Webhooks**: SES webhook endpoint (`/ses-webhook`) processing SNS notifications
- **Dependencies**: `symfony/mailer`, `symfony/http-client`

### Current Email Flow
1. Contact form submission received
2. reCAPTCHA verification
3. Email creation using Symfony Mailer
4. Two emails sent: admin notification + auto-reply
5. Message saved to database with unique message ID
6. SES webhook processes delivery status updates

## Target Architecture (Resend)

### New Implementation Structure
```
src/
├── Services/
│   ├── EmailService.php          # Resend service wrapper
│   └── WebhookVerifier.php       # Webhook signature verification
├── Models/
│   └── Message.php               # Existing model (minimal changes)
└── index.php                     # Updated route handlers
```

### Dependencies Update
- **Add**: `resend/resend-php` ^0.8.0
- **Keep**: `symfony/mailer` (optional - can be removed later)
- **Keep**: All existing dependencies

## Implementation Plan

### Phase 1: Foundation Setup

#### 1.1 Update composer.json
```json
{
    "require": {
        "php": "^8.3",
        "slim/slim": "^4.14",
        "slim/psr7": "^1.7",
        "resend/resend-php": "^0.8.0",
        "symfony/mailer": "^7.2",
        "symfony/http-client": "^7.2",
        "vlucas/phpdotenv": "^5.6",
        "illuminate/database": "^12.10",
        "google/recaptcha": "^1.3",
        "illuminate/pagination": "^12.10"
    }
}
```

#### 1.2 Environment Variables
```bash
# New Resend configuration
RESEND_API_KEY=re_xxxxxxxxxxxxxxxxxx
RESEND_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxxx

# Keep existing for transition
FROM_EMAIL=idm.calculus@gmail.com
ADMIN_EMAIL=igedami.calculus@gmail.com

# Legacy SES (can be removed after migration)
# SMTP_DSN=smtp://...
# SES_CONFIGURATION_SET=test
# SES_WEBHOOK_SECRET=new_webhook_secret
```

### Phase 2: Service Layer Implementation

#### 2.1 EmailService.php
```php
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

    public function sendContactNotification(array $data, string $messageId): array
    {
        return $this->resend->emails->send([
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
    }

    public function sendAutoReply(array $data, string $messageId): array
    {
        return $this->resend->emails->send([
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
    }

    private function buildAdminEmailBody(array $data): string
    {
        return "From: {$data['name']} <{$data['email']}>\n\n{$data['message']}";
    }

    private function buildAutoReplyBody(string $name): string
    {
        return "Hi {$name},\n\nThanks for reaching out. I'll get back to you soon!\n\nBest,\nDamilola";
    }
}
```

#### 2.2 WebhookVerifier.php
```php
<?php

namespace App\Services;

class WebhookVerifier
{
    private $secret;

    public function __construct()
    {
        $this->secret = $_ENV['RESEND_WEBHOOK_SECRET'] ?? '';
    }

    public function verify(string $payload, string $signature): bool
    {
        if (empty($this->secret)) {
            return true; // Skip verification if no secret configured
        }

        // Implement Resend webhook signature verification
        $expectedSignature = hash_hmac('sha256', $payload, $this->secret);
        return hash_equals($signature, $expectedSignature);
    }
}
```

### Phase 3: Route Handler Updates

#### 3.1 Contact Form Endpoint Updates
```php
// Replace lines 132-229 in index.php
$app->post('/contact', function (Request $request, Response $response) {
    try {
        $data = json_decode($request->getBody()->getContents(), true);
        
        // reCAPTCHA verification (keep existing logic)
        // ...existing reCAPTCHA code...

        // Validation (keep existing logic)
        // ...existing validation code...

        $messageId = uniqid('msg_', true);
        
        // NEW: Use Resend EmailService
        $emailService = new App\Services\EmailService();
        
        try {
            $adminEmailResult = $emailService->sendContactNotification($data, $messageId);
            $autoReplyResult = $emailService->sendAutoReply($data, $messageId);
            
            // Save to database (keep existing logic)
            Message::create([
                'name' => $data['name'],
                'email' => $email,
                'subject' => $data['subject'],
                'message' => $data['message'],
                'message_id' => $messageId
            ]);

            $response->getBody()->write(json_encode([
                'success' => true, 
                'message' => 'Message received. Thank you!'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (Exception $e) {
            throw new Exception('Email delivery failed: ' . $e->getMessage());
        }
    } catch (Exception $e) {
        error_log('Contact form error: ' . $e->getMessage());
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'An error occurred',
            'debug' => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});
```

#### 3.2 Webhook Endpoint Updates
```php
// Replace /ses-webhook with /resend-webhook
$app->post('/resend-webhook', function (Request $request, Response $response) {
    $payload = $request->getBody()->getContents();
    $signature = $request->getHeaderLine('resend-signature');
    
    // Verify webhook signature
    $verifier = new App\Services\WebhookVerifier();
    if (!$verifier->verify($payload, $signature)) {
        return $response->withStatus(401);
    }
    
    $event = json_decode($payload, true);
    
    // Log webhook event
    EventLog::create([
        'event_type' => 'resend_webhook',
        'payload' => $event
    ]);
    
    error_log('Resend webhook received: ' . $payload);
    
    // Extract message ID from tags
    $messageId = null;
    if (isset($event['data']['tags'])) {
        foreach ($event['data']['tags'] as $tag) {
            if ($tag['name'] === 'message_id') {
                $messageId = $tag['value'];
                break;
            }
        }
    }
    
    // Process event
    $eventType = $event['type'] ?? '';
    
    if ($messageId) {
        $status = match($eventType) {
            'email.delivered' => Message::STATUS_DELIVERED,
            'email.bounced' => Message::STATUS_BOUNCED,
            'email.opened' => Message::STATUS_OPENED,
            'email.clicked' => Message::STATUS_CLICKED,
            'email.complained' => Message::STATUS_COMPLAINED,
            default => null
        };

        if ($status) {
            Message::where('message_id', $messageId)
                ->update(['status' => $status]);
        }
    }

    return $response->withStatus(200);
});
```

### Phase 4: Configuration Updates

#### 4.1 Environment Files Update
- Update `.env` with Resend credentials
- Update `.env.example` with Resend template variables
- Remove or comment out SES-specific variables

#### 4.2 Import Updates
```php
// Add to index.php imports
use App\Services\EmailService;
use App\Services\WebhookVerifier;

// Optional: Remove if not using Symfony Mailer elsewhere
// use Symfony\Component\Mailer\Mailer;
// use Symfony\Component\Mailer\Transport;
// use Symfony\Component\Mime\Email;
```

## Event Type Mapping

| SES Event | Resend Event | Message Status |
|-----------|--------------|----------------|
| Delivery | email.delivered | STATUS_DELIVERED |
| Bounce | email.bounced | STATUS_BOUNCED |
| Open | email.opened | STATUS_OPENED |
| Click | email.clicked | STATUS_CLICKED |
| Complaint | email.complained | STATUS_COMPLAINED |

## Error Handling Strategy

### Resend API Errors
- Rate limiting (429)
- Authentication errors (401)
- Validation errors (400)
- Server errors (5xx)

### Fallback Strategy
1. Log all email failures
2. Save message to database regardless of email status
3. Provide user feedback on email delivery issues
4. Implement retry mechanism for transient failures

## Security Considerations

### Webhook Security
- Verify webhook signatures using `RESEND_WEBHOOK_SECRET`
- Rate limit webhook endpoint
- Log suspicious webhook attempts

### API Key Management
- Store Resend API key securely
- Use environment variables only
- Rotate keys regularly
- Monitor API usage

## Testing Strategy

### Development Testing
1. Unit tests for EmailService
2. Integration tests for webhook processing
3. Manual testing with test emails

### Production Deployment
1. Blue-green deployment strategy
2. Monitor email delivery rates
3. Compare with SES baseline metrics
4. Rollback plan if issues occur

## Migration Timeline

### Phase 1 (Setup): 1 day
- Update dependencies
- Create service classes
- Update environment variables

### Phase 2 (Implementation): 2 days
- Update route handlers
- Test email functionality
- Update webhook processing

### Phase 3 (Testing): 1 day
- Comprehensive testing
- Performance validation
- Security verification

### Phase 4 (Deployment): 1 day
- Production deployment
- Monitoring setup
- Documentation updates

## Rollback Plan

If issues occur:
1. Revert to SES configuration
2. Switch back to Symfony Mailer
3. Restore original webhook endpoint
4. Update environment variables

## Success Metrics

- Email delivery rate ≥ 99%
- Webhook processing accuracy = 100%
- Response time < 2 seconds
- Zero security incidents
- Successful migration within timeline

## Post-Migration Tasks

1. Remove SES dependencies (optional)
2. Update deployment scripts
3. Monitor Resend API limits
4. Document new webhook URLs
5. Train team on new system