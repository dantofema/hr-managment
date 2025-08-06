<?php

declare(strict_types=1);

namespace App\Application\Payroll\Command;

final readonly class DeleteSalaryCommand
{
    public function __construct(
        public string $employeeId
    ) {
    }
}