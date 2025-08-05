<?php

declare(strict_types=1);

namespace App\Employee\Application\Handler;

use App\Employee\Application\Command\UpdateEmployee\UpdateEmployeeCommand;
use App\Employee\Domain\Exception\EmployeeNotFoundException;
use App\Employee\Domain\Repository\EmployeeRepositoryInterface;
use App\Employee\Domain\ValueObject\Email;
use App\Employee\Domain\ValueObject\EmployeeId;
use App\Employee\Domain\ValueObject\EmployeeName;
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