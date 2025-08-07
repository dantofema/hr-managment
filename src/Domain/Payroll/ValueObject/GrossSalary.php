<?php

declare(strict_types=1);

namespace App\Domain\Payroll\ValueObject;

use InvalidArgumentException;

final readonly class GrossSalary
{
    private const VALID_CURRENCIES = ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY'];

    public function __construct(
        private float $amount,
        private string $currency
    ) {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Gross salary amount must be positive');
        }
        
        if (!in_array($currency, self::VALID_CURRENCIES, true)) {
            throw new InvalidArgumentException("Invalid currency code: {$currency}");
        }
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("Cannot add different currencies: {$this->currency} and {$other->currency}");
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("Cannot subtract different currencies: {$this->currency} and {$other->currency}");
        }

        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(float $multiplier): self
    {
        if ($multiplier <= 0) {
            throw new InvalidArgumentException('Multiplier must be positive');
        }

        return new self($this->amount * $multiplier, $this->currency);
    }

    public function format(): string
    {
        return sprintf('%.2f %s', $this->amount, $this->currency);
    }

    public function isGreaterThan(self $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("Cannot compare different currencies: {$this->currency} and {$other->currency}");
        }

        return $this->amount > $other->amount;
    }

    public function isLessThan(self $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("Cannot compare different currencies: {$this->currency} and {$other->currency}");
        }

        return $this->amount < $other->amount;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount 
            && $this->currency === $other->currency;
    }

    public function __toString(): string
    {
        return $this->format();
    }
}