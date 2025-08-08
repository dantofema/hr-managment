<?php

declare(strict_types=1);

namespace App\Application\UseCase\Employee\CreateEmployee;

use App\Domain\Employee\Employee;
use App\Domain\Employee\EmployeeRepository;
use App\Domain\Employee\ValueObject\Email;
use App\Domain\Employee\ValueObject\FullName;
use App\Domain\Employee\ValueObject\Position;
use App\Domain\Employee\ValueObject\Salary;
use App\Domain\Shared\ValueObject\Uuid;

final readonly class CreateEmployeeHandler
{
    public function __construct(
        private EmployeeRepository $employeeRepository
    ) {}

    public function handle(CreateEmployeeCommand $command): CreateEmployeeResponse
    {
        $employee = new Employee(
            Uuid::generate(),
            new FullName($command->firstName, $command->lastName),
            new Email($command->email),
            new Position($command->position),
            new Salary($command->salaryAmount, $command->salaryCurrency),
            $command->hiredAt
        );

        $this->employeeRepository->save($employee);

        return new CreateEmployeeResponse(
            $employee->getId()->toString(),
            $employee->getFullName()->getFirstName() . ' ' . $employee->getFullName()->getLastName(),
            $employee->getEmail()->toString(),
            $employee->getPosition()->toString(),
            $employee->getSalary()->getAmount(),
            $employee->getSalary()->getCurrency(),
            $employee->getHiredAt()->format('Y-m-d'),
            $employee->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }
}