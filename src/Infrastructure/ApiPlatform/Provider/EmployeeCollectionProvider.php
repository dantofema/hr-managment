<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\ArrayPaginator;
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
        error_log("EmployeeCollectionProvider::provide() called");
        $employees = $this->employeeRepository->findAll();
        error_log("EmployeeCollectionProvider found " . count($employees) . " employees");
        
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
        
        // Get pagination parameters from context
        $page = (int) ($context['filters']['page'] ?? 1);
        $itemsPerPage = $operation->getPaginationItemsPerPage() ?? 20;
        $offset = ($page - 1) * $itemsPerPage;
        
        error_log("EmployeeCollectionProvider total employees: " . count($apiEmployees));
        error_log("EmployeeCollectionProvider offset: " . $offset . ", itemsPerPage: " . $itemsPerPage);
        
        // Let ArrayPaginator handle pagination internally
        $totalItems = count($apiEmployees);
        error_log("ArrayPaginator params: fullArray=" . count($apiEmployees) . ", offset=$offset, itemsPerPage=$itemsPerPage");
        
        // Return API Platform ArrayPaginator with full results - it handles pagination internally
        // ArrayPaginator constructor: (array $results, int $firstResult, int $maxResults)
        $paginator = new ArrayPaginator($apiEmployees, $offset, $itemsPerPage);
        error_log("ArrayPaginator getTotalItems(): " . $paginator->getTotalItems());
        error_log("ArrayPaginator count(): " . $paginator->count());
        return $paginator;
    }
}