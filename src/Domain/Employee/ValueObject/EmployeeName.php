<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObject;

use Webmozart\Assert\Assert;

final readonly class EmployeeName
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        Assert::lengthBetween($value, 2, 100,
            'Employee name must be between 2 and 100 characters.');
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

