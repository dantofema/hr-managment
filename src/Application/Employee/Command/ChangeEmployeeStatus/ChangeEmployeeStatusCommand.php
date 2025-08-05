<?php

declare(strict_types=1);

namespace App\Application\Employee\Command\ChangeEmployeeStatus;

use App\Domain\Employee\ValueObject\EmployeeStatus;

final readonly class ChangeEmployeeStatusCommand
{
    public function __construct(
        public string $employeeId,
        public EmployeeStatus $newStatus
    ) {
    }
}

