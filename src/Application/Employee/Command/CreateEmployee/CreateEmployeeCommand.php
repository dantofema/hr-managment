<?php

declare(strict_types=1);

namespace App\Application\Employee\Command\CreateEmployee;

use App\Domain\Employee\ValueObject\Department;
use App\Domain\Employee\ValueObject\Role;

final readonly class CreateEmployeeCommand
{
    public function __construct(
        public string $name,
        public string $email,
        public Department $department,
        public Role $role
    ) {
    }
}

