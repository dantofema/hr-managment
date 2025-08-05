<?php

declare(strict_types=1);

namespace App\Employee\Domain\Repository;

use App\Employee\Domain\Entity\Employee;
use App\Employee\Domain\ValueObject\Email;
use App\Employee\Domain\ValueObject\EmployeeId;

interface EmployeeRepositoryInterface
{
    public function save(Employee $employee): void;

    public function findById(EmployeeId $id): ?Employee;

    public function findByEmail(Email $email): ?Employee;

    /**
     * @return Employee[]
     */
    public function findAllPaginated(
        int $page,
        int $limit,
        array $filters
    ): array;
}

