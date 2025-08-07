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
            throw new InvalidArgumentException('Start date must be before end date');
        }
        
        if ($this->getDurationInDays() > 31) {
            throw new InvalidArgumentException('Payroll period cannot exceed 31 days');
        }
    }

    public static function monthly(int $year, int $month): self
    {
        $startDate = new DateTimeImmutable(sprintf('%d-%02d-01', $year, $month));
        $endDate = $startDate->modify('last day of this month');
        
        return new self($startDate, $endDate);
    }

    public static function weekly(DateTimeImmutable $startDate): self
    {
        $endDate = $startDate->modify('+6 days');
        
        return new self($startDate, $endDate);
    }

    public function startDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function endDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getDurationInDays(): int
    {
        return $this->startDate->diff($this->endDate)->days + 1;
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