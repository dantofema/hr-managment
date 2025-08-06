<?php

declare(strict_types=1);

namespace App\Application\Payroll\Handler;

use App\Application\Payroll\Command\UpdateSalaryCommand;
use App\Domain\Employee\Repository\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Payroll\Repository\SalaryRepositoryInterface;
use App\Domain\Payroll\ValueObject\Money;
use InvalidArgumentException;

final readonly class UpdateSalaryCommandHandler
{
    public function __construct(
        private SalaryRepositoryInterface $salaryRepository,
        private EmployeeRepositoryInterface $employeeRepository
    ) {
    }

    public function __invoke(UpdateSalaryCommand $command): void
    {
        $employeeId = EmployeeId::fromString($command->employeeId);
        
        // Verify employee exists
        $employee = $this->employeeRepository->findById($employeeId);
        if (!$employee) {
            throw new InvalidArgumentException('Employee not found');
        }

        // Get existing salary
        $salary = $this->salaryRepository->findByEmployeeId($employeeId);
        if (!$salary) {
            throw new InvalidArgumentException('Salary not found for this employee');
        }

        // Update salary
        $baseSalary = Money::fromFloat($command->baseSalary, $command->currency);
        $salary->updateBaseSalary($baseSalary);

        // Update bonus if provided
        if ($command->bonus !== null) {
            $bonus = Money::fromFloat($command->bonus, $command->currency);
            $salary->updateBonus($bonus);
        }

        $this->salaryRepository->save($salary);
    }
}