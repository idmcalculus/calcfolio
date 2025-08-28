<?php

namespace App\Application\Services;

use App\Domain\Interfaces\MessageRepositoryInterface;
use App\Domain\Interfaces\EmailServiceInterface;
use App\Domain\Interfaces\ValidationInterface;
use App\Domain\Entities\Message;
use App\Domain\ValueObjects\EmailAddress;
use App\Domain\ValueObjects\MessageStatus;
use Exception;

class ContactFormService
{
    private MessageRepositoryInterface $messageRepository;
    private EmailServiceInterface $emailService;
    private ValidationInterface $validator;

    public function __construct(
        MessageRepositoryInterface $messageRepository,
        EmailServiceInterface $emailService,
        ValidationInterface $validator
    ) {
        $this->messageRepository = $messageRepository;
        $this->emailService = $emailService;
        $this->validator = $validator;
    }

    /**
     * Process a contact form submission
     */
    public function processContactForm(array $data): array
    {
        // Validate input data
        $validationResult = $this->validator->validateContactForm($data);
        if (!$this->validator->isValid()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ];
        }

        try {
            // Generate unique message ID
            $messageId = $this->generateMessageId();

            // Send admin notification
            $adminResult = $this->emailService->sendContactNotification($data, $messageId);

            // Send auto-reply
            $autoReplyResult = $this->emailService->sendAutoReply($data, $messageId);

            // Save to database
            $message = $this->messageRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'subject' => $data['subject'],
                'message' => $data['message'],
                'message_id' => $messageId,
                'status' => MessageStatus::STATUS_PENDING,
                'is_read' => false,
            ]);

            return [
                'success' => true,
                'message' => 'Message received successfully',
                'message_id' => $messageId,
                'data' => $message->toArray()
            ];

        } catch (Exception $e) {
            error_log('Contact form processing error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to process contact form',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get message status by message ID
     */
    public function getMessageStatus(string $messageId): array
    {
        $message = $this->messageRepository->findByMessageId($messageId);

        if (!$message) {
            return [
                'success' => false,
                'message' => 'Message not found'
            ];
        }

        return [
            'success' => true,
            'message_id' => $messageId,
            'status' => $message->getStatus()->getValue(),
            'is_read' => $message->isRead(),
            'created_at' => $message->getCreatedAt()->format('c')
        ];
    }

    /**
     * Generate unique message ID
     */
    private function generateMessageId(): string
    {
        return 'msg_' . uniqid() . '_' . bin2hex(random_bytes(4));
    }
}