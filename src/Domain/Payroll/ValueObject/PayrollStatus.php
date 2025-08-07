<?php

declare(strict_types=1);

namespace App\Domain\Payroll\ValueObject;

use InvalidArgumentException;

final readonly class PayrollStatus
{
    private const PENDING = 'pending';
    private const PROCESSED = 'processed';
    private const PAID = 'paid';
    private const CANCELLED = 'cancelled';

    private const VALID_STATUSES = [
        self::PENDING,
        self::PROCESSED,
        self::PAID,
        self::CANCELLED,
    ];

    private function __construct(private string $value)
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid payroll status: {$value}");
        }
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function processed(): self
    {
        return new self(self::PROCESSED);
    }

    public static function paid(): self
    {
        return new self(self::PAID);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isProcessed(): bool
    {
        return $this->value === self::PROCESSED;
    }

    public function isPaid(): bool
    {
        return $this->value === self::PAID;
    }

    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }

    public function process(): self
    {
        if (!$this->isPending()) {
            throw new \DomainException('Cannot process payroll that is not pending');
        }

        return self::processed();
    }

    public function pay(): self
    {
        if (!$this->isProcessed()) {
            throw new \DomainException('Cannot pay payroll that is not processed');
        }

        return self::paid();
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