<?php

declare(strict_types=1);

namespace App\Application\UseCase\Employee\ListEmployees;

final readonly class ListEmployeesQuery
{
    public function __construct(
        public int $page = 1,
        public int $limit = 10
    ) {}
}