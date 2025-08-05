<?php

declare(strict_types=1);

namespace App\Application\Employee\Query;

final readonly class GetEmployeesQuery
{
    public function __construct(
        public int $page,
        public int $limit,
        public array $filters
    ) {
    }
}

