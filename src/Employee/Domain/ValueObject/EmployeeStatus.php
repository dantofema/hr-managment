<?php

declare(strict_types=1);

namespace App\Employee\Domain\ValueObject;

enum EmployeeStatus: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
    case VACATION = 'VACATION';
}

