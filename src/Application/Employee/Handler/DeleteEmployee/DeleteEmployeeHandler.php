<?php

declare(strict_types=1);

namespace App\Application\Employee\Handler\DeleteEmployee;

use App\Application\Employee\Command\DeleteEmployee\DeleteEmployeeCommand;
use App\Domain\Employee\Exception\EmployeeNotFoundException;
use App\Domain\Employee\Repository\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObject\EmployeeId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
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
