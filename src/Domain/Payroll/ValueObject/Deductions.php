<?php

declare(strict_types=1);

namespace App\Domain\Payroll\ValueObject;

use InvalidArgumentException;

final readonly class Deductions
{
    public function __construct(
        private float $taxes,
        private float $socialSecurity,
        private float $healthInsurance,
        private float $otherDeductions = 0.0,
        private string $currency = 'USD'
    ) {
        if ($taxes < 0) {
            throw new InvalidArgumentException('Taxes cannot be negative');
        }
        
        if ($socialSecurity < 0) {
            throw new InvalidArgumentException('Social security cannot be negative');
        }
        
        if ($healthInsurance < 0) {
            throw new InvalidArgumentException('Health insurance cannot be negative');
        }
        
        if ($otherDeductions < 0) {
            throw new InvalidArgumentException('Other deductions cannot be negative');
        }
        
        if (empty(trim($currency))) {
            throw new InvalidArgumentException('Currency cannot be empty');
        }
        
        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException('Currency must be a 3-letter code (e.g., USD, EUR)');
        }
    }

    public function taxes(): float
    {
        return $this->taxes;
    }

    public function socialSecurity(): float
    {
        return $this->socialSecurity;
    }

    public function healthInsurance(): float
    {
        return $this->healthInsurance;
    }

    public function otherDeductions(): float
    {
        return $this->otherDeductions;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function totalAmount(): float
    {
        return $this->taxes + $this->socialSecurity + $this->healthInsurance + $this->otherDeductions;
    }

    public function equals(self $other): bool
    {
        return $this->taxes === $other->taxes
            && $this->socialSecurity === $other->socialSecurity
            && $this->healthInsurance === $other->healthInsurance
            && $this->otherDeductions === $other->otherDeductions
            && $this->currency === $other->currency;
    }

    public function __toString(): string
    {
        return sprintf(
            'Total: %s %s (Taxes: %.2f, SS: %.2f, Health: %.2f, Other: %.2f)',
            number_format($this->totalAmount(), 2),
            $this->currency,
            $this->taxes,
            $this->socialSecurity,
            $this->healthInsurance,
            $this->otherDeductions
        );
    }
}