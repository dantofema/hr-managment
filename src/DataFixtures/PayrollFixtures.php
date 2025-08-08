<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\Employee\Employee as DomainEmployee;
use App\Infrastructure\Doctrine\Entity\Employee;
use App\Domain\Payroll\Payroll as DomainPayroll;
use App\Domain\Payroll\ValueObject\Deductions;
use App\Domain\Payroll\ValueObject\GrossSalary;
use App\Domain\Payroll\ValueObject\PayrollPeriod;
use App\Domain\Payroll\ValueObject\PayrollStatus;
use App\Infrastructure\Doctrine\Entity\Payroll;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class PayrollFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        // Currency mapping for valid payroll currencies
        $currencyMapping = [
            'USD' => 'USD',
            'EUR' => 'EUR', 
            'ARS' => 'USD', // Map ARS to USD for payroll since ARS is not in valid currencies
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
            
            // Generate 3-6 payrolls per employee
            $payrollCount = $this->faker->numberBetween(3, 6);
            
            // Get employee salary info
            $employeeSalary = $employee->getSalary();
            $monthlySalary = $employeeSalary->amount() / 12; // Convert annual to monthly
            $originalCurrency = $employeeSalary->currency();
            $payrollCurrency = $currencyMapping[$originalCurrency];
            
            // Adjust salary if currency mapping changed
            if ($originalCurrency === 'ARS') {
                $monthlySalary = $monthlySalary / 350; // Convert ARS back to USD equivalent
            }

            // Generate consecutive monthly payrolls starting from hire date
            $hiredAt = $employee->getHiredAt();
            $startMonth = (int) $hiredAt->format('n');
            $startYear = (int) $hiredAt->format('Y');
            
            for ($j = 0; $j < $payrollCount; $j++) {
                // Calculate the month and year for this payroll
                $monthsFromStart = $j;
                $currentMonth = $startMonth + $monthsFromStart;
                $currentYear = $startYear;
                
                // Handle year overflow
                while ($currentMonth > 12) {
                    $currentMonth -= 12;
                    $currentYear++;
                }
                
                // Don't create payrolls for future months
                $now = new \DateTimeImmutable();
                $payrollDate = new \DateTimeImmutable(sprintf('%d-%02d-01', $currentYear, $currentMonth));
                if ($payrollDate > $now) {
                    break;
                }

                $period = PayrollPeriod::forMonth($currentYear, $currentMonth);
                $grossSalary = new GrossSalary($monthlySalary, $payrollCurrency);
                
                // Calculate realistic deductions
                $taxRate = $this->faker->randomFloat(2, 0.15, 0.25); // 15-25%
                $socialSecurityRate = $this->faker->randomFloat(2, 0.08, 0.12); // 8-12%
                $healthInsuranceRate = $this->faker->randomFloat(2, 0.03, 0.05); // 3-5%
                
                $taxes = $monthlySalary * $taxRate;
                $socialSecurity = $monthlySalary * $socialSecurityRate;
                $healthInsurance = $monthlySalary * $healthInsuranceRate;
                
                $deductions = new Deductions(
                    $taxes,
                    $socialSecurity,
                    $healthInsurance,
                    $payrollCurrency
                );

                $domainPayroll = DomainPayroll::create(
                    $employee->getId(),
                    $period,
                    $grossSalary,
                    $deductions
                );

                // Set realistic status distribution
                $statusRand = $this->faker->randomFloat(2, 0, 1);
                if ($statusRand < 0.7) {
                    // 70% processed and paid
                    $domainPayroll->process();
                    if ($this->faker->boolean(80)) {
                        // 80% of processed are paid (56% total)
                        $reflection = new \ReflectionClass($domainPayroll);
                        $statusProperty = $reflection->getProperty('status');
                        $statusProperty->setAccessible(true);
                        $statusProperty->setValue($domainPayroll, PayrollStatus::paid());
                    }
                } elseif ($statusRand < 0.9) {
                    // 20% remain pending (no action needed, default is pending)
                } else {
                    // 10% cancelled
                    $domainPayroll->cancel();
                }

                $infrastructurePayroll = Payroll::fromDomain($domainPayroll, $infrastructureEmployee);
                $manager->persist($infrastructurePayroll);
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
        return 2; // Load after employees
    }
}