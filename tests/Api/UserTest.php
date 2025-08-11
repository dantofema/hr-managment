<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Tests\Support\ApiTestCase;
use App\Domain\User\User;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use App\Infrastructure\Doctrine\Entity\User as UserEntity;

class UserTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clean up any existing test data
        $this->entityManager->createQuery('DELETE FROM App\\Infrastructure\\Doctrine\\Entity\\User u')->execute();
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->entityManager->createQuery('DELETE FROM App\\Infrastructure\\Doctrine\\Entity\\User u')->execute();
        parent::tearDown();
    }

    public function testGetUsersCollection(): void
    {
        // Arrange
        $this->createTestUser('user1@example.com');
        $this->createTestUser('user2@example.com');

        // Act
        $response = $this->getJsonAuthenticated('/api/users');

        // Assert
        $this->assertApiResponse($response, 200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/User',
            '@id' => '/api/users',
            '@type' => 'Collection',
            'totalItems' => 2,
        ]);

        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data['member']);
        
        // Verify password is not exposed
        foreach ($data['member'] as $user) {
            $this->assertArrayNotHasKey('password', $user);
            $this->assertArrayHasKey('email', $user);
            $this->assertArrayHasKey('name', $user);
            $this->assertArrayHasKey('isActive', $user);
        }
    }

    public function testGetUser(): void
    {
        // Arrange
        $userEntity = $this->createTestUser('test@example.com');

        // Act
        $response = $this->getJsonAuthenticated('/api/users/' . $userEntity->getId());

        // Assert
        $this->assertApiResponse($response, 200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/User',
            '@type' => 'User',
            'email' => 'test@example.com',
            'name' => 'test@example.com',
            'isActive' => true,
        ]);

        $data = json_decode($response->getContent(), true);
        
        // Verify password is not exposed
        $this->assertArrayNotHasKey('password', $data);
        
        // Verify additional fields are present in item view
        $this->assertArrayHasKey('createdAt', $data);
    }

    public function testGetNonExistentUser(): void
    {
        // Act
        $response = $this->getJsonAuthenticated('/api/users/non-existent-id');

        // Assert
        $this->assertApiResponse($response, 404);
    }

    public function testPostUserIsNotAllowed(): void
    {
        // Act
        $this->client->request('POST', '/api/users', [
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
        $userEntity = $this->createTestUser('test@example.com');

        // Act
        $this->client->request('PUT', '/api/users/' . $userEntity->getId(), [
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
        $userEntity = $this->createTestUser('test@example.com');

        // Act
        $this->client->request('DELETE', '/api/users/' . $userEntity->getId());

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
        $response = $this->getJsonAuthenticated('/api/users');

        // Assert
        $this->assertApiResponse($response, 200);
        $data = json_decode($response->getContent(), true);
        
        $this->assertEquals(20, $data['totalItems']);
        $this->assertCount(20, $data['member']); // Default pagination limit
    }

    private function createTestUser(string $email, string $name = null): UserEntity
    {
        $domainUser = User::create(
            new Email($email),
            HashedPassword::fromPlainPassword('password123')
        );

        $userEntity = UserEntity::fromDomain($domainUser);
        if ($name !== null) {
            $userEntity->setName($name);
        }
        
        $this->entityManager->persist($userEntity);
        $this->entityManager->flush();

        return $userEntity;
    }
}