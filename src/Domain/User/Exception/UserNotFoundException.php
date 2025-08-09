<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\User\ValueObject\Email;
use Exception;

class UserNotFoundException extends Exception
{
    public static function withId(Uuid $id): self
    {
        return new self(sprintf('User with ID "%s" not found', $id->toString()));
    }

    public static function withEmail(Email $email): self
    {
        return new self(sprintf('User with email "%s" not found', (string) $email));
    }
}