<?php

declare(strict_types=1);

namespace App\Domain\Vacation;

use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\Vacation\ValueObject\VacationStatus;
use App\Domain\Vacation\ValueObject\VacationPeriod;

interface VacationRepository
{
    public function save(Vacation $vacation): void;

    public function findById(Uuid $id): ?Vacation;

    public function findByEmployeeId(Uuid $employeeId): array;

    public function findByStatus(VacationStatus $status): array;

    public function findByEmployeeAndStatus(Uuid $employeeId, VacationStatus $status): array;

    public function findOverlappingVacations(Uuid $employeeId, VacationPeriod $period): array;

    public function findActiveVacations(): array;

    public function findUpcomingVacations(): array;

    public function findVacationsByDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array;

    public function delete(Vacation $vacation): void;

    public function findAll(): array;
}