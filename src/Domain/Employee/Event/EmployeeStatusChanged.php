<?php

declare(strict_types=1);

namespace App\Domain\Employee\Event;

use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Employee\ValueObject\EmployeeStatus;
use DateTimeImmutable;
use Symfony\Contracts\EventDispatcher\Event;

final class EmployeeStatusChanged extends Event
{
    public const NAME = 'employee.status.changed';

    public function __construct(
        public readonly EmployeeId $employeeId,
        public readonly EmployeeStatus $newStatus,
        public readonly DateTimeImmutable $occurredOn
    ) {
    }
}

