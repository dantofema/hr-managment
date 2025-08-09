<?php

declare(strict_types=1);

namespace App\Application\User\Service;

use App\Application\User\Exception\InvalidCredentialsException;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthenticationService
{
    private UserRepositoryInterface $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function authenticate(string $email, string $plainPassword): User
    {
        $user = $this->findUserByEmail($email);
        
        if (!$user) {
            throw InvalidCredentialsException::forEmail($email);
        }

        if (!$this->isPasswordValid($user, $plainPassword)) {
            throw InvalidCredentialsException::invalidPassword();
        }

        return $user;
    }

    public function isPasswordValid(User $user, string $plainPassword): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $plainPassword);
    }

    private function findUserByEmail(string $email): ?User
    {
        try {
            $emailVO = new Email($email);
            return $this->userRepository->findByEmail($emailVO);
        } catch (\InvalidArgumentException $e) {
            // Invalid email format
            return null;
        }
    }

    public function verifyUserExists(string $email): bool
    {
        return $this->findUserByEmail($email) !== null;
    }
}