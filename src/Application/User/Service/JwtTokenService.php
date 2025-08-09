<?php

declare(strict_types=1);

namespace App\Application\User\Service;

use App\Domain\User\User;
use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class JwtTokenService
{
    private JWTTokenManagerInterface $jwtManager;
    private int $tokenTtl;

    public function __construct(
        JWTTokenManagerInterface $jwtManager,
        int $tokenTtl = 3600 // 1 hour default
    ) {
        $this->jwtManager = $jwtManager;
        $this->tokenTtl = $tokenTtl;
    }

    public function generateToken(User $user): string
    {
        return $this->jwtManager->create($user);
    }

    public function getTokenExpirationDate(): DateTimeImmutable
    {
        return (new DateTimeImmutable())->modify(sprintf('+%d seconds', $this->tokenTtl));
    }

    public function validateToken(string $token): bool
    {
        try {
            $payload = $this->jwtManager->parse($token);
            return $payload !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getUserFromToken(string $token): ?array
    {
        try {
            return $this->jwtManager->parse($token);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function isTokenExpired(string $token): bool
    {
        $payload = $this->getUserFromToken($token);
        
        if (!$payload || !isset($payload['exp'])) {
            return true;
        }

        return $payload['exp'] < time();
    }

    public function getTokenTtl(): int
    {
        return $this->tokenTtl;
    }
}