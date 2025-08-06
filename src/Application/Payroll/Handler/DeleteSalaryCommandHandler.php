<?php

declare(strict_types=1);

namespace App\Application\Payroll\Handler;

use App\Application\Payroll\Command\DeleteSalaryCommand;
use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Payroll\Repository\SalaryRepositoryInterface;
use InvalidArgumentException;

final readonly class DeleteSalaryCommandHandler
{
    public function __construct(
        private SalaryRepositoryInterface $salaryRepository
    ) {
    }

    public function __invoke(DeleteSalaryCommand $command): void
    {
        $employeeId = EmployeeId::fromString($command->employeeId);
        
        // Find the salary to delete
        $salary = $this->salaryRepository->findByEmployeeId($employeeId);
        if (!$salary) {
            throw new InvalidArgumentException('Salary not found for this employee');
        }

        // Delete the salary
        $this->salaryRepository->delete($salary);
    }
}