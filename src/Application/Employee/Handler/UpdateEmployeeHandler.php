<?php

declare(strict_types=1);

namespace App\Application\Employee\Handler;

use App\Application\Employee\Command\UpdateEmployee\UpdateEmployeeCommand;
use App\Domain\Employee\Exception\EmployeeNotFoundException;
use App\Domain\Employee\Repository\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObject\Email;
use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Employee\ValueObject\EmployeeName;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateEmployeeHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
    ) {
    }

    public function __invoke(UpdateEmployeeCommand $command): void
    {
        $employeeId = EmployeeId::fromString($command->employeeId);
        $employee = $this->employeeRepository->findById($employeeId);

        if (!$employee) {
            throw new EmployeeNotFoundException(
                sprintf('Employee with ID %s not found', $command->employeeId)
            );
        }

        if ($command->name !== null) {
            $employee->changeName(EmployeeName::fromString($command->name));
        }

        if ($command->email !== null) {
            $employee->changeEmail(Email::fromString($command->email));
        }

        if ($command->department !== null) {
            $employee->changeDepartment($command->department);
        }

        if ($command->role !== null) {
            $employee->changeRole($command->role);
        }

        $this->employeeRepository->save($employee);
    }
}