<?php

declare(strict_types=1);

namespace App\Domain\Payroll\ValueObject;

use InvalidArgumentException;

final readonly class Deductions
{
    private const VALID_CURRENCIES = ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY'];

    public function __construct(
        private float $taxes,
        private float $socialSecurity,
        private float $healthInsurance,
        private string $currency
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
        
        if (!in_array($currency, self::VALID_CURRENCIES, true)) {
            throw new InvalidArgumentException("Invalid currency code: {$currency}");
        }
    }

    public function getTaxes(): float
    {
        return $this->taxes;
    }

    public function getSocialSecurity(): float
    {
        return $this->socialSecurity;
    }

    public function getHealthInsurance(): float
    {
        return $this->healthInsurance;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getTotal(): float
    {
        return $this->taxes + $this->socialSecurity + $this->healthInsurance;
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("Cannot add different currencies: {$this->currency} and {$other->currency}");
        }

        return new self(
            $this->taxes + $other->taxes,
            $this->socialSecurity + $other->socialSecurity,
            $this->healthInsurance + $other->healthInsurance,
            $this->currency
        );
    }

    public function getPercentageOf(float $grossSalary): float
    {
        if ($grossSalary <= 0) {
            throw new InvalidArgumentException('Gross salary must be positive');
        }

        return ($this->getTotal() / $grossSalary) * 100;
    }

    public function format(): string
    {
        return sprintf(
            'Taxes: %.2f %s, Social Security: %.2f %s, Health Insurance: %.2f %s, Total: %.2f %s',
            $this->taxes,
            $this->currency,
            $this->socialSecurity,
            $this->currency,
            $this->healthInsurance,
            $this->currency,
            $this->getTotal(),
            $this->currency
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['taxes'],
            $data['social_security'],
            $data['health_insurance'],
            $data['currency']
        );
    }

    public function toArray(): array
    {
        return [
            'taxes' => $this->taxes,
            'social_security' => $this->socialSecurity,
            'health_insurance' => $this->healthInsurance,
            'total' => $this->getTotal(),
            'currency' => $this->currency
        ];
    }

    public function equals(self $other): bool
    {
        return $this->taxes === $other->taxes
            && $this->socialSecurity === $other->socialSecurity
            && $this->healthInsurance === $other->healthInsurance
            && $this->currency === $other->currency;
    }

    public function __toString(): string
    {
        return $this->format();
    }
}