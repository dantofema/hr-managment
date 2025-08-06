<?php

declare(strict_types=1);

namespace App\Application\Employee\Command\UpdateEmployee;

use App\Domain\Employee\ValueObject\Department;
use App\Domain\Employee\ValueObject\Role;
use App\Domain\Employee\ValueObject\Salary;

final readonly class UpdateEmployeeCommand
{
    public function __construct(
        public string $employeeId,
        public ?string $name = null,
        public ?string $email = null,
        public ?Department $department = null,
        public ?Role $role = null,
        public ?Salary $salary = null,
    ) {
    }
}