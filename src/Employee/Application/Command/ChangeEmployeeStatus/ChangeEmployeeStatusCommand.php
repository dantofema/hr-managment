<?php

declare(strict_types=1);

namespace App\Employee\Application\Command\ChangeEmployeeStatus;

use App\Employee\Domain\ValueObject\EmployeeStatus;

final readonly class ChangeEmployeeStatusCommand
{
    public function __construct(
        public string $employeeId,
        public EmployeeStatus $newStatus
    ) {
    }
}

