<?php

namespace App\Application\Controllers;

use OpenApi\Attributes as OA;
use App\Infrastructure\External\WebhookVerifier;
use App\Domain\Interfaces\MessageRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\ValueObjects\MessageStatus;

class WebhookController
{
    private WebhookVerifier $webhookVerifier;
    private MessageRepositoryInterface $messageRepository;

    public function __construct(
        WebhookVerifier $webhookVerifier,
        MessageRepositoryInterface $messageRepository
    ) {
        $this->webhookVerifier = $webhookVerifier;
        $this->messageRepository = $messageRepository;
    }

    #[OA\Post(
        path: "/resend-webhook",
        summary: "Handle Resend webhook events",
        tags: ["Webhook"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "type", type: "string", example: "email.delivered"),
                    new OA\Property(property: "data", type: "object")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Webhook processed successfully"),
            new OA\Response(response: 401, description: "Invalid webhook signature")
        ]
    )]
    public function handleResendWebhook(Request $request, Response $response): Response
    {
        $payload = $request->getBody()->getContents();
        
        // Svix headers (used by Resend)
        $signature = $request->getHeaderLine('svix-signature');
        $timestamp = $request->getHeaderLine('svix-timestamp');
        $msgId = $request->getHeaderLine('svix-id');
        
        // Fallback to resend-signature if svix headers not present
        if (empty($signature)) {
            $signature = $request->getHeaderLine('resend-signature');
        }

        // Verify webhook signature
        if (!$this->webhookVerifier->verifySvix($payload, $signature, $timestamp, $msgId)) {
            error_log('Resend webhook signature verification failed');
            return $response->withStatus(401);
        }

        $event = json_decode($payload, true);

        // Log webhook event
        error_log('Resend webhook received: ' . json_encode($event));

        // Process webhook event
        $this->processWebhookEvent($event);

        return $response->withStatus(200);
    }

    /**
     * Process webhook event data
     */
    private function processWebhookEvent(array $event): void
    {
        $messageId = $this->extractMessageIdFromEvent($event);

        if (!$messageId) {
            error_log('No message ID found in webhook event');
            return;
        }

        $status = $this->mapEventTypeToStatus($event['type'] ?? '');

        if ($status) {
            // Find message by message_id
            $message = $this->messageRepository->findByMessageId($messageId);

            if ($message) {
                // Update message status using repository with the MessageStatus value object
                $this->messageRepository->update($message->getId(), ['status' => $status->getValue()]);

                error_log("Updated message {$messageId} status to: {$status->getValue()}");
            } else {
                error_log("Message with ID {$messageId} not found");
            }
        }
    }

    /**
     * Extract message ID from webhook event tags
     */
    private function extractMessageIdFromEvent(array $event): ?string
    {
        if (!isset($event['data']['tags'])) {
            return null;
        }

        // Tags can be either an object or an array
        $tags = $event['data']['tags'];
        
        // If tags is an associative array/object with message_id key
        if (isset($tags['message_id'])) {
            return $tags['message_id'];
        }
        
        // If tags is an array of tag objects (legacy format)
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                if (is_array($tag) && ($tag['name'] ?? '') === 'message_id') {
                    return $tag['value'] ?? null;
                }
            }
        }

        return null;
    }

    /**
     * Map webhook event type to message status value object
     */
    private function mapEventTypeToStatus(string $eventType): ?MessageStatus
    {
        return match($eventType) {
            'email.delivered' => MessageStatus::delivered(),
            'email.delivery_delayed' => MessageStatus::pending(), // Email is still pending delivery
            'email.bounced' => MessageStatus::bounced(),
            'email.opened' => MessageStatus::opened(),
            'email.clicked' => MessageStatus::clicked(),
            'email.complained' => MessageStatus::complained(),
            default => null
        };
    }
}