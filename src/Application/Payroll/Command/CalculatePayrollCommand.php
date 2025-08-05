<?php

declare(strict_types=1);

namespace App\Application\Payroll\Command;

final readonly class CalculatePayrollCommand
{
    public function __construct(
        public string $employeeId,
        public ?string $periodStart = null,
        public ?string $periodEnd = null
    ) {
    }
}