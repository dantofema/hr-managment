<?php

declare(strict_types=1);

namespace App\Payroll\Application\Command;

final readonly class CalculatePayrollCommand
{
    public function __construct(
        public string $employeeId,
        public ?string $periodStart = null,
        public ?string $periodEnd = null
    ) {
    }
}