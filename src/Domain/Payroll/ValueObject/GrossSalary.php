<?php

declare(strict_types=1);

namespace App\Domain\Payroll\ValueObject;

use InvalidArgumentException;

final readonly class GrossSalary
{
    public function __construct(
        private float $amount,
        private string $currency = 'USD'
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Gross salary amount cannot be negative');
        }
        
        if (empty(trim($currency))) {
            throw new InvalidArgumentException('Currency cannot be empty');
        }
        
        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException('Currency must be a 3-letter code (e.g., USD, EUR)');
        }
    }

    public function amount(): float
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount 
            && $this->currency === $other->currency;
    }

    public function __toString(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }
}