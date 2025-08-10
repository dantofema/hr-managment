<?php

declare(strict_types=1);

namespace App\Tests\Application\UseCase\Employee\CreateEmployee;

use App\Application\UseCase\Employee\CreateEmployee\CreateEmployeeCommand;
use App\Application\UseCase\Employee\CreateEmployee\CreateEmployeeHandler;
use App\Application\UseCase\Employee\CreateEmployee\CreateEmployeeResponse;
use App\Domain\Employee\Employee;
use App\Domain\Employee\EmployeeRepository;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateEmployeeHandlerTest extends TestCase
{
    private CreateEmployeeHandler $handler;
    private EmployeeRepository&MockObject $employeeRepository;

    protected function setUp(): void
    {
        $this->employeeRepository = $this->createMock(EmployeeRepository::class);
        $this->handler = new CreateEmployeeHandler($this->employeeRepository);
    }

    public function testCreateEmployeeSuccess(): void
    {
        $command = new CreateEmployeeCommand(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@example.com',
            position: 'Software Developer',
            salaryAmount: 50000.0,
            salaryCurrency: 'USD',
            hiredAt: new DateTimeImmutable('2024-01-15')
        );

        $this->employeeRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Employee $employee) {
                return $employee->getFullName()->getFirstName() === 'John'
                    && $employee->getFullName()->getLastName() === 'Doe'
                    && $employee->getEmail()->toString() === 'john.doe@example.com'
                    && $employee->getPosition()->toString() === 'Software Developer'
                    && $employee->getSalary()->getAmount() === 50000.0
                    && $employee->getSalary()->getCurrency() === 'USD'
                    && $employee->getHiredAt()->format('Y-m-d') === '2024-01-15';
            }));

        $response = $this->handler->handle($command);

        $this->assertInstanceOf(CreateEmployeeResponse::class, $response);
        $this->assertNotEmpty($response->id);
        $this->assertEquals('John Doe', $response->fullName);
        $this->assertEquals('john.doe@example.com', $response->email);
        $this->assertEquals('Software Developer', $response->position);
        $this->assertEquals(50000, $response->salaryAmount);
        $this->assertEquals('USD', $response->salaryCurrency);
        $this->assertEquals('2024-01-15', $response->hiredAt);
        $this->assertNotEmpty($response->createdAt);
    }

    public function testRepositorySaveCalled(): void
    {
        $command = new CreateEmployeeCommand(
            firstName: 'Jane',
            lastName: 'Smith',
            email: 'jane.smith@example.com',
            position: 'Designer',
            salaryAmount: 55000.0,
            salaryCurrency: 'USD',
            hiredAt: new DateTimeImmutable('2024-02-01')
        );

        $this->employeeRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Employee::class));

        $this->handler->handle($command);
    }

    public function testEmployeeCreatedWithCorrectData(): void
    {
        $command = new CreateEmployeeCommand(
            firstName: 'Alice',
            lastName: 'Johnson',
            email: 'alice.johnson@example.com',
            position: 'Senior Developer',
            salaryAmount: 75000.0,
            salaryCurrency: 'EUR',
            hiredAt: new DateTimeImmutable('2023-12-01')
        );

        $capturedEmployee = null;
        $this->employeeRepository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Employee $employee) use (&$capturedEmployee) {
                $capturedEmployee = $employee;
            });

        $response = $this->handler->handle($command);

        $this->assertNotNull($capturedEmployee);
        $this->assertEquals('Alice', $capturedEmployee->getFullName()->getFirstName());
        $this->assertEquals('Johnson', $capturedEmployee->getFullName()->getLastName());
        $this->assertEquals('alice.johnson@example.com', $capturedEmployee->getEmail()->toString());
        $this->assertEquals('Senior Developer', $capturedEmployee->getPosition()->toString());
        $this->assertEquals(75000, $capturedEmployee->getSalary()->getAmount());
        $this->assertEquals('EUR', $capturedEmployee->getSalary()->getCurrency());
        $this->assertEquals('2023-12-01', $capturedEmployee->getHiredAt()->format('Y-m-d'));

        // Verify response matches the created employee
        $this->assertEquals($capturedEmployee->getId()->toString(), $response->id);
        $this->assertEquals('Alice Johnson', $response->fullName);
        $this->assertEquals('alice.johnson@example.com', $response->email);
        $this->assertEquals('Senior Developer', $response->position);
        $this->assertEquals(75000, $response->salaryAmount);
        $this->assertEquals('EUR', $response->salaryCurrency);
        $this->assertEquals('2023-12-01', $response->hiredAt);
    }

    public function testResponseContainsAllRequiredFields(): void
    {
        $command = new CreateEmployeeCommand(
            firstName: 'Bob',
            lastName: 'Wilson',
            email: 'bob.wilson@example.com',
            position: 'Manager',
            salaryAmount: 80000.0,
            salaryCurrency: 'USD',
            hiredAt: new DateTimeImmutable('2024-03-01')
        );

        $this->employeeRepository
            ->expects($this->once())
            ->method('save');

        $response = $this->handler->handle($command);

        $this->assertNotEmpty($response->id);
        $this->assertIsString($response->id);
        $this->assertEquals('Bob Wilson', $response->fullName);
        $this->assertEquals('bob.wilson@example.com', $response->email);
        $this->assertEquals('Manager', $response->position);
        $this->assertEquals(80000, $response->salaryAmount);
        $this->assertEquals('USD', $response->salaryCurrency);
        $this->assertEquals('2024-03-01', $response->hiredAt);
        $this->assertNotEmpty($response->createdAt);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $response->createdAt);
    }

    public function testHandlerGeneratesUniqueIds(): void
    {
        $command1 = new CreateEmployeeCommand(
            firstName: 'Employee',
            lastName: 'One',
            email: 'employee1@example.com',
            position: 'Developer',
            salaryAmount: 50000.0,
            salaryCurrency: 'USD',
            hiredAt: new DateTimeImmutable('2024-01-01')
        );

        $command2 = new CreateEmployeeCommand(
            firstName: 'Employee',
            lastName: 'Two',
            email: 'employee2@example.com',
            position: 'Developer',
            salaryAmount: 50000.0,
            salaryCurrency: 'USD',
            hiredAt: new DateTimeImmutable('2024-01-01')
        );

        $this->employeeRepository
            ->expects($this->exactly(2))
            ->method('save');

        $response1 = $this->handler->handle($command1);
        $response2 = $this->handler->handle($command2);

        $this->assertNotEquals($response1->id, $response2->id);
    }

    public function testHandlerWithDifferentCurrencies(): void
    {
        $currencies = ['USD', 'EUR', 'GBP', 'CAD'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown'];
        $responses = [];

        $this->employeeRepository
            ->expects($this->exactly(count($currencies)))
            ->method('save');

        foreach ($currencies as $index => $currency) {
            $command = new CreateEmployeeCommand(
                firstName: 'Employee',
                lastName: $lastNames[$index],
                email: "employee{$index}@example.com",
                position: 'Developer',
                salaryAmount: 50000.0,
                salaryCurrency: $currency,
                hiredAt: new DateTimeImmutable('2024-01-01')
            );

            $response = $this->handler->handle($command);
            $responses[] = $response;

            $this->assertEquals($currency, $response->salaryCurrency);
        }

        // Verify all responses have unique IDs
        $ids = array_map(fn($response) => $response->id, $responses);
        $this->assertEquals(count($currencies), count(array_unique($ids)));
    }

    public function testHandlerWithMinimalData(): void
    {
        $command = new CreateEmployeeCommand(
            firstName: 'Min',
            lastName: 'Employee',
            email: 'min@example.com',
            position: 'Intern',
            salaryAmount: 30000.0,
            salaryCurrency: 'USD',
            hiredAt: new DateTimeImmutable('2024-08-01')
        );

        $this->employeeRepository
            ->expects($this->once())
            ->method('save');

        $response = $this->handler->handle($command);

        $this->assertEquals('Min Employee', $response->fullName);
        $this->assertEquals('min@example.com', $response->email);
        $this->assertEquals('Intern', $response->position);
        $this->assertEquals(30000, $response->salaryAmount);
        $this->assertEquals('USD', $response->salaryCurrency);
        $this->assertEquals('2024-08-01', $response->hiredAt);
    }
}