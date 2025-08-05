<?php

namespace App\Employee\Application\Handler\DeleteEmployee;

use App\Employee\Application\Command\DeleteEmployee\DeleteEmployeeCommand;
use App\Employee\Domain\Repository\EmployeeRepositoryInterface;
use App\Employee\Domain\Exception\EmployeeNotFoundException;

class DeleteEmployeeHandler
{
    private EmployeeRepositoryInterface $employeeRepository;

    public function __construct(EmployeeRepositoryInterface $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    public function __invoke(DeleteEmployeeCommand $command): void
    {
        $employee = $this->employeeRepository->findById($command->getId());
        if (!$employee) {
            throw new EmployeeNotFoundException();
        }
        $this->employeeRepository->delete($employee);
    }
}
