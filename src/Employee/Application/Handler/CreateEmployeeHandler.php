<?php

declare(strict_types=1);

namespace App\Employee\Application\Handler;

use App\Employee\Application\Command\CreateEmployee\CreateEmployeeCommand;
use App\Employee\Domain\Entity\Employee;
use App\Employee\Domain\Event\EmployeeCreated;
use App\Employee\Domain\Repository\EmployeeRepositoryInterface;
use App\Employee\Domain\ValueObject\Email;
use App\Employee\Domain\ValueObject\EmployeeId;
use App\Employee\Domain\ValueObject\EmployeeName;
use DateTimeImmutable;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateEmployeeHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function __invoke(CreateEmployeeCommand $command): void
    {
        $employeeId = EmployeeId::generate();
        $employee = new Employee(
            $employeeId,
            EmployeeName::fromString($command->name),
            Email::fromString($command->email),
            $command->department,
            $command->role
        );

        $this->employeeRepository->save($employee);

        $this->eventDispatcher->dispatch(new EmployeeCreated($employeeId,
            new DateTimeImmutable()));
    }
}
