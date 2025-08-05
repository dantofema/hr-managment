<?php

declare(strict_types=1);

namespace App\Domain\Employee\Repository;

use App\Domain\Employee\Entity\Employee;
use App\Domain\Employee\ValueObject\Email;
use App\Domain\Employee\ValueObject\EmployeeId;

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

    public function delete(Employee $employee): void;
}
