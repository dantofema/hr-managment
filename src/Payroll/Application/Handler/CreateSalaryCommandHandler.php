<?php

declare(strict_types=1);

namespace App\Payroll\Application\Handler;

use App\Employee\Domain\Repository\EmployeeRepositoryInterface;
use App\Employee\Domain\ValueObject\EmployeeId;
use App\Payroll\Application\Command\CreateSalaryCommand;
use App\Payroll\Domain\Entity\Salary;
use App\Payroll\Domain\Repository\SalaryRepositoryInterface;
use App\Payroll\Domain\ValueObject\Money;
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