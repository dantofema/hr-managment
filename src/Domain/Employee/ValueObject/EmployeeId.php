<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObject;

use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

final readonly class EmployeeId
{
    private function __construct(private Uuid $value)
    {
    }

    public static function generate(): self
    {
        return new self(Uuid::v4());
    }

    public static function fromString(string $value): self
    {
        Assert::uuid($value, 'Invalid UUID format for EmployeeId.');
        return new self(Uuid::fromString($value));
    }

    public function __toString(): string
    {
        return $this->value->toRfc4122();
    }

    public function equals(self $other): bool
    {
        return $this->value->equals($other->value);
    }
}

