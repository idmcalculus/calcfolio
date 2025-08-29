<?php

namespace App\Tests\Fixtures;

use App\Domain\Entities\Message;
use App\Domain\ValueObjects\EmailAddress;
use App\Domain\ValueObjects\MessageStatus;
use DateTimeImmutable;

class MessageFixture
{
    public static function createPendingMessage(
        int $id = 1,
        string $name = 'Test User',
        string $email = 'test@example.com',
        string $subject = 'Test Subject',
        string $message = 'This is a test message.',
        ?string $messageId = null
    ): Message {
        return new Message(
            $id,
            $name,
            new EmailAddress($email),
            $subject,
            $message,
            $messageId,
            MessageStatus::pending(),
            false,
            new DateTimeImmutable('2023-01-01 12:00:00'),
            new DateTimeImmutable('2023-01-01 12:00:00')
        );
    }

    public static function createDeliveredMessage(
        int $id = 2,
        string $name = 'Delivered User',
        string $email = 'delivered@example.com',
        string $subject = 'Delivered Subject',
        string $message = 'This message has been delivered.',
        ?string $messageId = null
    ): Message {
        return new Message(
            $id,
            $name,
            new EmailAddress($email),
            $subject,
            $message,
            $messageId,
            MessageStatus::delivered(),
            true,
            new DateTimeImmutable('2023-01-01 10:00:00'),
            new DateTimeImmutable('2023-01-01 11:00:00')
        );
    }

    public static function createBouncedMessage(
        int $id = 3,
        string $name = 'Bounced User',
        string $email = 'bounced@example.com',
        string $subject = 'Bounced Subject',
        string $message = 'This message bounced.',
        ?string $messageId = null
    ): Message {
        return new Message(
            $id,
            $name,
            new EmailAddress($email),
            $subject,
            $message,
            $messageId,
            MessageStatus::bounced(),
            false,
            new DateTimeImmutable('2023-01-01 09:00:00'),
            new DateTimeImmutable('2023-01-01 09:30:00')
        );
    }

    public static function createMessageArray(
        int $id = 1,
        string $name = 'Array User',
        string $email = 'array@example.com',
        string $subject = 'Array Subject',
        string $message = 'This is an array message.',
        ?string $messageId = null
    ): array {
        return [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'message_id' => $messageId,
            'status' => 'pending',
            'is_read' => false,
            'created_at' => '2023-01-01T12:00:00+00:00',
            'updated_at' => '2023-01-01T12:00:00+00:00',
        ];
    }

    public static function createContactFormData(
        string $name = 'Contact User',
        string $email = 'contact@example.com',
        string $subject = 'Contact Subject',
        string $message = 'This is a contact form message with sufficient content to pass validation.',
        ?string $recaptchaToken = 'test_recaptcha_token'
    ): array {
        $data = [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
        ];

        if ($recaptchaToken) {
            $data['recaptcha_token'] = $recaptchaToken;
        }

        return $data;
    }

    public static function createInvalidContactFormData(): array
    {
        return [
            'name' => '',
            'email' => 'invalid-email',
            'subject' => '',
            'message' => '',
        ];
    }

    public static function createPaginationParams(
        int $page = 1,
        int $limit = 15,
        string $sort = 'created_at',
        string $order = 'desc',
        ?bool $isRead = null,
        ?string $search = null,
        ?string $status = null
    ): array {
        $params = [];

        if ($page !== 1) {
            $params['page'] = $page;
        }

        if ($limit !== 15) {
            $params['limit'] = $limit;
        }

        if ($sort !== 'created_at') {
            $params['sort'] = $sort;
        }

        if ($order !== 'desc') {
            $params['order'] = $order;
        }

        if ($isRead !== null) {
            $params['is_read'] = $isRead;
        }

        if ($search !== null) {
            $params['search'] = $search;
        }

        if ($status !== null) {
            $params['status'] = $status;
        }

        return $params;
    }

    public static function createBulkActionData(
        string $action = 'mark_read',
        array $ids = [1, 2, 3]
    ): array {
        return [
            'action' => $action,
            'ids' => $ids,
        ];
    }

    public static function createAdminLoginData(
        string $username = 'admin',
        string $password = 'password123'
    ): array {
        return [
            'username' => $username,
            'password' => $password,
        ];
    }
}