<?php

declare(strict_types=1);

namespace App\Application\DTO\Employee;

final readonly class EmployeeRequest
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $position,
        public float $salaryAmount,
        public string $salaryCurrency,
        public \DateTimeImmutable $hiredAt
    ) {}
}