<?php

declare(strict_types=1);

namespace App\Payroll\Domain\Repository;

use App\Employee\Domain\ValueObject\EmployeeId;
use App\Payroll\Domain\Entity\Payroll;
use App\Payroll\Domain\ValueObject\PayrollId;
use App\Payroll\Domain\ValueObject\PayrollPeriod;

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