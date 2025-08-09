<?php

declare(strict_types=1);

namespace App\Application\User\Exception;

use Exception;

class InvalidCredentialsException extends Exception
{
    public function __construct(string $message = 'Invalid credentials provided')
    {
        parent::__construct($message, 401);
    }

    public static function forEmail(string $email): self
    {
        return new self(sprintf('Invalid credentials for email: %s', $email));
    }

    public static function invalidPassword(): self
    {
        return new self('Invalid password provided');
    }

    public static function userNotFound(): self
    {
        return new self('User not found');
    }
}