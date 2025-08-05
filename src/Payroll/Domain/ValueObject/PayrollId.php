<?php

declare(strict_types=1);

namespace App\Payroll\Domain\ValueObject;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class PayrollId
{
    private UuidInterface $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('PayrollId cannot be empty');
        }

        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException('PayrollId must be a valid UUID');
        }

        $this->value = Uuid::fromString($value);
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value->toString();
    }

    public function equals(PayrollId $other): bool
    {
        return $this->value->equals($other->value);
    }

    public function __toString(): string
    {
        return $this->value->toString();
    }
}