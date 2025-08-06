<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObject;

use Webmozart\Assert\Assert;

final readonly class Salary
{
    private function __construct(private float $value)
    {
    }

    public static function fromFloat(float $value): self
    {
        Assert::greaterThan($value, 0, 'Salary must be greater than 0.');
        return new self($value);
    }

    public static function fromString(string $value): self
    {
        $floatValue = (float) $value;
        Assert::greaterThan($floatValue, 0, 'Salary must be greater than 0.');
        return new self($floatValue);
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function toFloat(): float
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}