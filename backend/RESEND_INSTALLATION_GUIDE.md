# Resend Migration Installation Guide

This guide provides step-by-step instructions for completing the migration from Amazon SES to Resend.

## Prerequisites

- PHP 8.3+
- Composer
- Resend API account with API key
- Access to update webhook endpoints in your deployment

## Step 1: Install Dependencies

Run the following command in the backend directory:

```bash
cd backend
composer install
```

This will install the new `resend/resend-php` package along with all existing dependencies.

## Step 2: Environment Configuration

### 2.1 Update Environment Variables

Ensure your `.env` file contains the following Resend configuration:

```bash
# Resend Email Configuration
RESEND_API_KEY="re_your_actual_resend_api_key_here"
RESEND_WEBHOOK_SECRET="whsec_your_webhook_secret_here"
FROM_EMAIL=your-from-email@yourdomain.com
ADMIN_EMAIL=your-admin-email@yourdomain.com
```

### 2.2 Get Your Resend API Key

1. Sign up at [resend.com](https://resend.com)
2. Verify your domain or use a verified test domain
3. Generate an API key from your dashboard
4. Update the `RESEND_API_KEY` in your `.env` file

### 2.3 Configure Webhook Secret (Optional but Recommended)

1. In your Resend dashboard, go to Webhooks
2. Create a new webhook endpoint: `https://yourdomain.com/resend-webhook`
3. Generate a webhook secret
4. Update the `RESEND_WEBHOOK_SECRET` in your `.env` file

## Step 3: Test Email Functionality

### 3.1 Basic Email Test

Create a test script to verify email functionality:

```php
<?php
// test/test_resend.php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Services/EmailService.php';

use App\Services\EmailService;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $emailService = new EmailService();
    
    // Test data
    $testData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'subject' => 'Test Contact Form',
        'message' => 'This is a test message from the Resend migration.'
    ];
    
    $messageId = 'test_' . uniqid();
    
    echo "Testing Resend email functionality...\n";
    
    // Test admin notification
    $adminResult = $emailService->sendContactNotification($testData, $messageId);
    echo "Admin notification sent: " . json_encode($adminResult) . "\n";
    
    // Test auto-reply
    $replyResult = $emailService->sendAutoReply($testData, $messageId);
    echo "Auto-reply sent: " . json_encode($replyResult) . "\n";
    
    echo "✅ Email test completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Email test failed: " . $e->getMessage() . "\n";
    echo "Please check your RESEND_API_KEY and email configuration.\n";
}
```

Run the test:

```bash
php test/test_resend.php
```

### 3.2 Contact Form Integration Test

Test the full contact form endpoint using curl:

```bash
# Test the contact form endpoint
curl -X POST http://localhost:9000/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "subject": "Test Subject",
    "message": "Test message",
    "recaptcha_token": "test_token_disable_for_testing"
  }'
```

**Note**: For testing, you may need to temporarily disable reCAPTCHA verification or use a test token.

## Step 4: Webhook Testing

### 4.1 Test Webhook Endpoint

Create a webhook test script:

```php
<?php
// test/test_webhook.php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Services/WebhookVerifier.php';

use App\Services\WebhookVerifier;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Test webhook verification
$verifier = new WebhookVerifier();

$testPayload = json_encode([
    'type' => 'email.delivered',
    'data' => [
        'id' => 'test_email_id',
        'tags' => [
            ['name' => 'message_id', 'value' => 'test_msg_123']
        ]
    ]
]);

$testSignature = hash_hmac('sha256', $testPayload, $_ENV['RESEND_WEBHOOK_SECRET'] ?? '');

if ($verifier->verify($testPayload, $testSignature)) {
    echo "✅ Webhook signature verification works!\n";
} else {
    echo "❌ Webhook signature verification failed!\n";
    echo "Check your RESEND_WEBHOOK_SECRET configuration.\n";
}
```

### 4.2 Test Live Webhook

Use a tool like ngrok to expose your local server and test webhooks:

```bash
# Install ngrok if you haven't already
# Then expose your local server
ngrok http 9000

# Update your Resend webhook URL to the ngrok URL
# https://your-ngrok-id.ngrok.io/resend-webhook
```

## Step 5: Production Deployment

### 5.1 Pre-Deployment Checklist

- [ ] All tests pass successfully
- [ ] Environment variables are configured in production
- [ ] Webhook endpoint is accessible from the internet
- [ ] Domain is verified in Resend dashboard
- [ ] API key has appropriate permissions

### 5.2 Deploy and Monitor

1. Deploy your updated code to production
2. Monitor the application logs for any email-related errors
3. Test the contact form on the live site
4. Verify webhook deliveries in the Resend dashboard

### 5.3 Rollback Plan (if needed)

If you encounter issues, you can quickly rollback by:

1. Uncommenting the SES configuration in `.env`
2. Reverting the imports in `index.php`:
   ```php
   // Restore these imports
   use Symfony\Component\Mailer\Mailer;
   use Symfony\Component\Mailer\Transport;
   use Symfony\Component\Mime\Email;
   ```
3. Reverting the email sending logic to use Symfony Mailer

## Step 6: Post-Migration Tasks

### 6.1 Update Monitoring

Update your monitoring systems to track:
- Email delivery rates via Resend dashboard
- Webhook processing success rates
- API error rates and types

### 6.2 Clean Up (Optional)

After confirming everything works correctly, you can:

1. Remove commented SES configuration from `.env`
2. Remove unused Symfony Mailer imports
3. Remove the legacy `/ses-webhook` endpoint

### 6.3 Documentation Updates

Update your deployment and maintenance documentation to reflect:
- New Resend API key management
- Webhook endpoint configuration
- New monitoring dashboards
- Updated environment variables

## Troubleshooting

### Common Issues

#### 1. "Undefined type 'Resend'" Error

**Solution**: Run `composer install` to install the Resend package.

#### 2. Email Sending Fails

**Possible Causes**:
- Invalid API key
- Domain not verified in Resend
- Rate limiting
- Invalid email addresses

**Check**:
- API key format (should start with "re_")
- Domain verification status in Resend dashboard
- Error logs for specific error messages

#### 3. Webhook Not Receiving Events

**Possible Causes**:
- Webhook URL not accessible
- Incorrect signature verification
- Wrong endpoint URL in Resend dashboard

**Check**:
- Webhook URL is publicly accessible
- HTTPS is properly configured (recommended)
- Webhook secret matches between code and Resend dashboard

#### 4. Signature Verification Fails

**Solution**: Ensure `RESEND_WEBHOOK_SECRET` matches exactly with the secret in your Resend webhook configuration.

## Support

- **Resend Documentation**: [https://resend.com/docs](https://resend.com/docs)
- **API Reference**: [https://resend.com/docs/api-reference](https://resend.com/docs/api-reference)
- **Status Page**: [https://status.resend.com](https://status.resend.com)

For issues specific to this migration, check the implementation in:
- `src/Services/EmailService.php` - Email sending logic
- `src/Services/WebhookVerifier.php` - Webhook verification
- `src/index.php` - Route handlers and error handling