<?php

declare(strict_types=1);

namespace App\Employee\Domain\Event;

use App\Employee\Domain\ValueObject\EmployeeId;
use App\Employee\Domain\ValueObject\EmployeeStatus;
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

