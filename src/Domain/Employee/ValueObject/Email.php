<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObject;

use Webmozart\Assert\Assert;

final readonly class Email
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        Assert::email($value, 'Invalid email format.');
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

