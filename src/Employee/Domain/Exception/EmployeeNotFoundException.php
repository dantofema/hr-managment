<?php

namespace App\Employee\Domain\Exception;

use RuntimeException;

class EmployeeNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Employee not found')
    {
        parent::__construct($message);
    }
}
