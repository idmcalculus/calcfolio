<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class EmailAddress
{
    private string $value;

    public function __construct(string $email)
    {
        $trimmedEmail = trim($email);
        $this->ensureIsValidEmail($trimmedEmail);
        $this->value = strtolower($trimmedEmail);
    }

    private function ensureIsValidEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address format');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getDomain(): string
    {
        return substr(strrchr($this->value, "@"), 1);
    }

    public function getLocalPart(): string
    {
        return strstr($this->value, '@', true);
    }

    public function equals(EmailAddress $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}