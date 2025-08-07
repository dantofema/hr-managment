<?php

declare(strict_types=1);

namespace App\Domain\Vacation\ValueObject;

use InvalidArgumentException;

final readonly class VacationStatus
{
    private const PENDING = 'pending';
    private const APPROVED = 'approved';
    private const REJECTED = 'rejected';
    private const CANCELLED = 'cancelled';

    private const VALID_STATUSES = [
        self::PENDING,
        self::APPROVED,
        self::REJECTED,
        self::CANCELLED,
    ];

    private function __construct(private string $value)
    {
        if (!in_array($value, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid vacation status: {$value}");
        }
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function approved(): self
    {
        return new self(self::APPROVED);
    }

    public static function rejected(): self
    {
        return new self(self::REJECTED);
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

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->value === self::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->value === self::REJECTED;
    }

    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }

    public function approve(): self
    {
        if (!$this->isPending()) {
            throw new \DomainException('Cannot approve vacation that is not pending');
        }

        return self::approved();
    }

    public function reject(): self
    {
        if (!$this->isPending()) {
            throw new \DomainException('Cannot reject vacation that is not pending');
        }

        return self::rejected();
    }

    public function cancel(): self
    {
        if ($this->isRejected()) {
            throw new \DomainException('Cannot cancel rejected vacation');
        }

        return self::cancelled();
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