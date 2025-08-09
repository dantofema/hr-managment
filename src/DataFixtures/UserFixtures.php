<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use App\Domain\User\User;
use App\Infrastructure\Doctrine\Entity\User as DoctrineUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $adminUser = $this->createUser(
            'admin@hr-system.com',
            'password123',
            ['ROLE_ADMIN'],
            true
        );
        $manager->persist($adminUser);

        // Create regular user
        $regularUser = $this->createUser(
            'user@hr-system.com',
            'password123',
            ['ROLE_USER'],
            true
        );
        $manager->persist($regularUser);

        // Create inactive user for testing
        $inactiveUser = $this->createUser(
            'inactive@hr-system.com',
            'password123',
            ['ROLE_USER'],
            false
        );
        $manager->persist($inactiveUser);

        $manager->flush();
    }

    private function createUser(
        string $email,
        string $plainPassword,
        array $roles,
        bool $isActive
    ): DoctrineUser {
        // Create domain user first to get hashed password
        $domainUser = User::create(
            new Email($email),
            new HashedPassword($this->hashPassword($plainPassword)),
            $roles
        );

        // Note: User activation/deactivation logic would be implemented here if needed

        // Convert to Doctrine entity
        return DoctrineUser::fromDomain($domainUser);
    }

    private function hashPassword(string $plainPassword): string
    {
        // Create a temporary Doctrine user for password hashing
        $tempUser = new DoctrineUser();
        $tempUser->setEmail('temp@example.com');

        return $this->passwordHasher->hashPassword($tempUser, $plainPassword);
    }
}