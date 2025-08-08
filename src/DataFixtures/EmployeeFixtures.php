<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\Employee\Employee as DomainEmployee;
use App\Domain\Employee\ValueObject\Email;
use App\Domain\Employee\ValueObject\FullName;
use App\Domain\Employee\ValueObject\Position;
use App\Domain\Employee\ValueObject\Salary;
use App\Infrastructure\Doctrine\Entity\Employee;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class EmployeeFixtures extends Fixture
{
    private Generator $faker;
    private int $employeeCount = 0;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $positions = [
            'Software Developer' => ['min' => 45000, 'max' => 85000],
            'Senior Software Developer' => ['min' => 70000, 'max' => 120000],
            'Project Manager' => ['min' => 60000, 'max' => 95000],
            'QA Engineer' => ['min' => 40000, 'max' => 70000],
            'DevOps Engineer' => ['min' => 65000, 'max' => 110000],
            'UX Designer' => ['min' => 50000, 'max' => 80000],
            'Data Analyst' => ['min' => 55000, 'max' => 85000],
            'Product Owner' => ['min' => 70000, 'max' => 100000],
            'Scrum Master' => ['min' => 60000, 'max' => 90000],
            'Frontend Developer' => ['min' => 45000, 'max' => 80000],
            'Backend Developer' => ['min' => 50000, 'max' => 90000],
            'Full Stack Developer' => ['min' => 55000, 'max' => 95000],
            'System Administrator' => ['min' => 45000, 'max' => 75000],
            'Database Administrator' => ['min' => 60000, 'max' => 95000],
            'Security Engineer' => ['min' => 70000, 'max' => 115000],
        ];

        $currencies = ['USD', 'EUR', 'ARS'];
        $domains = ['company.com', 'techcorp.com', 'gmail.com', 'outlook.com', 'yahoo.com'];

        // Generate between 25-50 employees
        $employeeCount = $this->faker->numberBetween(25, 50);

        for ($i = 0; $i < $employeeCount; $i++) {
            $firstName = $this->faker->firstName();
            $lastName = $this->faker->lastName();
            $positionName = $this->faker->randomElement(array_keys($positions));
            $salaryRange = $positions[$positionName];
            $currency = $this->faker->randomElement($currencies);
            
            // Adjust salary based on currency
            $baseSalary = $this->faker->numberBetween($salaryRange['min'], $salaryRange['max']);
            $salaryAmount = match ($currency) {
                'EUR' => $baseSalary * 0.85, // EUR is typically lower than USD
                'ARS' => $baseSalary * 350, // ARS conversion (approximate)
                default => $baseSalary, // USD
            };

            // Create unique email
            $emailPrefix = strtolower($firstName . '.' . $lastName);
            $domain = $this->faker->randomElement($domains);
            $email = $emailPrefix . $i . '@' . $domain; // Add index to ensure uniqueness

            // Hired date within last 5 years
            $hiredAt = $this->faker->dateTimeBetween('-5 years', '-1 month');

            $domainEmployee = DomainEmployee::create(
                new FullName($firstName, $lastName),
                new Email($email),
                new Position($positionName),
                new Salary($salaryAmount, $currency),
                \DateTimeImmutable::createFromMutable($hiredAt)
            );

            $infrastructureEmployee = Employee::fromDomain($domainEmployee);
            $manager->persist($infrastructureEmployee);
            
            // Store reference for use in other fixtures
            $this->addReference('employee_' . $i, $infrastructureEmployee);
        }

        // Other fixtures will iterate through employee references using try-catch

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1; // Load employees first
    }
}