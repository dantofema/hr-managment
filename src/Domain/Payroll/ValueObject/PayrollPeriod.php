<?php

declare(strict_types=1);

namespace App\Domain\Payroll\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class PayrollPeriod
{
    public function __construct(
        private DateTimeImmutable $startDate,
        private DateTimeImmutable $endDate
    ) {
        if ($startDate >= $endDate) {
            throw new InvalidArgumentException('End date must be after start date');
        }
    }

    public static function forMonth(int $year, int $month): self
    {
        $startDate = new DateTimeImmutable(sprintf('%d-%02d-01', $year, $month));
        $endDate = $startDate->modify('last day of this month');
        
        return new self($startDate, $endDate);
    }

    public static function biweekly(DateTimeImmutable $startDate): self
    {
        $endDate = $startDate->modify('+13 days');
        
        return new self($startDate, $endDate);
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getDaysInPeriod(): int
    {
        return $this->startDate->diff($this->endDate)->days;
    }

    public function format(): string
    {
        return sprintf(
            '%s to %s',
            $this->startDate->format('Y-m-d'),
            $this->endDate->format('Y-m-d')
        );
    }

    public function contains(DateTimeImmutable $date): bool
    {
        return $date >= $this->startDate && $date <= $this->endDate;
    }

    public function equals(self $other): bool
    {
        return $this->startDate == $other->startDate 
            && $this->endDate == $other->endDate;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s to %s',
            $this->startDate->format('Y-m-d'),
            $this->endDate->format('Y-m-d')
        );
    }
}