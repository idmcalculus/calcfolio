<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class MessageStatus
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_BOUNCED = 'bounced';
    public const STATUS_OPENED = 'opened';
    public const STATUS_CLICKED = 'clicked';
    public const STATUS_COMPLAINED = 'complained';

    private string $value;

    private function __construct(string $value)
    {
        $this->ensureIsValidStatus($value);
        $this->value = $value;
    }

    private function ensureIsValidStatus(string $value): void
    {
        $validStatuses = [
            self::STATUS_PENDING,
            self::STATUS_DELIVERED,
            self::STATUS_BOUNCED,
            self::STATUS_OPENED,
            self::STATUS_CLICKED,
            self::STATUS_COMPLAINED,
        ];

        if (!in_array($value, $validStatuses, true)) {
            throw new InvalidArgumentException("Invalid message status: {$value}");
        }
    }

    public static function pending(): self
    {
        return new self(self::STATUS_PENDING);
    }

    public static function delivered(): self
    {
        return new self(self::STATUS_DELIVERED);
    }

    public static function bounced(): self
    {
        return new self(self::STATUS_BOUNCED);
    }

    public static function opened(): self
    {
        return new self(self::STATUS_OPENED);
    }

    public static function clicked(): self
    {
        return new self(self::STATUS_CLICKED);
    }

    public static function complained(): self
    {
        return new self(self::STATUS_COMPLAINED);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isPending(): bool
    {
        return $this->value === self::STATUS_PENDING;
    }

    public function isDelivered(): bool
    {
        return $this->value === self::STATUS_DELIVERED;
    }

    public function isBounced(): bool
    {
        return $this->value === self::STATUS_BOUNCED;
    }

    public function isOpened(): bool
    {
        return $this->value === self::STATUS_OPENED;
    }

    public function isClicked(): bool
    {
        return $this->value === self::STATUS_CLICKED;
    }

    public function isComplained(): bool
    {
        return $this->value === self::STATUS_COMPLAINED;
    }

    public function equals(MessageStatus $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}