<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObject;

use InvalidArgumentException;

final readonly class FullName
{
    public function __construct(
        private string $firstName,
        private string $lastName
    ) {
        if (trim($firstName) === '') {
            throw new InvalidArgumentException('First name cannot be empty');
        }
        
        if (trim($lastName) === '') {
            throw new InvalidArgumentException('Last name cannot be empty');
        }
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function lastName(): string
    {
        return $this->lastName;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function fullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function equals(self $other): bool
    {
        return $this->firstName === $other->firstName 
            && $this->lastName === $other->lastName;
    }

    public function __toString(): string
    {
        return $this->fullName();
    }
}