<?php

declare(strict_types=1);

namespace App\Domain\Payroll\ValueObject;

use InvalidArgumentException;

final readonly class NetSalary
{
    private const VALID_CURRENCIES = ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY'];

    public function __construct(
        private float $amount,
        private string $currency
    ) {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Net salary amount must be positive');
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