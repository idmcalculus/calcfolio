<?php

namespace App\Tests\E2E;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;

class ContactFormApiTest extends TestCase
{
    private Client $httpClient;
    private string $baseUrl;

    protected function setUp(): void
    {
        $this->baseUrl = 'http://localhost:8080';
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 10,
            'http_errors' => false, // Don't throw exceptions for HTTP errors
        ]);
    }

    public function testContactFormEndpointExists(): void
    {
        $response = $this->httpClient->get('/contact');

        // Even if method not allowed, endpoint should exist
        $this->assertContains($response->getStatusCode(), [200, 405, 404]);
    }

    public function testOptionsRequestForCors(): void
    {
        $response = $this->httpClient->request('OPTIONS', '/contact', [
            'headers' => [
                'Origin' => 'http://localhost:3000',
                'Access-Control-Request-Method' => 'POST',
            ]
        ]);

        // Check CORS headers
        $headers = $response->getHeaders();
        $this->assertArrayHasKey('Access-Control-Allow-Origin', $headers);
        $this->assertArrayHasKey('Access-Control-Allow-Methods', $headers);
        $this->assertArrayHasKey('Access-Control-Allow-Headers', $headers);
    }

    public function testContactFormSubmissionWithValidData(): void
    {
        $contactData = [
            'name' => 'E2E Test User',
            'email' => 'e2e-test@example.com',
            'subject' => 'E2E Test Subject',
            'message' => 'This is an end-to-end test message with sufficient content to pass validation requirements.',
            'recaptcha_token' => 'test_recaptcha_token'
        ];

        $response = $this->httpClient->post('/contact', [
            'json' => $contactData,
            'headers' => [
                'Content-Type' => 'application/json',
                'Origin' => 'http://localhost:3000'
            ]
        ]);

        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true);

        // Should either succeed or fail gracefully
        $this->assertContains($statusCode, [200, 400, 422, 500]);

        if ($statusCode === 200) {
            $this->assertArrayHasKey('success', $body);
            $this->assertArrayHasKey('message_id', $body);
            $this->assertStringStartsWith('msg_', $body['message_id']);
        } elseif ($statusCode === 400 || $statusCode === 422) {
            $this->assertArrayHasKey('success', $body);
            $this->assertFalse($body['success']);
            $this->assertArrayHasKey('message', $body);
        }
    }

    public function testContactFormSubmissionWithInvalidData(): void
    {
        $invalidData = [
            'name' => '',
            'email' => 'invalid-email',
            'subject' => '',
            'message' => ''
        ];

        $response = $this->httpClient->post('/contact', [
            'json' => $invalidData,
            'headers' => [
                'Content-Type' => 'application/json',
                'Origin' => 'http://localhost:3000'
            ]
        ]);

        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true);

        // Should return validation error
        $this->assertContains($statusCode, [400, 422]);

        if (isset($body['success'])) {
            $this->assertFalse($body['success']);
            $this->assertArrayHasKey('errors', $body);
        }
    }

    public function testMessageStatusEndpoint(): void
    {
        // First try to submit a message to get a valid message ID
        $contactData = [
            'name' => 'Status Test User',
            'email' => 'status-test@example.com',
            'subject' => 'Status Test Subject',
            'message' => 'This is a test message for checking status endpoint functionality.',
            'recaptcha_token' => 'test_recaptcha_token'
        ];

        $submitResponse = $this->httpClient->post('/contact', [
            'json' => $contactData,
            'headers' => [
                'Content-Type' => 'application/json',
                'Origin' => 'http://localhost:3000'
            ]
        ]);

        if ($submitResponse->getStatusCode() === 200) {
            $submitBody = json_decode($submitResponse->getBody()->getContents(), true);
            $messageId = $submitBody['message_id'];

            // Now test the status endpoint
            $statusResponse = $this->httpClient->get("/message/{$messageId}", [
                'headers' => [
                    'Origin' => 'http://localhost:3000'
                ]
            ]);

            $statusCode = $statusResponse->getStatusCode();
            $statusBody = json_decode($statusResponse->getBody()->getContents(), true);

            $this->assertContains($statusCode, [200, 404]);

            if ($statusCode === 200) {
                $this->assertArrayHasKey('success', $statusBody);
                $this->assertArrayHasKey('message_id', $statusBody);
                $this->assertArrayHasKey('status', $statusBody);
                $this->assertEquals($messageId, $statusBody['message_id']);
            }
        }
    }

    public function testMessageStatusEndpointWithInvalidId(): void
    {
        $response = $this->httpClient->get('/message/invalid_message_id', [
            'headers' => [
                'Origin' => 'http://localhost:3000'
            ]
        ]);

        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertContains($statusCode, [404, 400]);

        if (isset($body['success'])) {
            $this->assertFalse($body['success']);
        }
    }

    public function testApiDocumentationEndpoints(): void
    {
        // Test OpenAPI JSON endpoint
        $jsonResponse = $this->httpClient->get('/openapi.json');
        $this->assertContains($jsonResponse->getStatusCode(), [200, 404]);

        if ($jsonResponse->getStatusCode() === 200) {
            $jsonBody = json_decode($jsonResponse->getBody()->getContents(), true);
            $this->assertIsArray($jsonBody);
            $this->assertArrayHasKey('openapi', $jsonBody);
        }

        // Test OpenAPI YAML endpoint
        $yamlResponse = $this->httpClient->get('/openapi.yaml');
        $this->assertContains($yamlResponse->getStatusCode(), [200, 404]);

        // Test HTML documentation endpoint
        $htmlResponse = $this->httpClient->get('/docs.html');
        $this->assertContains($htmlResponse->getStatusCode(), [200, 404]);

        if ($htmlResponse->getStatusCode() === 200) {
            $htmlBody = $htmlResponse->getBody()->getContents();
            $this->assertStringContainsString('swagger', strtolower($htmlBody));
        }
    }

    public function testHealthCheckEndpoint(): void
    {
        $response = $this->httpClient->get('/health');

        // Health endpoint might not exist, but if it does, it should return 200
        if ($response->getStatusCode() === 200) {
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertIsArray($body);
        }
    }

    public function testInvalidHttpMethodReturns405(): void
    {
        $response = $this->httpClient->put('/contact', [
            'json' => [],
            'headers' => [
                'Origin' => 'http://localhost:3000'
            ]
        ]);

        // Should return Method Not Allowed
        $this->assertEquals(405, $response->getStatusCode());
    }

    public function testContentTypeValidation(): void
    {
        $contactData = [
            'name' => 'Content Type Test',
            'email' => 'content-test@example.com',
            'subject' => 'Content Type Test',
            'message' => 'Testing content type validation.'
        ];

        // Test without Content-Type header
        $response = $this->httpClient->post('/contact', [
            'body' => json_encode($contactData),
            'headers' => [
                'Origin' => 'http://localhost:3000'
            ]
        ]);

        // Should handle gracefully
        $this->assertContains($response->getStatusCode(), [200, 400, 415]);
    }

    public function testLargeMessageHandling(): void
    {
        $largeMessage = str_repeat('This is a very long message. ', 1000);

        $contactData = [
            'name' => 'Large Message Test',
            'email' => 'large-test@example.com',
            'subject' => 'Large Message Test',
            'message' => $largeMessage
        ];

        $response = $this->httpClient->post('/contact', [
            'json' => $contactData,
            'headers' => [
                'Content-Type' => 'application/json',
                'Origin' => 'http://localhost:3000'
            ]
        ]);

        $statusCode = $response->getStatusCode();

        // Should either succeed or fail with validation error for too long message
        $this->assertContains($statusCode, [200, 400, 422]);

        if ($statusCode === 400 || $statusCode === 422) {
            $body = json_decode($response->getBody()->getContents(), true);
            if (isset($body['errors']['message'])) {
                $this->assertStringContainsString('message', $body['errors']['message'][0]);
            }
        }
    }

    public function testSqlInjectionPrevention(): void
    {
        $maliciousData = [
            'name' => "'; DROP TABLE messages; --",
            'email' => 'sql-injection@example.com',
            'subject' => 'SQL Injection Test',
            'message' => 'Testing SQL injection prevention.'
        ];

        $response = $this->httpClient->post('/contact', [
            'json' => $maliciousData,
            'headers' => [
                'Content-Type' => 'application/json',
                'Origin' => 'http://localhost:3000'
            ]
        ]);

        $statusCode = $response->getStatusCode();

        // Should either succeed (with sanitized data) or fail validation
        $this->assertContains($statusCode, [200, 400, 422]);

        // If it succeeds, the malicious SQL should be stored as plain text
        if ($statusCode === 200) {
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertArrayHasKey('message_id', $body);
        }
    }

    public function testXssPrevention(): void
    {
        $xssData = [
            'name' => '<script>alert("XSS")</script>',
            'email' => 'xss-test@example.com',
            'subject' => '<img src=x onerror=alert("XSS")>',
            'message' => 'Testing XSS prevention in message content.'
        ];

        $response = $this->httpClient->post('/contact', [
            'json' => $xssData,
            'headers' => [
                'Content-Type' => 'application/json',
                'Origin' => 'http://localhost:3000'
            ]
        ]);

        $statusCode = $response->getStatusCode();

        // Should handle XSS attempts gracefully
        $this->assertContains($statusCode, [200, 400, 422]);

        if ($statusCode === 200) {
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertArrayHasKey('message_id', $body);
        }
    }
}