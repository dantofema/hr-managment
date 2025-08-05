<?php

declare(strict_types=1);

namespace App\Payroll\Application\Command;

final readonly class CreateSalaryCommand
{
    public function __construct(
        public string $employeeId,
        public float $baseSalary,
        public float $bonus = 0.0,
        public string $currency = 'USD'
    ) {
    }
}