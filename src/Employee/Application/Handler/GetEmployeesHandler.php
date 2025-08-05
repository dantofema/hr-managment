<?php

declare(strict_types=1);

namespace App\Employee\Application\Handler;

use App\Employee\Application\Query\GetEmployeesQuery;
use App\Employee\Domain\Repository\EmployeeRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetEmployeesHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {
    }

    public function __invoke(GetEmployeesQuery $query): array
    {
        $employees = $this->employeeRepository->findAllPaginated($query->page,
            $query->limit, $query->filters);

        return array_map(fn($employee) => $employee->getFullInfo(), $employees);
    }
}

