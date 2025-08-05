<?php

namespace App\Tests\Employee\Application\Handler\DeleteEmployee;

use App\Employee\Application\Command\DeleteEmployee\DeleteEmployeeCommand;
use App\Employee\Application\Handler\DeleteEmployee\DeleteEmployeeHandler;
use App\Employee\Domain\Exception\EmployeeNotFoundException;
use App\Employee\Domain\Repository\EmployeeRepositoryInterface;
use PHPUnit\Framework\TestCase;

class DeleteEmployeeHandlerTest extends TestCase
{
    public function testDeleteEmployeeSuccess(): void
    {
        $employeeId = '123';
        $employee = $this->createMock(\stdClass::class); // Simula la entidad Employee
        $repository = $this->createMock(EmployeeRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findById')
            ->with($employeeId)
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
        $employeeId = 'not-exist';
        $repository = $this->createMock(EmployeeRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findById')
            ->with($employeeId)
            ->willReturn(null);
        $repository->expects($this->never())
            ->method('delete');

        $handler = new DeleteEmployeeHandler($repository);
        $command = new DeleteEmployeeCommand($employeeId);

        $this->expectException(EmployeeNotFoundException::class);
        $handler($command);
    }
}
