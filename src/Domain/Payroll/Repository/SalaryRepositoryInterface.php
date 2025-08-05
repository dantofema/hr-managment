<?php

declare(strict_types=1);

namespace App\Domain\Payroll\Repository;

use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Payroll\Entity\Salary;

interface SalaryRepositoryInterface
{
    public function save(Salary $salary): void;

    public function findByEmployeeId(EmployeeId $employeeId): ?Salary;

    public function findAll(): array;

    public function delete(Salary $salary): void;

    public function existsForEmployee(EmployeeId $employeeId): bool;
}