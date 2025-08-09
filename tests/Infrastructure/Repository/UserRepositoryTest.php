<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Repository;

use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use App\Domain\Shared\ValueObject\Uuid;
use App\Infrastructure\Doctrine\Repository\UserRepository;
use App\Tests\Support\DatabaseTestCase;

class UserRepositoryTest extends DatabaseTestCase
{
    private UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->container->get(UserRepository::class);
    }

    public function testSaveAndFindById(): void
    {
        // Arrange
        $user = User::create(
            new Email('test@example.com'),
            HashedPassword::fromPlainPassword('password123'),
            'Test User'
        );

        // Act
        $this->userRepository->save($user);
        $foundUser = $this->userRepository->findById($user->getId());

        // Assert
        $this->assertNotNull($foundUser);
        $this->assertEquals($user->getId()->toString(), $foundUser->getId()->toString());
        $this->assertEquals('test@example.com', $foundUser->getEmail()->value());
        $this->assertEquals('Test User', $foundUser->getName());
        $this->assertTrue($foundUser->isActive());
    }

    public function testFindByEmail(): void
    {
        // Arrange
        $email = new Email('user@example.com');
        $user = User::create(
            $email,
            HashedPassword::fromPlainPassword('password123'),
            'User Name'
        );

        // Act
        $this->userRepository->save($user);
        $foundUser = $this->userRepository->findByEmail($email);

        // Assert
        $this->assertNotNull($foundUser);
        $this->assertEquals($user->getId()->toString(), $foundUser->getId()->toString());
        $this->assertEquals('user@example.com', $foundUser->getEmail()->value());
    }

    public function testFindActiveByEmail(): void
    {
        // Arrange
        $email = new Email('active@example.com');
        $user = User::create(
            $email,
            HashedPassword::fromPlainPassword('password123'),
            'Active User'
        );

        // Act
        $this->userRepository->save($user);
        $foundUser = $this->userRepository->findActiveByEmail($email);

        // Assert
        $this->assertNotNull($foundUser);
        $this->assertTrue($foundUser->isActive());
        $this->assertEquals('active@example.com', $foundUser->getEmail()->value());
    }

    public function testFindActiveByEmailWithInactiveUser(): void
    {
        // Arrange
        $email = new Email('inactive@example.com');
        $user = User::create(
            $email,
            HashedPassword::fromPlainPassword('password123'),
            'Inactive User'
        );
        $user->deactivate();

        // Act
        $this->userRepository->save($user);
        $foundUser = $this->userRepository->findActiveByEmail($email);

        // Assert
        $this->assertNull($foundUser);
    }

    public function testFindAll(): void
    {
        // Arrange
        $user1 = User::create(
            new Email('user1@example.com'),
            HashedPassword::fromPlainPassword('password123'),
            'User One'
        );
        $user2 = User::create(
            new Email('user2@example.com'),
            HashedPassword::fromPlainPassword('password123'),
            'User Two'
        );

        // Act
        $this->userRepository->save($user1);
        $this->userRepository->save($user2);
        $users = $this->userRepository->findAll();

        // Assert
        $this->assertCount(2, $users);
        $this->assertContainsOnlyInstancesOf(User::class, $users);
    }

    public function testDelete(): void
    {
        // Arrange
        $user = User::create(
            new Email('delete@example.com'),
            HashedPassword::fromPlainPassword('password123'),
            'Delete User'
        );

        // Act
        $this->userRepository->save($user);
        $this->assertNotNull($this->userRepository->findById($user->getId()));
        
        $this->userRepository->delete($user);
        $foundUser = $this->userRepository->findById($user->getId());

        // Assert
        $this->assertNull($foundUser);
    }

    public function testNextIdentity(): void
    {
        // Act
        $uuid1 = $this->userRepository->nextIdentity();
        $uuid2 = $this->userRepository->nextIdentity();

        // Assert
        $this->assertInstanceOf(Uuid::class, $uuid1);
        $this->assertInstanceOf(Uuid::class, $uuid2);
        $this->assertNotEquals($uuid1->toString(), $uuid2->toString());
    }

    public function testFindByIdReturnsNullForNonExistentUser(): void
    {
        // Arrange
        $nonExistentId = Uuid::generate();

        // Act
        $foundUser = $this->userRepository->findById($nonExistentId);

        // Assert
        $this->assertNull($foundUser);
    }

    public function testFindByEmailReturnsNullForNonExistentEmail(): void
    {
        // Arrange
        $nonExistentEmail = new Email('nonexistent@example.com');

        // Act
        $foundUser = $this->userRepository->findByEmail($nonExistentEmail);

        // Assert
        $this->assertNull($foundUser);
    }
}