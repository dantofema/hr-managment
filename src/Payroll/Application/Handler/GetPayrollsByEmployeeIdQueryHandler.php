<?php

declare(strict_types=1);

namespace App\Payroll\Application\Handler;

use App\Employee\Domain\ValueObject\EmployeeId;
use App\Payroll\Application\Query\GetPayrollsByEmployeeIdQuery;
use App\Payroll\Domain\Repository\PayrollRepositoryInterface;

final readonly class GetPayrollsByEmployeeIdQueryHandler
{
    public function __construct(
        private PayrollRepositoryInterface $payrollRepository
    ) {
    }

    public function __invoke(GetPayrollsByEmployeeIdQuery $query): array
    {
        $employeeId = EmployeeId::fromString($query->employeeId);
        
        return $this->payrollRepository->findByEmployeeId($employeeId);
    }
}