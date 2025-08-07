<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

use InvalidArgumentException;

final readonly class Uuid
{
    public function __construct(private string $value)
    {
        if (!$this->isValid($value)) {
            throw new InvalidArgumentException('Invalid UUID format');
        }
    }

    public static function generate(): self
    {
        return new self(\Ramsey\Uuid\Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private function isValid(string $value): bool
    {
        return \Ramsey\Uuid\Uuid::isValid($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}