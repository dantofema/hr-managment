<?php

declare(strict_types=1);

namespace App\Domain\Payroll;

use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\Payroll\ValueObject\PayrollPeriod;
use App\Domain\Payroll\ValueObject\PayrollStatus;

interface PayrollRepository
{
    public function save(Payroll $payroll): void;
    
    public function findById(Uuid $id): ?Payroll;
    
    public function findByEmployeeId(Uuid $employeeId): array;
    
    public function findByPeriod(PayrollPeriod $period): array;
    
    public function findByStatus(PayrollStatus $status): array;
    
    public function findByEmployeeAndPeriod(Uuid $employeeId, PayrollPeriod $period): ?Payroll;
    
    public function findAll(): array;
    
    public function delete(Payroll $payroll): void;
    
    public function nextIdentity(): Uuid;
}