<?php

declare(strict_types=1);

namespace App\Application\User\DTO;

use App\Domain\User\User;
use DateTimeImmutable;

class LoginResponse
{
    private string $token;
    private string $tokenType;
    private DateTimeImmutable $expiresAt;
    private array $user;

    public function __construct(
        string $token,
        DateTimeImmutable $expiresAt,
        User $user,
        string $tokenType = 'Bearer'
    ) {
        $this->token = $token;
        $this->tokenType = $tokenType;
        $this->expiresAt = $expiresAt;
        $this->user = $user->toArray();
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getUser(): array
    {
        return $this->user;
    }

    public function getExpiresIn(): int
    {
        $now = new DateTimeImmutable();
        return $this->expiresAt->getTimestamp() - $now->getTimestamp();
    }

    public function toArray(): array
    {
        return [
            'access_token' => $this->token,
            'token_type' => $this->tokenType,
            'expires_in' => $this->getExpiresIn(),
            'expires_at' => $this->expiresAt->format('Y-m-d H:i:s'),
            'user' => $this->user,
        ];
    }
}