<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\MessageStatus;
use App\Domain\ValueObjects\EmailAddress;
use DateTimeImmutable;

class Message
{
    private int $id;
    private string $name;
    private EmailAddress $email;
    private string $subject;
    private string $message;
    private ?string $messageId;
    private MessageStatus $status;
    private bool $isRead;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        int $id,
        string $name,
        EmailAddress $email,
        string $subject,
        string $message,
        ?string $messageId = null,
        ?MessageStatus $status = null,
        bool $isRead = false,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->subject = $subject;
        $this->message = $message;
        $this->messageId = $messageId;
        $this->status = $status ?? MessageStatus::pending();
        $this->isRead = $isRead;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): EmailAddress
    {
        return $this->email;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function getStatus(): MessageStatus
    {
        return $this->status;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function markAsRead(): void
    {
        $this->isRead = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateStatus(MessageStatus $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email->getValue(),
            'subject' => $this->subject,
            'message' => $this->message,
            'message_id' => $this->messageId,
            'status' => $this->status->getValue(),
            'is_read' => $this->isRead,
            'created_at' => $this->createdAt->format('c'),
            'updated_at' => $this->updatedAt->format('c'),
        ];
    }
}