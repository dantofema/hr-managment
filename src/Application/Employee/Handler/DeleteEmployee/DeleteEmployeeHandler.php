<?php

namespace App\Application\Employee\Handler\DeleteEmployee;

use App\Employee\Application\Command\DeleteEmployee\DeleteEmployeeCommand;
use App\Domain\Employee\Exception\EmployeeNotFoundException;
use App\Domain\Employee\Repository\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObject\EmployeeId;

class DeleteEmployeeHandler
{
    private EmployeeRepositoryInterface $employeeRepository;

    public function __construct(EmployeeRepositoryInterface $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    public function __invoke(DeleteEmployeeCommand $command): void
    {
        $employeeId = EmployeeId::fromString($command->getId());
        $employee = $this->employeeRepository->findById($employeeId);
        if (!$employee) {
            throw new EmployeeNotFoundException();
        }
        $this->employeeRepository->delete($employee);
    }
}
