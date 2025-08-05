<?php

declare(strict_types=1);

namespace App\Domain\Payroll\Event;

use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Payroll\ValueObject\Money;
use App\Domain\Payroll\ValueObject\PayrollId;
use App\Domain\Payroll\ValueObject\PayrollPeriod;
use DateTimeImmutable;

final readonly class PayrollCalculatedEvent
{
    public function __construct(
        private PayrollId $payrollId,
        private EmployeeId $employeeId,
        private Money $grossSalary,
        private Money $netSalary,
        private Money $totalDeductions,
        private PayrollPeriod $period,
        private DateTimeImmutable $calculatedAt
    ) {
    }

    public function getPayrollId(): PayrollId
    {
        return $this->payrollId;
    }

    public function getEmployeeId(): EmployeeId
    {
        return $this->employeeId;
    }

    public function getGrossSalary(): Money
    {
        return $this->grossSalary;
    }

    public function getNetSalary(): Money
    {
        return $this->netSalary;
    }

    public function getTotalDeductions(): Money
    {
        return $this->totalDeductions;
    }

    public function getPeriod(): PayrollPeriod
    {
        return $this->period;
    }

    public function getCalculatedAt(): DateTimeImmutable
    {
        return $this->calculatedAt;
    }

    public function toArray(): array
    {
        return [
            'payroll_id' => (string) $this->payrollId,
            'employee_id' => (string) $this->employeeId,
            'gross_salary' => $this->grossSalary->toArray(),
            'net_salary' => $this->netSalary->toArray(),
            'total_deductions' => $this->totalDeductions->toArray(),
            'period' => $this->period->toArray(),
            'calculated_at' => $this->calculatedAt->format('Y-m-d H:i:s'),
        ];
    }
}