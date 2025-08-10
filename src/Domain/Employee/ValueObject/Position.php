<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObject;

use InvalidArgumentException;

final readonly class Position
{
    public function __construct(private string $value)
    {
        if (empty(trim($value))) {
            throw new InvalidArgumentException('Position cannot be empty');
        }
        
        if (strlen($value) > 100) {
            throw new InvalidArgumentException('Position cannot exceed 100 characters');
        }
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

    public function __toString(): string
    {
        return $this->value;
    }
}