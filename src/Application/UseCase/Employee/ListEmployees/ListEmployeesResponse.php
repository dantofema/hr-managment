<?php

declare(strict_types=1);

namespace App\Application\UseCase\Employee\ListEmployees;

use App\Application\UseCase\Employee\GetEmployee\GetEmployeeResponse;

final readonly class ListEmployeesResponse
{
    /**
     * @param GetEmployeeResponse[] $employees
     */
    public function __construct(
        public array $employees,
        public int $total,
        public int $page,
        public int $limit,
        public int $totalPages
    ) {}
}