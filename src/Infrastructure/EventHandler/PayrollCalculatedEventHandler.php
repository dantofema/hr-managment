<?php

declare(strict_types=1);

namespace App\Infrastructure\EventHandler;

use App\Domain\Payroll\Event\PayrollCalculatedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class PayrollCalculatedEventHandler
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(PayrollCalculatedEvent $event): void
    {
        // Log the payroll calculation event
        $this->logger->info('Payroll calculated', [
            'payroll_id' => (string) $event->getPayrollId(),
            'employee_id' => (string) $event->getEmployeeId(),
            'gross_salary' => $event->getGrossSalary()->getAmount(),
            'net_salary' => $event->getNetSalary()->getAmount(),
            'total_deductions' => $event->getTotalDeductions()->getAmount(),
            'period' => $event->getPeriod()->getDescription(),
            'calculated_at' => $event->getCalculatedAt()->format('Y-m-d H:i:s'),
        ]);

        // Here you could add additional business logic such as:
        // - Sending notifications to HR department
        // - Updating employee records
        // - Triggering payment processes
        // - Generating reports
        // - Sending email notifications to employees
        
        // For now, we just log the event as this is a simple system
    }
}