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
    }

    public static function monthly(int $year, int $month): self
    {
        $startDate = new DateTimeImmutable(sprintf('%d-%02d-01', $year, $month));
        $endDate = $startDate->modify('last day of this month');
        
        return new self($startDate, $endDate);
    }

    public static function fromDates(DateTimeImmutable $startDate, DateTimeImmutable $endDate): self
    {
        return new self($startDate, $endDate);
    }

    public static function current(): self
    {
        $now = new DateTimeImmutable();
        return self::monthly((int) $now->format('Y'), (int) $now->format('m'));
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
        return (int) $this->startDate->diff($this->endDate)->days + 1;
    }

    public function getWorkingDaysCount(): int
    {
        $workingDays = 0;
        $current = $this->startDate;
        
        while ($current <= $this->endDate) {
            $dayOfWeek = (int) $current->format('N'); // 1 = Monday, 7 = Sunday
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) { // Monday to Friday
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

    public function getDescription(): string
    {
        return sprintf(
            '%s to %s',
            $this->startDate->format('Y-m-d'),
            $this->endDate->format('Y-m-d')
        );
    }

    public function toArray(): array
    {
        return [
            'start_date' => $this->startDate->format('Y-m-d'),
            'end_date' => $this->endDate->format('Y-m-d'),
            'days_count' => $this->getDaysCount(),
            'working_days_count' => $this->getWorkingDaysCount(),
        ];
    }

    public function __toString(): string
    {
        return $this->getDescription();
    }
}