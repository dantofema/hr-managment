<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\Employee\Employee as DomainEmployee;
use App\Infrastructure\Doctrine\Entity\Employee;
use App\Domain\Vacation\Vacation as DomainVacation;
use App\Infrastructure\Doctrine\Entity\Vacation;
use App\Domain\Vacation\ValueObject\VacationPeriod;
use App\Domain\Vacation\ValueObject\VacationStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class VacationFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        $reasons = [
            'Vacaciones familiares',
            'Descanso personal',
            'Viaje',
            'Asuntos personales',
            'Vacaciones de verano',
            'Fiestas navideñas',
            'Vacaciones de invierno',
            'Viaje de bodas',
            'Visita familiar',
            'Descanso médico',
            'Vacaciones programadas',
            'Tiempo personal'
        ];

        // Iterate through employee references (max 100 to be safe)
        for ($i = 0; $i < 100; $i++) {
            try {
                /** @var Employee $infrastructureEmployee */
                $infrastructureEmployee = $this->getReference('employee_' . $i, Employee::class);
                $employee = $infrastructureEmployee->toDomain();
            } catch (\Exception $e) {
                break; // No more employees
            }
            
            // Check if employee is eligible for vacation (3+ months of service)
            if (!$employee->isEligibleForVacation()) {
                continue; // Skip employees not eligible for vacation
            }
            
            // Generate 2-8 vacation requests per eligible employee
            $vacationCount = $this->faker->numberBetween(2, 8);
            
            $createdVacations = [];
            
            for ($j = 0; $j < $vacationCount; $j++) {
                // Generate vacation periods that don't overlap
                $maxAttempts = 10;
                $attempts = 0;
                $validPeriod = null;
                
                while ($attempts < $maxAttempts && $validPeriod === null) {
                    // Generate vacation starting from tomorrow to 6 months in the future
                    $startDate = $this->faker->dateTimeBetween('+1 day', '+6 months');
                    
                    // Generate vacation duration between 3-15 working days
                    $workingDays = $this->faker->numberBetween(3, 15);
                    
                    // Calculate end date considering weekends
                    $endDate = clone $startDate;
                    $addedDays = 0;
                    $totalDays = 0;
                    
                    while ($addedDays < $workingDays) {
                        $endDate->modify('+1 day');
                        $totalDays++;
                        
                        // Count only working days (Monday to Friday)
                        $dayOfWeek = (int) $endDate->format('N');
                        if ($dayOfWeek < 6) {
                            $addedDays++;
                        }
                        
                        // Safety check to prevent infinite loop
                        if ($totalDays > 30) {
                            break;
                        }
                    }
                    
                    try {
                        $period = new VacationPeriod(
                            \DateTimeImmutable::createFromMutable($startDate),
                            \DateTimeImmutable::createFromMutable($endDate)
                        );
                        
                        // Check for overlaps with existing vacations for this employee
                        $hasOverlap = false;
                        foreach ($createdVacations as $existingVacation) {
                            if ($period->overlaps($existingVacation->getPeriod())) {
                                $hasOverlap = true;
                                break;
                            }
                        }
                        
                        if (!$hasOverlap) {
                            $validPeriod = $period;
                        }
                    } catch (\InvalidArgumentException $e) {
                        // Period validation failed, try again
                    }
                    
                    $attempts++;
                }
                
                if ($validPeriod === null) {
                    continue; // Skip this vacation if we couldn't generate a valid period
                }
                
                $reason = $this->faker->randomElement($reasons);
                
                $domainVacation = DomainVacation::request(
                    \App\Domain\Shared\ValueObject\Uuid::generate(),
                    $employee->getId(),
                    $validPeriod,
                    $reason
                );
                
                // Set realistic status distribution: 70% approved, 20% pending, 10% rejected
                $statusRand = $this->faker->randomFloat(2, 0, 1);
                
                if ($statusRand < 0.7) {
                    // 70% approved
                    $domainVacation->approve();
                } elseif ($statusRand < 0.9) {
                    // 20% remain pending (no action needed, default is pending)
                } else {
                    // 10% rejected
                    $rejectionReasons = [
                        'Período muy ocupado en el proyecto',
                        'Conflicto con vacaciones de otros empleados',
                        'Necesidad de cobertura en el equipo',
                        'Fechas no disponibles',
                        'Solicitud fuera de política',
                        'Período de alta demanda'
                    ];
                    $rejectionReason = $this->faker->randomElement($rejectionReasons);
                    $domainVacation->reject($rejectionReason);
                }
                
                $createdVacations[] = $domainVacation;
                $infrastructureVacation = Vacation::fromDomain($domainVacation, $infrastructureEmployee);
                $manager->persist($infrastructureVacation);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            EmployeeFixtures::class,
        ];
    }

    public function getOrder(): int
    {
        return 3; // Load after employees and payrolls
    }
}