<?php

declare(strict_types=1);

namespace App\Application\Payroll\Handler;

use App\Domain\Employee\ValueObject\EmployeeId;
use App\Payroll\Application\Query\GetSalaryByEmployeeIdQuery;
use App\Domain\Payroll\Entity\Salary;
use App\Domain\Payroll\Repository\SalaryRepositoryInterface;

final readonly class GetSalaryByEmployeeIdQueryHandler
{
    public function __construct(
        private SalaryRepositoryInterface $salaryRepository
    ) {
    }

    public function __invoke(GetSalaryByEmployeeIdQuery $query): ?Salary
    {
        $employeeId = EmployeeId::fromString($query->employeeId);
        
        return $this->salaryRepository->findByEmployeeId($employeeId);
    }
}