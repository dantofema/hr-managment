<?php

declare(strict_types=1);

namespace App\Payroll\Domain\Repository;

use App\Employee\Domain\ValueObject\EmployeeId;
use App\Payroll\Domain\Entity\Salary;

interface SalaryRepositoryInterface
{
    public function save(Salary $salary): void;

    public function findByEmployeeId(EmployeeId $employeeId): ?Salary;

    public function findAll(): array;

    public function delete(Salary $salary): void;

    public function existsForEmployee(EmployeeId $employeeId): bool;
}