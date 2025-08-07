<?php

declare(strict_types=1);

namespace App\Domain\Vacation\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class VacationPeriod
{
    public function __construct(
        private DateTimeImmutable $startDate,
        private DateTimeImmutable $endDate
    ) {
        if ($startDate >= $endDate) {
            throw new InvalidArgumentException('End date must be after start date');
        }

        if ($startDate < new DateTimeImmutable('today')) {
            throw new InvalidArgumentException('Vacation cannot start in the past');
        }

        if ($this->getDaysCount() > 365) {
            throw new InvalidArgumentException('Vacation period cannot exceed 365 days');
        }
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getDaysCount(): int
    {
        return $this->startDate->diff($this->endDate)->days + 1;
    }

    public function getWorkingDaysCount(): int
    {
        $workingDays = 0;
        $current = $this->startDate;

        while ($current <= $this->endDate) {
            $dayOfWeek = (int) $current->format('N');
            if ($dayOfWeek < 6) { // Monday to Friday (1-5)
                $workingDays++;
            }
            $current = $current->modify('+1 day');
        }

        return $workingDays;
    }

    public function contains(DateTimeImmutable $date): bool
    {
        return $date >= $this->startDate && $date <= $this->endDate;
    }

    public function overlaps(self $other): bool
    {
        return $this->startDate <= $other->endDate && $this->endDate >= $other->startDate;
    }

    public function format(): string
    {
        return sprintf(
            '%s to %s (%d days)',
            $this->startDate->format('Y-m-d'),
            $this->endDate->format('Y-m-d'),
            $this->getDaysCount()
        );
    }

    public function equals(self $other): bool
    {
        return $this->startDate == $other->startDate 
            && $this->endDate == $other->endDate;
    }

    public function __toString(): string
    {
        return $this->format();
    }
}