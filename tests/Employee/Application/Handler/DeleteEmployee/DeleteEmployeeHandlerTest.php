<?php

namespace App\Tests\Employee\Application\Handler\DeleteEmployee;

use App\Application\Employee\Command\DeleteEmployee\DeleteEmployeeCommand;
use App\Application\Employee\Handler\DeleteEmployee\DeleteEmployeeHandler;
use App\Domain\Employee\Entity\Employee;
use App\Domain\Employee\Exception\EmployeeNotFoundException;
use App\Domain\Employee\Repository\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObject\EmployeeId;
use PHPUnit\Framework\TestCase;

class DeleteEmployeeHandlerTest extends TestCase
{
    public function testDeleteEmployeeSuccess(): void
    {
        // Usar un UUID válido para cumplir con EmployeeId::fromString
        $employeeId = '123e4567-e89b-12d3-a456-426614174000';
        $employeeIdVO = EmployeeId::fromString($employeeId);
        $employee = $this->createMock(Employee::class);
        $repository = $this->createMock(EmployeeRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findById')
            ->with($employeeIdVO)
            ->willReturn($employee);
        $repository->expects($this->once())
            ->method('delete')
            ->with($employee);

        $handler = new DeleteEmployeeHandler($repository);
        $command = new DeleteEmployeeCommand($employeeId);
        $handler($command);
    }

    public function testDeleteEmployeeNotFound(): void
    {
        // Usar un UUID válido para cumplir con EmployeeId::fromString
        $employeeId = '123e4567-e89b-12d3-a456-426614174001';
        $employeeIdVO = EmployeeId::fromString($employeeId);
        $repository = $this->createMock(EmployeeRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findById')
            ->with($employeeIdVO)
            ->willReturn(null);
        $repository->expects($this->never())
            ->method('delete');

        $handler = new DeleteEmployeeHandler($repository);
        $command = new DeleteEmployeeCommand($employeeId);

        $this->expectException(EmployeeNotFoundException::class);
        $handler($command);
    }
}
