<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use Exception;

class InvalidEmailException extends Exception
{
    public static function withValue(string $email): self
    {
        return new self(sprintf('Invalid email format: "%s"', $email));
    }
}