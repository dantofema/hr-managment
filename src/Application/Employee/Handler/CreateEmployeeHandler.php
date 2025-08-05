<?php

declare(strict_types=1);

namespace App\Application\Employee\Handler;

use App\Employee\Application\Command\CreateEmployee\CreateEmployeeCommand;
use App\Domain\Employee\Entity\Employee;
use App\Domain\Employee\Event\EmployeeCreated;
use App\Domain\Employee\Repository\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObject\Email;
use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Employee\ValueObject\EmployeeName;
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
