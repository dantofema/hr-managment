<?php

namespace App\Tests\Employee\Application\Handler;

use App\Employee\Application\Command\UpdateEmployee\UpdateEmployeeCommand;
use App\Employee\Application\Handler\UpdateEmployeeHandler;
use App\Employee\Domain\Entity\Employee;
use App\Employee\Domain\Exception\EmployeeNotFoundException;
use App\Employee\Domain\Repository\EmployeeRepositoryInterface;
use App\Employee\Domain\ValueObject\Department;
use App\Employee\Domain\ValueObject\Email;
use App\Employee\Domain\ValueObject\EmployeeId;
use App\Employee\Domain\ValueObject\EmployeeName;
use App\Employee\Domain\ValueObject\Role;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateEmployeeHandlerTest extends TestCase
{
    private EmployeeRepositoryInterface|MockObject $repository;
    private UpdateEmployeeHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EmployeeRepositoryInterface::class);
        $this->handler = new UpdateEmployeeHandler($this->repository);
    }

    public function testUpdateEmployeeSuccess(): void
    {
        $employeeId = '550e8400-e29b-41d4-a716-446655440000';
        $employee = $this->createMock(Employee::class);

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($this->callback(function (EmployeeId $id) use ($employeeId) {
                return (string) $id === $employeeId;
            }))
            ->willReturn($employee);

        $employee->expects($this->once())
            ->method('changeName')
            ->with($this->callback(function (EmployeeName $name) {
                return (string) $name === 'John Doe Updated';
            }));

        $employee->expects($this->once())
            ->method('changeEmail')
            ->with($this->callback(function (Email $email) {
                return (string) $email === 'john.updated@example.com';
            }));

        $employee->expects($this->once())
            ->method('changeDepartment')
            ->with(Department::Engineering);

        $employee->expects($this->once())
            ->method('changeRole')
            ->with(Role::Manager);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($employee);

        $command = new UpdateEmployeeCommand(
            employeeId: $employeeId,
            name: 'John Doe Updated',
            email: 'john.updated@example.com',
            department: Department::Engineering,
            role: Role::Manager
        );

        $this->handler->__invoke($command);
    }

    public function testUpdateEmployeePartialUpdate(): void
    {
        $employeeId = '550e8400-e29b-41d4-a716-446655440000';
        $employee = $this->createMock(Employee::class);

        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn($employee);

        $employee->expects($this->once())
            ->method('changeName')
            ->with($this->callback(function (EmployeeName $name) {
                return (string) $name === 'Jane Doe';
            }));

        $employee->expects($this->never())->method('changeEmail');
        $employee->expects($this->never())->method('changeDepartment');
        $employee->expects($this->never())->method('changeRole');

        $this->repository->expects($this->once())
            ->method('save')
            ->with($employee);

        $command = new UpdateEmployeeCommand(
            employeeId: $employeeId,
            name: 'Jane Doe'
        );

        $this->handler->__invoke($command);
    }

    public function testUpdateEmployeeNotFound(): void
    {
        $employeeId = '550e8400-e29b-41d4-a716-446655440000';

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($this->callback(function (EmployeeId $id) use ($employeeId) {
                return (string) $id === $employeeId;
            }))
            ->willReturn(null);

        $this->repository->expects($this->never())
            ->method('save');

        $command = new UpdateEmployeeCommand(
            employeeId: $employeeId,
            name: 'John Doe'
        );

        $this->expectException(EmployeeNotFoundException::class);
        $this->expectExceptionMessage('Employee with ID 550e8400-e29b-41d4-a716-446655440000 not found');

        $this->handler->__invoke($command);
    }

    public function testUpdateEmployeeWithInvalidEmail(): void
    {
        $employeeId = '550e8400-e29b-41d4-a716-446655440000';
        $employee = $this->createMock(Employee::class);

        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn($employee);

        $this->repository->expects($this->never())
            ->method('save');

        $command = new UpdateEmployeeCommand(
            employeeId: $employeeId,
            email: 'invalid-email'
        );

        $this->expectException(\InvalidArgumentException::class);

        $this->handler->__invoke($command);
    }

    public function testUpdateEmployeeWithInvalidName(): void
    {
        $employeeId = '550e8400-e29b-41d4-a716-446655440000';
        $employee = $this->createMock(Employee::class);

        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn($employee);

        $this->repository->expects($this->never())
            ->method('save');

        $command = new UpdateEmployeeCommand(
            employeeId: $employeeId,
            name: 'A' // Too short
        );

        $this->expectException(\InvalidArgumentException::class);

        $this->handler->__invoke($command);
    }
}