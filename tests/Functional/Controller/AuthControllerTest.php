<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Infrastructure\Doctrine\Entity\User;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use App\Domain\Shared\ValueObject\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends WebTestCase
{
    private function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }

    public function testLoginInfo(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/login');
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('example', $responseData);
    }

    public function testLoginCheckWithValidCredentials(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        
        // Create a test user
        $testUser = new User();
        $testUserId = uniqid('test-user-1-');
        $testUserEmail = 'test-' . uniqid() . '@example.com';
        $testUser->setId($testUserId)
                 ->setEmail($testUserEmail)
                 ->setPassword(HashedPassword::fromPlainPassword('password123')->value())
                 ->setName('Test User');
        $entityManager->persist($testUser);
        $entityManager->flush();
        
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $testUserEmail,
            'password' => 'password123'
        ]));
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertEquals($testUserEmail, $responseData['user']['email']);
        $this->assertEquals('Test User', $responseData['user']['name']);
    }

    public function testLoginCheckWithInvalidCredentials(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ]));
        
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testLoginCheckWithMissingCredentials(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([]));
        
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testProtectedRouteWithoutToken(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/employees');
        
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testProtectedRouteWithValidToken(): void
    {
        $client = static::createClient();
        $entityManager = $this->getEntityManager();
        
        // Create a test user
        $testUser = new User();
        $testUserId = uniqid('test-user-2-');
        $testUserEmail = 'test2-' . uniqid() . '@example.com';
        $testUser->setId($testUserId)
                 ->setEmail($testUserEmail)
                 ->setPassword(HashedPassword::fromPlainPassword('password123')->value())
                 ->setName('Test User 2');
        $entityManager->persist($testUser);
        $entityManager->flush();
        
        // Login to get token
        $client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $testUserEmail,
            'password' => 'password123'
        ]));
        
        $this->assertResponseIsSuccessful();
        $loginResponse = json_decode($client->getResponse()->getContent(), true);
        $token = $loginResponse['token'];
        
        // Use token to access protected route
        $client->request('GET', '/api/employees', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        
        // Should not be unauthorized (might be 404 if employees endpoint doesn't exist, but not 401)
        $this->assertNotEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up test data - only if kernel is already booted
        if (static::$kernel !== null) {
            $entityManager = $this->getEntityManager();
            $entityManager->createQuery('DELETE FROM App\Infrastructure\Doctrine\Entity\User u WHERE u.email LIKE :email')
                ->setParameter('email', 'test%@example.com')
                ->execute();
            
            $entityManager->close();
        }
    }
}