<?php

declare(strict_types=1);

namespace App\Tests\Domain\User;

use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\User\User;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testCanCreateUser(): void
    {
        $id = Uuid::generate();
        $email = new Email('test@example.com');
        $password = HashedPassword::fromPlainPassword('password123');
        $roles = ['ROLE_USER'];

        $user = new User($id, $email, $password, $roles);

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($password, $user->getPassword());
        $this->assertEquals($roles, $user->getRoles());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertNull($user->getUpdatedAt());
    }

    public function testCanCreateUserWithFactory(): void
    {
        $email = new Email('test@example.com');
        $password = HashedPassword::fromPlainPassword('password123');

        $user = User::create($email, $password);

        $this->assertInstanceOf(Uuid::class, $user->getId());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($password, $user->getPassword());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreatedAt());
        $this->assertNull($user->getUpdatedAt());
    }

    public function testCanCreateUserWithCustomRoles(): void
    {
        $email = new Email('admin@example.com');
        $password = HashedPassword::fromPlainPassword('password123');
        $roles = ['ROLE_USER', 'ROLE_ADMIN'];

        $user = User::create($email, $password, $roles);

        $this->assertEquals($roles, $user->getRoles());
    }

    public function testCanUpdateEmail(): void
    {
        $user = User::create(
            new Email('old@example.com'),
            HashedPassword::fromPlainPassword('password123')
        );

        $newEmail = new Email('new@example.com');
        $user->updateEmail($newEmail);

        $this->assertEquals($newEmail, $user->getEmail());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getUpdatedAt());
    }

    public function testCanUpdatePassword(): void
    {
        $user = User::create(
            new Email('test@example.com'),
            HashedPassword::fromPlainPassword('oldpassword')
        );

        $newPassword = HashedPassword::fromPlainPassword('newpassword123');
        $user->updatePassword($newPassword);

        $this->assertEquals($newPassword, $user->getPassword());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getUpdatedAt());
    }

    public function testCanAddRole(): void
    {
        $user = User::create(
            new Email('test@example.com'),
            HashedPassword::fromPlainPassword('password123')
        );

        $user->addRole('ROLE_ADMIN');

        $this->assertTrue($user->hasRole('ROLE_ADMIN'));
        $this->assertTrue($user->hasRole('ROLE_USER'));
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getUpdatedAt());
    }

    public function testCannotAddDuplicateRole(): void
    {
        $user = User::create(
            new Email('test@example.com'),
            HashedPassword::fromPlainPassword('password123')
        );

        $user->addRole('ROLE_USER'); // Already exists

        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testCanRemoveRole(): void
    {
        $user = User::create(
            new Email('test@example.com'),
            HashedPassword::fromPlainPassword('password123'),
            ['ROLE_USER', 'ROLE_ADMIN']
        );

        $user->removeRole('ROLE_ADMIN');

        $this->assertFalse($user->hasRole('ROLE_ADMIN'));
        $this->assertTrue($user->hasRole('ROLE_USER'));
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getUpdatedAt());
    }

    public function testCannotRemoveNonExistentRole(): void
    {
        $user = User::create(
            new Email('test@example.com'),
            HashedPassword::fromPlainPassword('password123')
        );

        $user->removeRole('ROLE_ADMIN'); // Doesn't exist

        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testHasRole(): void
    {
        $user = User::create(
            new Email('test@example.com'),
            HashedPassword::fromPlainPassword('password123'),
            ['ROLE_USER', 'ROLE_ADMIN']
        );

        $this->assertTrue($user->hasRole('ROLE_USER'));
        $this->assertTrue($user->hasRole('ROLE_ADMIN'));
        $this->assertFalse($user->hasRole('ROLE_SUPER_ADMIN'));
    }

    public function testGetUserIdentifier(): void
    {
        $email = new Email('test@example.com');
        $user = User::create($email, HashedPassword::fromPlainPassword('password123'));

        $this->assertEquals('test@example.com', $user->getUserIdentifier());
    }

    public function testEraseCredentials(): void
    {
        $user = User::create(
            new Email('test@example.com'),
            HashedPassword::fromPlainPassword('password123')
        );

        // Should not throw any exception
        $user->eraseCredentials();
        $this->assertTrue(true);
    }

    public function testToArray(): void
    {
        $user = User::create(
            new Email('test@example.com'),
            HashedPassword::fromPlainPassword('password123'),
            ['ROLE_USER', 'ROLE_ADMIN']
        );

        $array = $user->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('email', $array);
        $this->assertArrayHasKey('roles', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
        $this->assertEquals('test@example.com', $array['email']);
        $this->assertEquals(['ROLE_USER', 'ROLE_ADMIN'], $array['roles']);
        $this->assertNull($array['updated_at']);
    }
}