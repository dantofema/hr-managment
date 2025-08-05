<?php

declare(strict_types=1);

namespace App\Employee\Application\Handler;

use App\Employee\Application\Command\ChangeEmployeeStatus\ChangeEmployeeStatusCommand;
use App\Employee\Domain\Event\EmployeeStatusChanged;
use App\Employee\Domain\Repository\EmployeeRepositoryInterface;
use App\Employee\Domain\ValueObject\EmployeeId;
use DateTimeImmutable;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
final readonly class ChangeEmployeeStatusHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function __invoke(ChangeEmployeeStatusCommand $command): void
    {
        $employeeId = EmployeeId::fromString($command->employeeId);
        $employee = $this->employeeRepository->findById($employeeId);

        Assert::notNull($employee, 'Employee not found.');

        $employee->changeStatus($command->newStatus);

        $this->employeeRepository->save($employee);

        $this->eventDispatcher->dispatch(new EmployeeStatusChanged(
            $employeeId,
            $command->newStatus,
            new DateTimeImmutable()
        ));
    }
}
