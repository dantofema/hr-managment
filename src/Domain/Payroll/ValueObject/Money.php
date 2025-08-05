<?php

declare(strict_types=1);

namespace App\Domain\Payroll\ValueObject;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(
        private float $amount,
        private string $currency = 'USD'
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative');
        }
        
        if (empty($currency)) {
            throw new InvalidArgumentException('Currency cannot be empty');
        }
    }

    public static function fromFloat(float $amount, string $currency = 'USD'): self
    {
        return new self($amount, $currency);
    }

    public static function zero(string $currency = 'USD'): self
    {
        return new self(0.0, $currency);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->ensureSameCurrency($other);
        $newAmount = $this->amount - $other->amount;
        
        if ($newAmount < 0) {
            throw new InvalidArgumentException('Subtraction would result in negative amount');
        }
        
        return new self($newAmount, $this->currency);
    }

    public function multiply(float $multiplier): self
    {
        if ($multiplier < 0) {
            throw new InvalidArgumentException('Multiplier cannot be negative');
        }
        
        return new self($this->amount * $multiplier, $this->currency);
    }

    public function percentage(float $percentage): self
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new InvalidArgumentException('Percentage must be between 0 and 100');
        }
        
        return new self($this->amount * ($percentage / 100), $this->currency);
    }

    public function isGreaterThan(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount > $other->amount;
    }

    public function isEqual(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        return abs($this->amount - $other->amount) < 0.01; // Handle floating point precision
    }

    public function __toString(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }

    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot operate on different currencies');
        }
    }
}