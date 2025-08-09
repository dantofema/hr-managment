<?php

declare(strict_types=1);

namespace App\Tests\Builder;

use App\Domain\Employee\Employee;
use App\Domain\Employee\ValueObject\Email;
use App\Domain\Employee\ValueObject\FullName;
use App\Domain\Employee\ValueObject\Position;
use App\Domain\Employee\ValueObject\Salary;
use App\Domain\Shared\ValueObject\Uuid;
use DateTimeImmutable;

class EmployeeTestDataBuilder
{
    public static function valid(): Employee
    {
        return new Employee(
            Uuid::generate(),
            new FullName('John', 'Doe'),
            new Email('john.doe@example.com'),
            new Position('Software Developer'),
            new Salary(50000, 'USD'),
            new DateTimeImmutable('2020-01-15')
        );
    }

    public static function withEmail(string $email): Employee
    {
        return new Employee(
            Uuid::generate(),
            new FullName('Jane', 'Smith'),
            new Email($email),
            new Position('Software Developer'),
            new Salary(55000, 'USD'),
            new DateTimeImmutable('2021-03-10')
        );
    }

    public static function hired(DateTimeImmutable $hiredAt): Employee
    {
        return new Employee(
            Uuid::generate(),
            new FullName('Alice', 'Johnson'),
            new Email('alice.johnson@example.com'),
            new Position('Senior Developer'),
            new Salary(75000, 'USD'),
            $hiredAt
        );
    }

    public static function minimal(): Employee
    {
        return new Employee(
            Uuid::generate(),
            new FullName('Min', 'Employee'),
            new Email('min@example.com'),
            new Position('Intern'),
            new Salary(30000, 'USD'),
            new DateTimeImmutable('2024-01-01')
        );
    }

    public static function withSalary(int $amount, string $currency = 'USD'): Employee
    {
        return new Employee(
            Uuid::generate(),
            new FullName('Rich', 'Person'),
            new Email('rich.person@example.com'),
            new Position('Manager'),
            new Salary($amount, $currency),
            new DateTimeImmutable('2019-06-01')
        );
    }

    public static function withPosition(string $position): Employee
    {
        return new Employee(
            Uuid::generate(),
            new FullName('Position', 'Holder'),
            new Email('position.holder@example.com'),
            new Position($position),
            new Salary(60000, 'USD'),
            new DateTimeImmutable('2022-04-15')
        );
    }

    public static function longTermEmployee(): Employee
    {
        // Employee hired 12 years ago
        $hiredAt = new DateTimeImmutable('-12 years');
        
        return new Employee(
            Uuid::generate(),
            new FullName('Veteran', 'Employee'),
            new Email('veteran@example.com'),
            new Position('Senior Manager'),
            new Salary(90000, 'USD'),
            $hiredAt
        );
    }

    public static function midTermEmployee(): Employee
    {
        // Employee hired 7 years ago
        $hiredAt = new DateTimeImmutable('-7 years');
        
        return new Employee(
            Uuid::generate(),
            new FullName('Mid', 'Career'),
            new Email('mid.career@example.com'),
            new Position('Team Lead'),
            new Salary(70000, 'USD'),
            $hiredAt
        );
    }

    public static function newEmployee(): Employee
    {
        // Employee hired 2 months ago
        $hiredAt = new DateTimeImmutable('-2 months');
        
        return new Employee(
            Uuid::generate(),
            new FullName('New', 'Hire'),
            new Email('new.hire@example.com'),
            new Position('Junior Developer'),
            new Salary(45000, 'USD'),
            $hiredAt
        );
    }

    public static function eligibleForVacation(): Employee
    {
        // Employee hired 6 months ago (eligible for vacation)
        $hiredAt = new DateTimeImmutable('-6 months');
        
        return new Employee(
            Uuid::generate(),
            new FullName('Vacation', 'Ready'),
            new Email('vacation.ready@example.com'),
            new Position('Developer'),
            new Salary(55000, 'USD'),
            $hiredAt
        );
    }

    public static function notEligibleForVacation(): Employee
    {
        // Employee hired 2 months ago (not eligible for vacation)
        $hiredAt = new DateTimeImmutable('-2 months');
        
        return new Employee(
            Uuid::generate(),
            new FullName('Too', 'New'),
            new Email('too.new@example.com'),
            new Position('Trainee'),
            new Salary(40000, 'USD'),
            $hiredAt
        );
    }

    public static function withId(Uuid $id): Employee
    {
        return new Employee(
            $id,
            new FullName('Custom', 'ID'),
            new Email('custom.id@example.com'),
            new Position('Developer'),
            new Salary(50000, 'USD'),
            new DateTimeImmutable('2023-01-01')
        );
    }

    public static function create(): Employee
    {
        return Employee::create(
            new FullName('Created', 'Employee'),
            new Email('created@example.com'),
            new Position('Developer'),
            new Salary(52000, 'USD'),
            new DateTimeImmutable('2023-05-01')
        );
    }
}