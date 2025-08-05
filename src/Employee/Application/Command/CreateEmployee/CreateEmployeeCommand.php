<?php

declare(strict_types=1);

namespace App\Employee\Application\Command\CreateEmployee;

use App\Employee\Domain\ValueObject\Department;
use App\Employee\Domain\ValueObject\Role;

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

