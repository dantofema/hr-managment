<?php

declare(strict_types=1);

namespace App\Application\Payroll\Command;

final readonly class UpdateSalaryCommand
{
    public function __construct(
        public string $employeeId,
        public float $baseSalary,
        public ?float $bonus = null,
        public string $currency = 'USD'
    ) {
    }
}