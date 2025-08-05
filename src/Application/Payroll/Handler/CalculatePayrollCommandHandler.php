<?php

declare(strict_types=1);

namespace App\Application\Payroll\Handler;

use App\Domain\Employee\Repository\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObject\EmployeeId;
use App\Payroll\Application\Command\CalculatePayrollCommand;
use App\Domain\Payroll\Entity\Payroll;
use App\Domain\Payroll\Event\PayrollCalculatedEvent;
use App\Domain\Payroll\Repository\PayrollRepositoryInterface;
use App\Domain\Payroll\Repository\SalaryRepositoryInterface;
use App\Domain\Payroll\ValueObject\PayrollPeriod;
use DateTimeImmutable;
use InvalidArgumentException;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class CalculatePayrollCommandHandler
{
    public function __construct(
        private PayrollRepositoryInterface $payrollRepository,
        private SalaryRepositoryInterface $salaryRepository,
        private EmployeeRepositoryInterface $employeeRepository,
        private MessageBusInterface $eventBus
    ) {
    }

    public function __invoke(CalculatePayrollCommand $command): Payroll
    {
        $employeeId = EmployeeId::fromString($command->employeeId);
        
        // Verify employee exists and is active
        $employee = $this->employeeRepository->findById($employeeId);
        if (!$employee) {
            throw new InvalidArgumentException('Employee not found');
        }
        
        if (!$employee->isActive()) {
            throw new InvalidArgumentException('Cannot calculate payroll for inactive employee');
        }

        // Get employee salary
        $salary = $this->salaryRepository->findByEmployeeId($employeeId);
        if (!$salary) {
            throw new InvalidArgumentException('No salary configured for employee');
        }

        // Determine payroll period
        $period = $this->determinePeriod($command->periodStart, $command->periodEnd);
        
        // Check if payroll already exists for this period
        if ($this->payrollRepository->existsForEmployeeAndPeriod($employeeId, $period)) {
            throw new InvalidArgumentException('Payroll already calculated for this period');
        }

        // Calculate payroll
        $grossSalary = $salary->getTotalSalary();
        $payroll = Payroll::calculate($employeeId, $grossSalary, $period);

        // Save payroll
        $this->payrollRepository->save($payroll);

        // Dispatch domain event
        $event = new PayrollCalculatedEvent(
            $payroll->getId(),
            $payroll->getEmployeeId(),
            $payroll->getGrossSalary(),
            $payroll->getNetSalary(),
            $payroll->getTotalDeductions(),
            $payroll->getPeriod(),
            $payroll->getCalculatedAt()
        );
        
        $this->eventBus->dispatch($event);

        return $payroll;
    }

    private function determinePeriod(?string $periodStart, ?string $periodEnd): PayrollPeriod
    {
        if ($periodStart && $periodEnd) {
            return PayrollPeriod::fromDates(
                new DateTimeImmutable($periodStart),
                new DateTimeImmutable($periodEnd)
            );
        }

        // Default to current month
        return PayrollPeriod::current();
    }
}