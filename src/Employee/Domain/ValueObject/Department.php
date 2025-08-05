<?php

declare(strict_types=1);

namespace App\Employee\Domain\ValueObject;

enum Department: string
{
    case HR = 'HR';
    case Engineering = 'Engineering';
    case Marketing = 'Marketing';
    case Sales = 'Sales';
}

