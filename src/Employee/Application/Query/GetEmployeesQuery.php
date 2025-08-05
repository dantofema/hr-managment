<?php

declare(strict_types=1);

namespace App\Employee\Application\Query;

final readonly class GetEmployeesQuery
{
    public function __construct(
        public int $page,
        public int $limit,
        public array $filters
    ) {
    }
}

