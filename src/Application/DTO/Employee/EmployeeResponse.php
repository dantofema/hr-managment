<?php

declare(strict_types=1);

namespace App\Application\DTO\Employee;

final readonly class EmployeeResponse
{
    public function __construct(
        public string $id,
        public string $fullName,
        public string $email,
        public string $position,
        public float $salaryAmount,
        public string $salaryCurrency,
        public string $hiredAt,
        public int $yearsOfService,
        public int $annualVacationDays,
        public bool $vacationEligible
    ) {}
}