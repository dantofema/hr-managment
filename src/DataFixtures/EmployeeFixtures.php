<?php

namespace App\DataFixtures;

use App\Employee\Domain\Entity\Employee;
use App\Employee\Domain\ValueObject\Department;
use App\Employee\Domain\ValueObject\Email;
use App\Employee\Domain\ValueObject\EmployeeId;
use App\Employee\Domain\ValueObject\EmployeeName;
use App\Employee\Domain\ValueObject\Role;
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

