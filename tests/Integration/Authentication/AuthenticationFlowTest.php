<?php

declare(strict_types=1);

namespace App\Tests\Integration\Authentication;

use App\Infrastructure\Doctrine\Entity\User;
use App\Domain\User\User as DomainUser;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use App\Tests\Support\DatabaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationFlowTest extends DatabaseTestCase
{
    private array $testUsers = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clean up any existing test users
        $this->entityManager->createQuery('DELETE FROM App\\Infrastructure\\Doctrine\\Entity\\User u WHERE u.email IN (:emails)')
            ->setParameter('emails', ['valid.user@example.com', 'admin@example.com'])
            ->execute();
        
        $this->createTestUsers();
    }

    private function createTestUsers(): void
    {
        // Create test users for different scenarios
        $domainUser1 = DomainUser::create(
            new Email('valid.user@example.com'),
            HashedPassword::fromPlainPassword('ValidPassword123!')
        );
        $this->testUsers['valid'] = User::fromDomain($domainUser1);

        $domainUser2 = DomainUser::create(
            new Email('admin@example.com'),
            HashedPassword::fromPlainPassword('AdminPassword123!'),
            ['ROLE_USER', 'ROLE_ADMIN']
        );
        $this->testUsers['admin'] = User::fromDomain($domainUser2);

        foreach ($this->testUsers as $user) {
            $this->entityManager->persist($user);
        }
        $this->entityManager->flush();
        
        // Commit transaction so users are visible to HTTP requests
        $this->connection->commit();
        $this->connection->beginTransaction();
    }

    public function testCompleteAuthenticationFlow(): void
    {
        $client = static::getClient();

        // Step 1: Attempt to access protected resource without authentication
        $client->request('GET', '/api/employees');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        // Step 2: Login with valid credentials
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'valid.user@example.com',
            'password' => 'ValidPassword123!'
        ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $loginResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $loginResponse);
        $this->assertArrayHasKey('user', $loginResponse);
        $this->assertEquals('valid.user@example.com', $loginResponse['user']['email']);
        $this->assertEquals('valid.user@example.com', $loginResponse['user']['name']);

        $token = $loginResponse['token'];
        $this->assertNotEmpty($token);

        // Step 3: Access protected resource with valid token
        $client->request('GET', '/api/employees', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        // Should not be unauthorized (might be 404 if endpoint doesn't exist, but not 401)
        $this->assertNotEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());

        // Step 4: Verify token structure (JWT should have 3 parts)
        $tokenParts = explode('.', $token);
        $this->assertCount(3, $tokenParts, 'JWT token should have 3 parts');

        // Step 5: Verify user data in token payload
        $payload = json_decode(base64_decode($tokenParts[1]), true);
        $this->assertArrayHasKey('username', $payload);
        $this->assertEquals('valid.user@example.com', $payload['username']);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::getClient();

        // Test with wrong password
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'valid.user@example.com',
            'password' => 'WrongPassword'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        // Test with non-existent user
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'nonexistent@example.com',
            'password' => 'AnyPassword'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testAccessWithInvalidToken(): void
    {
        $client = static::getClient();

        // Test with malformed token
        $client->request('GET', '/api/employees', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer invalid.token.here',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        // Test with empty token
        $client->request('GET', '/api/employees', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        // Test with wrong authorization format
        $client->request('GET', '/api/employees', [], [], [
            'HTTP_AUTHORIZATION' => 'Basic sometoken',
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testTokenExpiration(): void
    {
        $client = static::getClient();

        // Login to get a token
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'valid.user@example.com',
            'password' => 'ValidPassword123!'
        ]));

        $this->assertResponseIsSuccessful();
        $loginResponse = json_decode($client->getResponse()->getContent(), true);
        $token = $loginResponse['token'];

        // Verify token is currently valid
        $client->request('GET', '/api/employees', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        $this->assertNotEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());

        // Note: In a real scenario, we would test with an expired token
        // For now, we verify the token structure and that it contains expiration info
        $tokenParts = explode('.', $token);
        $payload = json_decode(base64_decode($tokenParts[1]), true);
        $this->assertArrayHasKey('exp', $payload, 'Token should contain expiration time');
        $this->assertGreaterThan(time(), $payload['exp'], 'Token should not be expired yet');
    }

    public function testRoleBasedAccess(): void
    {
        $client = static::getClient();

        // Login as admin user
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'admin@example.com',
            'password' => 'AdminPassword123!'
        ]));

        $this->assertResponseIsSuccessful();
        $loginResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('user', $loginResponse);
        $this->assertContains('ROLE_ADMIN', $loginResponse['user']['roles']);

        $adminToken = $loginResponse['token'];

        // Login as regular user
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'valid.user@example.com',
            'password' => 'ValidPassword123!'
        ]));

        $this->assertResponseIsSuccessful();
        $userLoginResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('user', $userLoginResponse);
        $this->assertNotContains('ROLE_ADMIN', $userLoginResponse['user']['roles']);

        $userToken = $userLoginResponse['token'];

        // Both tokens should be valid for basic access
        foreach ([$adminToken, $userToken] as $token) {
            $client->request('GET', '/api/employees', [], [], [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ]);
            $this->assertNotEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
        }
    }

    public function testConcurrentSessions(): void
    {
        $client1 = static::getClient();
        $client2 = static::getClient();

        // Login with first client
        $client1->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'valid.user@example.com',
            'password' => 'ValidPassword123!'
        ]));
        $this->assertResponseIsSuccessful();
        $response1 = json_decode($client1->getResponse()->getContent(), true);

        // Login with second client
        $client2->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'valid.user@example.com',
            'password' => 'ValidPassword123!'
        ]));
        $this->assertResponseIsSuccessful();
        $response2 = json_decode($client2->getResponse()->getContent(), true);

        $token1 = $response1['token'];
        $token2 = $response2['token'];

        // Tokens should be different (each session gets its own token)
        $this->assertNotEquals($token1, $token2);

        // Both tokens should work for accessing protected resources
        foreach ([[$client1, $token1], [$client2, $token2]] as [$client, $token]) {
            $client->request('GET', '/api/employees', [], [], [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ]);
            $this->assertNotEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
        }
    }

    public function testLoginDataValidation(): void
    {
        $client = static::getClient();

        // Test with missing email
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'password' => 'ValidPassword123!'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        // Test with missing password
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'valid.user@example.com'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        // Test with empty credentials
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => '',
            'password' => ''
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        // Test with invalid JSON
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], 'invalid json');
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testUserLastLoginUpdate(): void
    {
        $client = static::getClient();

        // Get user before login
        $userBefore = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'valid.user@example.com']);
        
        $lastLoginBefore = $userBefore->getLastLoginAt();

        // Login
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'valid.user@example.com',
            'password' => 'ValidPassword123!'
        ]));

        $this->assertResponseIsSuccessful();

        // Refresh entity from database
        $this->entityManager->refresh($userBefore);
        $lastLoginAfter = $userBefore->getLastLoginAt();

        // Last login should be updated
        $this->assertNotEquals($lastLoginBefore, $lastLoginAfter);
        $this->assertGreaterThan($lastLoginBefore ?? new \DateTime('1970-01-01'), $lastLoginAfter);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up test data
        $this->entityManager->createQuery('DELETE FROM App\\Infrastructure\\Doctrine\\Entity\\User u WHERE u.email LIKE :email')
            ->setParameter('email', '%@example.com')
            ->execute();

        $this->entityManager->close();
    }
}