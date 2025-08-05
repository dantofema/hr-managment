<?php

declare(strict_types=1);

namespace App\Domain\Payroll\Repository;

use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Payroll\Entity\Payroll;
use App\Domain\Payroll\ValueObject\PayrollId;
use App\Domain\Payroll\ValueObject\PayrollPeriod;

interface PayrollRepositoryInterface
{
    public function save(Payroll $payroll): void;

    public function findById(PayrollId $id): ?Payroll;

    public function findByEmployeeId(EmployeeId $employeeId): array;

    public function findByEmployeeIdAndPeriod(EmployeeId $employeeId, PayrollPeriod $period): ?Payroll;

    public function findAll(): array;

    public function delete(Payroll $payroll): void;

    public function existsForEmployeeAndPeriod(EmployeeId $employeeId, PayrollPeriod $period): bool;
}