<?php

declare(strict_types=1);

namespace App\Employee\Application\Command\UpdateEmployee;

use App\Employee\Domain\ValueObject\Department;
use App\Employee\Domain\ValueObject\Role;

final readonly class UpdateEmployeeCommand
{
    public function __construct(
        public string $employeeId,
        public ?string $name = null,
        public ?string $email = null,
        public ?Department $department = null,
        public ?Role $role = null,
    ) {
    }
}