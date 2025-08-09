<?php

declare(strict_types=1);

namespace App\Tests\Domain\Employee\Entity;

use App\Domain\Employee\Employee;
use App\Domain\Employee\ValueObject\Email;
use App\Domain\Employee\ValueObject\FullName;
use App\Domain\Employee\ValueObject\Position;
use App\Domain\Employee\ValueObject\Salary;
use App\Domain\Shared\ValueObject\Uuid;
use App\Tests\Builder\EmployeeTestDataBuilder;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class EmployeeTest extends TestCase
{
    public function testCreateEmployeeWithValidData(): void
    {
        $id = Uuid::generate();
        $fullName = new FullName('John', 'Doe');
        $email = new Email('john.doe@example.com');
        $position = new Position('Software Developer');
        $salary = new Salary(50000, 'USD');
        $hiredAt = new DateTimeImmutable('2020-01-15');

        $employee = new Employee($id, $fullName, $email, $position, $salary, $hiredAt);

        $this->assertEquals($id, $employee->getId());
        $this->assertEquals($fullName, $employee->getFullName());
        $this->assertEquals($email, $employee->getEmail());
        $this->assertEquals($position, $employee->getPosition());
        $this->assertEquals($salary, $employee->getSalary());
        $this->assertEquals($hiredAt, $employee->getHiredAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $employee->getCreatedAt());
        $this->assertNull($employee->getUpdatedAt());
    }

    public function testCalculateYearsOfService(): void
    {
        // Employee hired 5 years ago
        $hiredAt = new DateTimeImmutable('-5 years');
        $employee = EmployeeTestDataBuilder::hired($hiredAt);

        $yearsOfService = $employee->getYearsOfService();

        $this->assertEquals(5, $yearsOfService);
    }

    public function testCalculateMonthsOfService(): void
    {
        // Employee hired 18 months ago
        $hiredAt = new DateTimeImmutable('-18 months');
        $employee = EmployeeTestDataBuilder::hired($hiredAt);

        $monthsOfService = $employee->getMonthsOfService();

        $this->assertEquals(18, $monthsOfService);
    }

    public function testCalculateAnnualVacationDaysForNewEmployee(): void
    {
        // Employee hired 2 years ago (0-4 years range)
        $hiredAt = new DateTimeImmutable('-2 years');
        $employee = EmployeeTestDataBuilder::hired($hiredAt);

        $vacationDays = $employee->calculateAnnualVacationDays();

        $this->assertEquals(15, $vacationDays); // Base 15 days
    }

    public function testCalculateAnnualVacationDaysForMidTermEmployee(): void
    {
        // Employee hired 7 years ago (5-9 years range)
        $employee = EmployeeTestDataBuilder::midTermEmployee();

        $vacationDays = $employee->calculateAnnualVacationDays();

        $this->assertEquals(20, $vacationDays); // Base 15 + 5 additional days
    }

    public function testCalculateAnnualVacationDaysForLongTermEmployee(): void
    {
        // Employee hired 12 years ago (10+ years range)
        $employee = EmployeeTestDataBuilder::longTermEmployee();

        $vacationDays = $employee->calculateAnnualVacationDays();

        $this->assertEquals(25, $vacationDays); // Base 15 + 10 additional days
    }

    public function testIsEligibleForVacationWhenEligible(): void
    {
        // Employee hired 6 months ago (eligible)
        $employee = EmployeeTestDataBuilder::eligibleForVacation();

        $this->assertTrue($employee->isEligibleForVacation());
    }

    public function testIsEligibleForVacationWhenNotEligible(): void
    {
        // Employee hired 2 months ago (not eligible)
        $employee = EmployeeTestDataBuilder::notEligibleForVacation();

        $this->assertFalse($employee->isEligibleForVacation());
    }

    public function testIsEligibleForVacationAtExactThreeMonths(): void
    {
        // Employee hired exactly 3 months ago (eligible)
        $hiredAt = new DateTimeImmutable('-3 months');
        $employee = EmployeeTestDataBuilder::hired($hiredAt);

        $this->assertTrue($employee->isEligibleForVacation());
    }

    public function testUpdatePosition(): void
    {
        $employee = EmployeeTestDataBuilder::valid();
        $originalUpdatedAt = $employee->getUpdatedAt();
        $newPosition = new Position('Senior Developer');

        $employee->updatePosition($newPosition);

        $this->assertEquals($newPosition, $employee->getPosition());
        $this->assertNotEquals($originalUpdatedAt, $employee->getUpdatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $employee->getUpdatedAt());
    }

    public function testUpdateSalary(): void
    {
        $employee = EmployeeTestDataBuilder::valid();
        $originalUpdatedAt = $employee->getUpdatedAt();
        $newSalary = new Salary(75000, 'USD');

        $employee->updateSalary($newSalary);

        $this->assertEquals($newSalary, $employee->getSalary());
        $this->assertNotEquals($originalUpdatedAt, $employee->getUpdatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $employee->getUpdatedAt());
    }

    public function testUpdateEmail(): void
    {
        $employee = EmployeeTestDataBuilder::valid();
        $originalUpdatedAt = $employee->getUpdatedAt();
        $newEmail = new Email('new.email@example.com');

        $employee->updateEmail($newEmail);

        $this->assertEquals($newEmail, $employee->getEmail());
        $this->assertNotEquals($originalUpdatedAt, $employee->getUpdatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $employee->getUpdatedAt());
    }

    public function testGetVacationEligibilityDate(): void
    {
        $hiredAt = new DateTimeImmutable('2024-01-15');
        $employee = EmployeeTestDataBuilder::hired($hiredAt);

        $eligibilityDate = $employee->getVacationEligibilityDate();

        $expectedDate = new DateTimeImmutable('2024-04-15'); // 3 months after hire date
        $this->assertEquals($expectedDate->format('Y-m-d'), $eligibilityDate->format('Y-m-d'));
    }

    public function testToArray(): void
    {
        $employee = EmployeeTestDataBuilder::valid();

        $array = $employee->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('full_name', $array);
        $this->assertArrayHasKey('email', $array);
        $this->assertArrayHasKey('position', $array);
        $this->assertArrayHasKey('salary', $array);
        $this->assertArrayHasKey('hired_at', $array);
        $this->assertArrayHasKey('years_of_service', $array);
        $this->assertArrayHasKey('annual_vacation_days', $array);
        $this->assertArrayHasKey('vacation_eligible', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);

        $this->assertEquals($employee->getId()->toString(), $array['id']);
        $this->assertEquals((string) $employee->getFullName(), $array['full_name']);
        $this->assertEquals((string) $employee->getEmail(), $array['email']);
        $this->assertEquals((string) $employee->getPosition(), $array['position']);
        $this->assertEquals((string) $employee->getSalary(), $array['salary']);
        $this->assertEquals($employee->getHiredAt()->format('Y-m-d'), $array['hired_at']);
        $this->assertEquals($employee->getYearsOfService(), $array['years_of_service']);
        $this->assertEquals($employee->calculateAnnualVacationDays(), $array['annual_vacation_days']);
        $this->assertEquals($employee->isEligibleForVacation(), $array['vacation_eligible']);
        $this->assertEquals($employee->getCreatedAt()->format('Y-m-d H:i:s'), $array['created_at']);
        $this->assertNull($array['updated_at']); // Initially null
    }

    public function testCreateStaticMethod(): void
    {
        $fullName = new FullName('Jane', 'Smith');
        $email = new Email('jane.smith@example.com');
        $position = new Position('Designer');
        $salary = new Salary(60000, 'USD');
        $hiredAt = new DateTimeImmutable('2023-03-01');

        $employee = Employee::create($fullName, $email, $position, $salary, $hiredAt);

        $this->assertInstanceOf(Employee::class, $employee);
        $this->assertInstanceOf(Uuid::class, $employee->getId());
        $this->assertEquals($fullName, $employee->getFullName());
        $this->assertEquals($email, $employee->getEmail());
        $this->assertEquals($position, $employee->getPosition());
        $this->assertEquals($salary, $employee->getSalary());
        $this->assertEquals($hiredAt, $employee->getHiredAt());
    }

    public function testVacationCalculationEdgeCases(): void
    {
        // Test exactly 5 years (should get 20 days)
        $hiredAt = new DateTimeImmutable('-5 years');
        $employee = EmployeeTestDataBuilder::hired($hiredAt);
        $this->assertEquals(20, $employee->calculateAnnualVacationDays());

        // Test exactly 10 years (should get 25 days)
        $hiredAt = new DateTimeImmutable('-10 years');
        $employee = EmployeeTestDataBuilder::hired($hiredAt);
        $this->assertEquals(25, $employee->calculateAnnualVacationDays());

        // Test 4 years 11 months (should still get 15 days)
        $hiredAt = new DateTimeImmutable('-4 years -11 months');
        $employee = EmployeeTestDataBuilder::hired($hiredAt);
        $this->assertEquals(15, $employee->calculateAnnualVacationDays());
    }

    public function testMonthsOfServiceCalculation(): void
    {
        // Test 1 year 6 months = 18 months
        $hiredAt = new DateTimeImmutable('-1 year -6 months');
        $employee = EmployeeTestDataBuilder::hired($hiredAt);
        $this->assertEquals(18, $employee->getMonthsOfService());

        // Test 2 years 3 months = 27 months
        $hiredAt = new DateTimeImmutable('-2 years -3 months');
        $employee = EmployeeTestDataBuilder::hired($hiredAt);
        $this->assertEquals(27, $employee->getMonthsOfService());
    }
}