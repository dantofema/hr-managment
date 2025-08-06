<?php

declare(strict_types=1);

namespace App\Application\Employee\Handler;

use App\Application\Employee\Query\GetEmployeesQuery;
use App\Domain\Employee\Repository\EmployeeRepositoryInterface;
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

        // Get total count for pagination
        $total = $this->employeeRepository->countAll($query->filters);
        $pages = (int) ceil($total / $query->limit);

        return [
            'data' => array_map(fn($employee) => $employee->getFullInfo(), $employees),
            'pagination' => [
                'page' => $query->page,
                'limit' => $query->limit,
                'total' => $total,
                'pages' => $pages
            ]
        ];
    }
}

