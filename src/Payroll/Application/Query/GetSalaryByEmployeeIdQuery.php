<?php

declare(strict_types=1);

namespace App\Payroll\Application\Query;

final readonly class GetSalaryByEmployeeIdQuery
{
    public function __construct(
        public string $employeeId
    ) {
    }
}