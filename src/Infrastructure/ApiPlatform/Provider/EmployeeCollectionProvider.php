<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\Pagination\Pagination;
use App\Infrastructure\ApiResource\Employee;
use App\Domain\Employee\EmployeeRepository;

final readonly class EmployeeCollectionProvider implements ProviderInterface
{
    public function __construct(
        private EmployeeRepository $employeeRepository,
        private Pagination $pagination
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
            $apiEmployee->hiredAt = $employee->getHiredAt()->format('Y-m-d');
            $apiEmployee->createdAt = $employee->getCreatedAt();
            $apiEmployee->updatedAt = $employee->getUpdatedAt();
            $apiEmployee->fullName = $employee->getFullName()->fullName();
            $apiEmployee->yearsOfService = $employee->getYearsOfService();
            $apiEmployee->annualVacationDays = $employee->calculateAnnualVacationDays();
            $apiEmployee->vacationEligible = $employee->isEligibleForVacation();
            
            $apiEmployees[] = $apiEmployee;
        }
        
        // Apply pagination manually since we're using a custom provider
        $page = $context['filters']['page'] ?? 1;
        $itemsPerPage = $operation->getPaginationItemsPerPage() ?? 20;
        $offset = ($page - 1) * $itemsPerPage;
        
        $totalItems = count($apiEmployees);
        $paginatedItems = array_slice($apiEmployees, $offset, $itemsPerPage);
        
        return $this->pagination->paginate($paginatedItems, $totalItems, $page, $itemsPerPage);
    }
}