<?php

declare(strict_types=1);

namespace App\Application\Payroll\Query;

final readonly class GetPayrollsByEmployeeIdQuery
{
    public function __construct(
        public string $employeeId
    ) {
    }
}