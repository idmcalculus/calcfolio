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
        $signature = $request->getHeaderLine('resend-signature');

        // Verify webhook signature
        if (!$this->webhookVerifier->verify($payload, $signature)) {
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

        $statusString = $this->mapEventTypeToStatus($event['type'] ?? '');

        if ($statusString) {
            // Find message by message_id
            $message = $this->messageRepository->findByMessageId($messageId);

            if ($message) {
                // Update message status using repository
                $status = MessageStatus::fromString($statusString);
                $this->messageRepository->update($message->getId(), ['status' => $statusString]);

                error_log("Updated message {$messageId} status to: {$statusString}");
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

        foreach ($event['data']['tags'] as $tag) {
            if (($tag['name'] ?? '') === 'message_id') {
                return $tag['value'] ?? null;
            }
        }

        return null;
    }

    /**
     * Map webhook event type to message status
     */
    private function mapEventTypeToStatus(string $eventType): ?string
    {
        return match($eventType) {
            'email.delivered' => MessageStatus::STATUS_DELIVERED,
            'email.bounced' => MessageStatus::STATUS_BOUNCED,
            'email.opened' => MessageStatus::STATUS_OPENED,
            'email.clicked' => MessageStatus::STATUS_CLICKED,
            'email.complained' => MessageStatus::STATUS_COMPLAINED,
            default => null
        };
    }
}