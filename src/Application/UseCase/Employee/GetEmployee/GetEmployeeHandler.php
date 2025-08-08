<?php

declare(strict_types=1);

namespace App\Application\UseCase\Employee\GetEmployee;

use App\Domain\Employee\EmployeeRepository;
use App\Domain\Shared\ValueObject\Uuid;

final readonly class GetEmployeeHandler
{
    public function __construct(
        private EmployeeRepository $employeeRepository
    ) {}

    public function handle(GetEmployeeQuery $query): GetEmployeeResponse
    {
        $employee = $this->employeeRepository->findById(new Uuid($query->employeeId));

        if (!$employee) {
            throw new \InvalidArgumentException('Employee not found');
        }

        // Calculate business logic values
        $yearsOfService = $this->calculateYearsOfService($employee->getHiredAt());
        $annualVacationDays = $this->calculateAnnualVacationDays($yearsOfService);
        $vacationEligible = $this->isVacationEligible($employee->getHiredAt());

        return new GetEmployeeResponse(
            $employee->getId()->toString(),
            $employee->getFullName()->getFirstName() . ' ' . $employee->getFullName()->getLastName(),
            $employee->getEmail()->toString(),
            $employee->getPosition()->toString(),
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