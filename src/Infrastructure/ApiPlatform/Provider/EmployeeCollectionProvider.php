<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Infrastructure\ApiResource\Employee;
use App\Domain\Employee\EmployeeRepository;

final readonly class EmployeeCollectionProvider implements ProviderInterface
{
    public function __construct(
        private EmployeeRepository $employeeRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $employees = $this->employeeRepository->findAll();
        
        $apiEmployees = [];
        foreach ($employees as $employee) {
            $apiEmployee = new Employee();
            $apiEmployee->id = $employee->getId()->toString();
            $apiEmployee->firstName = $employee->getFullName()->firstName();
            $apiEmployee->lastName = $employee->getFullName()->lastName();
            $apiEmployee->email = $employee->getEmail()->value();
            $apiEmployee->position = $employee->getPosition()->value();
            $apiEmployee->salaryAmount = $employee->getSalary()->amount();
            $apiEmployee->salaryCurrency = $employee->getSalary()->currency();
            $apiEmployee->hiredAt = $employee->getHiredAt();
            $apiEmployee->createdAt = $employee->getCreatedAt();
            $apiEmployee->updatedAt = $employee->getUpdatedAt();
            $apiEmployee->fullName = $employee->getFullName()->fullName();
            $apiEmployee->yearsOfService = $employee->getYearsOfService();
            $apiEmployee->annualVacationDays = $employee->calculateAnnualVacationDays();
            $apiEmployee->vacationEligible = $employee->isEligibleForVacation();
            
            $apiEmployees[] = $apiEmployee;
        }
        
        return $apiEmployees;
    }
}