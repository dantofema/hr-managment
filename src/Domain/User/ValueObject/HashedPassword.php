<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use InvalidArgumentException;

final readonly class HashedPassword
{
    public function __construct(private string $value)
    {
        if (empty($value) || !$this->isValidHashFormat($value)) {
            throw new InvalidArgumentException('Invalid hashed password format');
        }
    }

    private function isValidHashFormat(string $hash): bool
    {
        // Check if it's a valid password hash format
        // PHP password hashes typically start with $2y$ (bcrypt), $argon2i$, $argon2id$, etc.
        return password_get_info($hash)['algo'] !== null;
    }

    public static function fromPlainPassword(string $plainPassword): self
    {
        if (strlen($plainPassword) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long');
        }

        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
        
        if ($hashedPassword === false) {
            throw new InvalidArgumentException('Failed to hash password');
        }

        return new self($hashedPassword);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function verify(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}