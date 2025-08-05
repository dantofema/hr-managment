<?php

declare(strict_types=1);

namespace App\Employee\Application\Query;

final readonly class GetEmployeeByIdQuery
{
    public function __construct(
        public string $employeeId
    ) {
    }
}

