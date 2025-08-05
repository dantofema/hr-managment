<?php

declare(strict_types=1);

namespace App\Payroll\Application\Handler;

use App\Employee\Domain\ValueObject\EmployeeId;
use App\Payroll\Application\Query\GetSalaryByEmployeeIdQuery;
use App\Payroll\Domain\Entity\Salary;
use App\Payroll\Domain\Repository\SalaryRepositoryInterface;

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