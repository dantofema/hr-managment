<?php

namespace App\DataFixtures;

use App\Domain\Employee\Entity\Employee;
use App\Domain\Employee\ValueObject\Department;
use App\Domain\Employee\ValueObject\Email;
use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Employee\ValueObject\EmployeeName;
use App\Domain\Employee\ValueObject\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EmployeeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $employee1 = new Employee(
            EmployeeId::generate(),
            EmployeeName::fromString('John Doe'),
            Email::fromString('john.doe@example.com'),
            Department::Engineering,
            Role::SeniorDeveloper
        );
        $manager->persist($employee1);

        $employee2 = new Employee(
            EmployeeId::generate(),
            EmployeeName::fromString('Jane Smith'),
            Email::fromString('jane.smith@example.com'),
            Department::Marketing,
            Role::Manager
        );
        $manager->persist($employee2);

        $employee3 = new Employee(
            EmployeeId::generate(),
            EmployeeName::fromString('Peter Jones'),
            Email::fromString('peter.jones@example.com'),
            Department::HR,
            Role::HRSpecialist
        );
        $manager->persist($employee3);

        $manager->flush();
    }
}

