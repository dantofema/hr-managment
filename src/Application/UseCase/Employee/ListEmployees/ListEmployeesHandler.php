<?php

declare(strict_types=1);

namespace App\Application\UseCase\Employee\ListEmployees;

use App\Application\UseCase\Employee\GetEmployee\GetEmployeeResponse;
use App\Domain\Employee\EmployeeRepository;

final readonly class ListEmployeesHandler
{
    public function __construct(
        private EmployeeRepository $employeeRepository
    ) {}

    public function handle(ListEmployeesQuery $query): ListEmployeesResponse
    {
        // Get all employees from repository
        $allEmployees = $this->employeeRepository->findAll();
        $total = count($allEmployees);
        
        // Calculate pagination
        $totalPages = (int) ceil($total / $query->limit);
        $offset = ($query->page - 1) * $query->limit;
        
        // Get employees for current page
        $pageEmployees = array_slice($allEmployees, $offset, $query->limit);
        
        // Convert to response DTOs
        $employeeResponses = array_map(
            fn($employee) => $this->createEmployeeResponse($employee),
            $pageEmployees
        );
        
        return new ListEmployeesResponse(
            employees: $employeeResponses,
            total: $total,
            page: $query->page,
            limit: $query->limit,
            totalPages: $totalPages
        );
    }

    private function createEmployeeResponse($employee): GetEmployeeResponse
    {
        // Calculate business logic values
        $yearsOfService = $this->calculateYearsOfService($employee->getHiredAt());
        $annualVacationDays = $this->calculateAnnualVacationDays($yearsOfService);
        $vacationEligible = $this->isVacationEligible($employee->getHiredAt());

        return new GetEmployeeResponse(
            $employee->getId()->toString(),
            $employee->getFullName()->getFirstName() . ' ' . $employee->getFullName()->getLastName(),
            $employee->getEmail()->toString(),
            $employee->getPosition()->value(),
            $employee->getSalary()->getAmount(),
            $employee->getSalary()->getCurrency(),
            $employee->getHiredAt()->format('Y-m-d'),
            $yearsOfService,
            $annualVacationDays,
            $vacationEligible
        );
    }

    private function calculateYearsOfService(\DateTimeImmutable $hiredAt): int
    {
        $now = new \DateTimeImmutable();
        return $now->diff($hiredAt)->y;
    }

    private function calculateAnnualVacationDays(int $yearsOfService): int
    {
        // Base vacation days: 15 days
        $baseDays = 15;
        
        // Additional days based on years of service
        if ($yearsOfService >= 10) {
            return $baseDays + 10; // 25 days for 10+ years
        } elseif ($yearsOfService >= 5) {
            return $baseDays + 5; // 20 days for 5-9 years
        }
        
        return $baseDays; // 15 days for 0-4 years
    }

    private function isVacationEligible(\DateTimeImmutable $hiredAt): bool
    {
        $now = new \DateTimeImmutable();
        $monthsOfService = ($now->diff($hiredAt)->y * 12) + $now->diff($hiredAt)->m;
        
        // Employee must have worked for at least 3 months to be eligible for vacation
        return $monthsOfService >= 3;
    }
}