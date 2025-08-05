<?php

declare(strict_types=1);

namespace App\Domain\Employee\Event;

use App\Domain\Employee\ValueObject\EmployeeId;
use DateTimeImmutable;
use Symfony\Contracts\EventDispatcher\Event;

final class EmployeeCreated extends Event
{
    public const NAME = 'employee.created';

    public function __construct(
        public readonly EmployeeId $employeeId,
        public readonly DateTimeImmutable $occurredOn
    ) {
    }
}

