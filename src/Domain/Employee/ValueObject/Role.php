<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObject;

enum Role: string
{
    case Developer = 'Developer';
    case SeniorDeveloper = 'Senior Developer';
    case TechLead = 'Tech Lead';
    case Manager = 'Manager';
    case HRSpecialist = 'HR Specialist';
}

