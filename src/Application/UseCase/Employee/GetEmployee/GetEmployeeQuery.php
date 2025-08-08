<?php

declare(strict_types=1);

namespace App\Application\UseCase\Employee\GetEmployee;

final readonly class GetEmployeeQuery
{
    public function __construct(
        public string $employeeId
    ) {}
}