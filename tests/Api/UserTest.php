<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Domain\User\User;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use App\Infrastructure\Doctrine\Entity\User as UserEntity;
use Doctrine\ORM\EntityManagerInterface;

class UserTest extends ApiTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        
        // Clean up any existing test data
        $this->entityManager->createQuery('DELETE FROM App\Infrastructure\Doctrine\Entity\User')->execute();
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->entityManager->createQuery('DELETE FROM App\Infrastructure\Doctrine\Entity\User')->execute();
        parent::tearDown();
    }

    public function testGetUsersCollection(): void
    {
        // Arrange
        $this->createTestUser('user1@example.com', 'User One');
        $this->createTestUser('user2@example.com', 'User Two');

        // Act
        $response = static::createClient()->request('GET', '/api/users');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/User',
            '@id' => '/api/users',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 2,
        ]);

        $data = $response->toArray();
        $this->assertCount(2, $data['hydra:member']);
        
        // Verify password is not exposed
        foreach ($data['hydra:member'] as $user) {
            $this->assertArrayNotHasKey('password', $user);
            $this->assertArrayHasKey('email', $user);
            $this->assertArrayHasKey('name', $user);
            $this->assertArrayHasKey('isActive', $user);
        }
    }

    public function testGetUser(): void
    {
        // Arrange
        $userEntity = $this->createTestUser('test@example.com', 'Test User');

        // Act
        $response = static::createClient()->request('GET', '/api/users/' . $userEntity->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/User',
            '@type' => 'User',
            'email' => 'test@example.com',
            'name' => 'Test User',
            'isActive' => true,
        ]);

        $data = $response->toArray();
        
        // Verify password is not exposed
        $this->assertArrayNotHasKey('password', $data);
        
        // Verify additional fields are present in item view
        $this->assertArrayHasKey('lastLoginAt', $data);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('updatedAt', $data);
    }

    public function testGetNonExistentUser(): void
    {
        // Act
        static::createClient()->request('GET', '/api/users/non-existent-id');

        // Assert
        $this->assertResponseStatusCodeSame(404);
    }

    public function testPostUserIsNotAllowed(): void
    {
        // Act
        static::createClient()->request('POST', '/api/users', [
            'json' => [
                'email' => 'new@example.com',
                'name' => 'New User',
            ]
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(405); // Method Not Allowed
    }

    public function testPutUserIsNotAllowed(): void
    {
        // Arrange
        $userEntity = $this->createTestUser('test@example.com', 'Test User');

        // Act
        static::createClient()->request('PUT', '/api/users/' . $userEntity->getId(), [
            'json' => [
                'name' => 'Updated Name',
            ]
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(405); // Method Not Allowed
    }

    public function testDeleteUserIsNotAllowed(): void
    {
        // Arrange
        $userEntity = $this->createTestUser('test@example.com', 'Test User');

        // Act
        static::createClient()->request('DELETE', '/api/users/' . $userEntity->getId());

        // Assert
        $this->assertResponseStatusCodeSame(405); // Method Not Allowed
    }

    public function testUsersCollectionPagination(): void
    {
        // Arrange - Create more users than the pagination limit
        for ($i = 1; $i <= 25; $i++) {
            $this->createTestUser("user{$i}@example.com", "User {$i}");
        }

        // Act
        $response = static::createClient()->request('GET', '/api/users');

        // Assert
        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        
        $this->assertEquals(25, $data['hydra:totalItems']);
        $this->assertCount(20, $data['hydra:member']); // Default pagination limit
        $this->assertArrayHasKey('hydra:view', $data);
    }

    private function createTestUser(string $email, string $name): UserEntity
    {
        $domainUser = User::create(
            new Email($email),
            HashedPassword::fromPlainPassword('password123'),
            $name
        );

        $userEntity = UserEntity::fromDomain($domainUser);
        
        $this->entityManager->persist($userEntity);
        $this->entityManager->flush();

        return $userEntity;
    }
}