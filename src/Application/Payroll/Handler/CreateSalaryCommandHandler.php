<?php

declare(strict_types=1);

namespace App\Application\Payroll\Handler;

use App\Domain\Employee\Repository\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObject\EmployeeId;
use App\Payroll\Application\Command\CreateSalaryCommand;
use App\Domain\Payroll\Entity\Salary;
use App\Domain\Payroll\Repository\SalaryRepositoryInterface;
use App\Domain\Payroll\ValueObject\Money;
use InvalidArgumentException;

final readonly class CreateSalaryCommandHandler
{
    public function __construct(
        private SalaryRepositoryInterface $salaryRepository,
        private EmployeeRepositoryInterface $employeeRepository
    ) {
    }

    public function __invoke(CreateSalaryCommand $command): void
    {
        $employeeId = EmployeeId::fromString($command->employeeId);
        
        // Verify employee exists
        $employee = $this->employeeRepository->findById($employeeId);
        if (!$employee) {
            throw new InvalidArgumentException('Employee not found');
        }

        // Check if salary already exists for this employee
        if ($this->salaryRepository->existsForEmployee($employeeId)) {
            throw new InvalidArgumentException('Salary already exists for this employee');
        }

        // Create salary
        $baseSalary = Money::fromFloat($command->baseSalary, $command->currency);
        $bonus = Money::fromFloat($command->bonus, $command->currency);
        
        $salary = Salary::createForEmployee(
            $employeeId,
            $employee->getRole(),
            $baseSalary,
            $bonus
        );

        $this->salaryRepository->save($salary);
    }
}