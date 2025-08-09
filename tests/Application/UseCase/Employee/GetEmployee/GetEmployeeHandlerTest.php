<?php

declare(strict_types=1);

namespace App\Tests\Application\UseCase\Employee\GetEmployee;

use App\Application\UseCase\Employee\GetEmployee\GetEmployeeHandler;
use App\Application\UseCase\Employee\GetEmployee\GetEmployeeQuery;
use App\Application\UseCase\Employee\GetEmployee\GetEmployeeResponse;
use App\Domain\Employee\Employee;
use App\Domain\Employee\EmployeeRepository;
use App\Domain\Shared\ValueObject\Uuid;
use App\Tests\Builder\EmployeeTestDataBuilder;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetEmployeeHandlerTest extends TestCase
{
    private GetEmployeeHandler $handler;
    private EmployeeRepository&MockObject $employeeRepository;

    protected function setUp(): void
    {
        $this->employeeRepository = $this->createMock(EmployeeRepository::class);
        $this->handler = new GetEmployeeHandler($this->employeeRepository);
    }

    public function testGetExistingEmployee(): void
    {
        $employeeId = Uuid::generate();
        $employee = EmployeeTestDataBuilder::withId($employeeId);
        $query = new GetEmployeeQuery($employeeId->toString());

        $this->employeeRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->callback(function (Uuid $uuid) use ($employeeId) {
                return $uuid->toString() === $employeeId->toString();
            }))
            ->willReturn($employee);

        $response = $this->handler->handle($query);

        $this->assertInstanceOf(GetEmployeeResponse::class, $response);
        $this->assertEquals($employeeId->toString(), $response->id);
        $this->assertEquals('Custom ID', $response->fullName);
        $this->assertEquals('custom.id@example.com', $response->email);
        $this->assertEquals('Developer', $response->position);
        $this->assertEquals(50000, $response->salaryAmount);
        $this->assertEquals('USD', $response->salaryCurrency);
        $this->assertEquals('2023-01-01', $response->hiredAt);
        $this->assertIsInt($response->yearsOfService);
        $this->assertIsInt($response->annualVacationDays);
        $this->assertIsBool($response->vacationEligible);
    }

    public function testEmployeeNotFound(): void
    {
        $employeeId = Uuid::generate();
        $query = new GetEmployeeQuery($employeeId->toString());

        $this->employeeRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->callback(function (Uuid $uuid) use ($employeeId) {
                return $uuid->toString() === $employeeId->toString();
            }))
            ->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Employee not found');

        $this->handler->handle($query);
    }

    public function testBusinessCalculationsIntegrity(): void
    {
        // Test with a long-term employee (12 years)
        $employee = EmployeeTestDataBuilder::longTermEmployee();
        $query = new GetEmployeeQuery($employee->getId()->toString());

        $this->employeeRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($employee);

        $response = $this->handler->handle($query);

        // Verify business calculations match domain logic
        $this->assertEquals($employee->getYearsOfService(), $response->yearsOfService);
        $this->assertEquals($employee->calculateAnnualVacationDays(), $response->annualVacationDays);
        $this->assertEquals($employee->isEligibleForVacation(), $response->vacationEligible);

        // Long-term employee should have 25 vacation days and be eligible
        $this->assertEquals(25, $response->annualVacationDays);
        $this->assertTrue($response->vacationEligible);
    }

    public function testMidTermEmployeeCalculations(): void
    {
        // Test with a mid-term employee (7 years)
        $employee = EmployeeTestDataBuilder::midTermEmployee();
        $query = new GetEmployeeQuery($employee->getId()->toString());

        $this->employeeRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($employee);

        $response = $this->handler->handle($query);

        // Mid-term employee should have 20 vacation days
        $this->assertEquals(20, $response->annualVacationDays);
        $this->assertTrue($response->vacationEligible);
        $this->assertEquals(7, $response->yearsOfService);
    }

    public function testNewEmployeeCalculations(): void
    {
        // Test with a new employee (2 months)
        $employee = EmployeeTestDataBuilder::newEmployee();
        $query = new GetEmployeeQuery($employee->getId()->toString());

        $this->employeeRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($employee);

        $response = $this->handler->handle($query);

        // New employee should have 15 vacation days but not be eligible yet
        $this->assertEquals(15, $response->annualVacationDays);
        $this->assertFalse($response->vacationEligible);
        $this->assertEquals(0, $response->yearsOfService);
    }

    public function testVacationEligibleEmployee(): void
    {
        // Test with an employee eligible for vacation (6 months)
        $employee = EmployeeTestDataBuilder::eligibleForVacation();
        $query = new GetEmployeeQuery($employee->getId()->toString());

        $this->employeeRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($employee);

        $response = $this->handler->handle($query);

        $this->assertTrue($response->vacationEligible);
        $this->assertEquals(15, $response->annualVacationDays); // Still in 0-4 years range
    }

    public function testResponseContainsAllRequiredFields(): void
    {
        $employee = EmployeeTestDataBuilder::valid();
        $query = new GetEmployeeQuery($employee->getId()->toString());

        $this->employeeRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($employee);

        $response = $this->handler->handle($query);

        $this->assertNotEmpty($response->id);
        $this->assertIsString($response->id);
        $this->assertNotEmpty($response->fullName);
        $this->assertIsString($response->fullName);
        $this->assertNotEmpty($response->email);
        $this->assertIsString($response->email);
        $this->assertNotEmpty($response->position);
        $this->assertIsString($response->position);
        $this->assertIsFloat($response->salaryAmount);
        $this->assertNotEmpty($response->salaryCurrency);
        $this->assertIsString($response->salaryCurrency);
        $this->assertNotEmpty($response->hiredAt);
        $this->assertIsString($response->hiredAt);
        $this->assertIsInt($response->yearsOfService);
        $this->assertIsInt($response->annualVacationDays);
        $this->assertIsBool($response->vacationEligible);
    }

    public function testFullNameConcatenation(): void
    {
        $employee = EmployeeTestDataBuilder::valid();
        $query = new GetEmployeeQuery($employee->getId()->toString());

        $this->employeeRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($employee);

        $response = $this->handler->handle($query);

        $expectedFullName = $employee->getFullName()->getFirstName() . ' ' . $employee->getFullName()->getLastName();
        $this->assertEquals($expectedFullName, $response->fullName);
    }

    public function testDateFormatting(): void
    {
        $employee = EmployeeTestDataBuilder::valid();
        $query = new GetEmployeeQuery($employee->getId()->toString());

        $this->employeeRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($employee);

        $response = $this->handler->handle($query);

        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}/', $response->hiredAt);
        $this->assertEquals($employee->getHiredAt()->format('Y-m-d'), $response->hiredAt);
    }

    public function testVacationCalculationEdgeCases(): void
    {
        // Test employee hired exactly 5 years ago (should get 20 days)
        $hiredAt = new DateTimeImmutable('-5 years');
        $employee = EmployeeTestDataBuilder::hired($hiredAt);
        $query = new GetEmployeeQuery($employee->getId()->toString());

        $this->employeeRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($employee);

        $response = $this->handler->handle($query);

        $this->assertEquals(5, $response->yearsOfService);
        $this->assertEquals(20, $response->annualVacationDays);
        $this->assertTrue($response->vacationEligible);
    }

    public function testVacationCalculationTenYears(): void
    {
        // Test employee hired exactly 10 years ago (should get 25 days)
        $hiredAt = new DateTimeImmutable('-10 years');
        $employee = EmployeeTestDataBuilder::hired($hiredAt);
        $query = new GetEmployeeQuery($employee->getId()->toString());

        $this->employeeRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn($employee);

        $response = $this->handler->handle($query);

        $this->assertEquals(10, $response->yearsOfService);
        $this->assertEquals(25, $response->annualVacationDays);
        $this->assertTrue($response->vacationEligible);
    }

    public function testRepositoryCalledWithCorrectUuid(): void
    {
        $employeeId = 'test-uuid-string';
        $employee = EmployeeTestDataBuilder::valid();
        $query = new GetEmployeeQuery($employeeId);

        $this->employeeRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->callback(function (Uuid $uuid) use ($employeeId) {
                return $uuid->toString() === $employeeId;
            }))
            ->willReturn($employee);

        $this->handler->handle($query);
    }

    public function testHandlerWithDifferentEmployeeTypes(): void
    {
        $employees = [
            EmployeeTestDataBuilder::minimal(),
            EmployeeTestDataBuilder::longTermEmployee(),
            EmployeeTestDataBuilder::midTermEmployee(),
            EmployeeTestDataBuilder::newEmployee(),
        ];

        foreach ($employees as $employee) {
            $query = new GetEmployeeQuery($employee->getId()->toString());

            $this->employeeRepository
                ->expects($this->once())
                ->method('findById')
                ->willReturn($employee);

            $response = $this->handler->handle($query);

            $this->assertEquals($employee->getId()->toString(), $response->id);
            $this->assertEquals($employee->getYearsOfService(), $response->yearsOfService);
            $this->assertEquals($employee->calculateAnnualVacationDays(), $response->annualVacationDays);
            $this->assertEquals($employee->isEligibleForVacation(), $response->vacationEligible);

            // Reset mock for next iteration
            $this->setUp();
        }
    }
}