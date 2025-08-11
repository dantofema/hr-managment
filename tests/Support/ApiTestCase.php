<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Domain\User\User;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use App\Infrastructure\Doctrine\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiTestCase extends DatabaseTestCase
{
    protected KernelBrowser $client;
    protected ?User $testUser = null;
    protected ?string $jwtToken = null;

    protected function setUp(): void
    {
        parent::setUp();
        // Reuse the client created by DatabaseTestCase or create if not exists
        $this->client = static::getClient() ?? static::createClient();
    }

    protected function createAuthenticatedUser(string $email = 'auth-user@example.com', array $roles = ['ROLE_USER']): User
    {
        $userRepository = $this->container->get(UserRepository::class);
        
        $user = User::create(
            new Email($email),
            HashedPassword::fromPlainPassword('password123'),
            $roles
        );
        
        // Commit the user creation outside of transaction to ensure it's available for JWT validation
        if ($this->connection->isTransactionActive()) {
            $this->connection->commit();
        }
        
        $userRepository->save($user);
        $this->testUser = $user;
        
        // Start a new transaction for test data isolation
        $this->connection->beginTransaction();
        
        return $user;
    }

    protected function getJwtToken(?User $user = null): string
    {
        if ($this->jwtToken && !$user) {
            return $this->jwtToken;
        }
        
        $user = $user ?? $this->testUser ?? $this->createAuthenticatedUser();
        
        $jwtManager = $this->container->get(JWTTokenManagerInterface::class);
        $this->jwtToken = $jwtManager->create($user);
        
        return $this->jwtToken;
    }

    protected function getAuthHeaders(?User $user = null): array
    {
        $token = $this->getJwtToken($user);
        return ['HTTP_AUTHORIZATION' => 'Bearer ' . $token];
    }

    protected function makeApiRequest(
        string $method,
        string $url,
        array $data = [],
        array $headers = []
    ): Response {
        $defaultHeaders = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/ld+json',
        ];

        $headers = array_merge($defaultHeaders, $headers);

        $this->client->request(
            $method,
            $url,
            [],
            [],
            $headers,
            empty($data) ? null : json_encode($data)
        );

        return $this->client->getResponse();
    }

    protected function assertApiResponse(Response $response, int $expectedStatusCode): void
    {
        $this->assertEquals(
            $expectedStatusCode,
            $response->getStatusCode(),
            sprintf(
                'Expected status code %d, got %d. Response content: %s',
                $expectedStatusCode,
                $response->getStatusCode(),
                $response->getContent()
            )
        );
    }

    protected function assertJsonResponse(Response $response): array
    {
        $contentType = $response->headers->get('content-type');
        $this->assertTrue(
            str_contains($contentType, 'application/json') || 
            str_contains($contentType, 'application/ld+json') ||
            str_contains($contentType, 'application/problem+json'),
            'Response is not JSON. Content-Type: ' . $contentType
        );

        $content = $response->getContent();
        $this->assertJson($content, 'Response content is not valid JSON');

        return json_decode($content, true);
    }

    protected function assertApiSuccessResponse(Response $response, int $expectedStatusCode = 200): array
    {
        $this->assertApiResponse($response, $expectedStatusCode);
        return $this->assertJsonResponse($response);
    }

    protected function assertApiErrorResponse(Response $response, int $expectedStatusCode = 400): array
    {
        $this->assertApiResponse($response, $expectedStatusCode);
        return $this->assertJsonResponse($response);
    }

    protected function postJson(string $url, array $data = []): Response
    {
        return $this->makeApiRequest('POST', $url, $data);
    }

    protected function getJson(string $url): Response
    {
        return $this->makeApiRequest('GET', $url);
    }

    protected function putJson(string $url, array $data = []): Response
    {
        return $this->makeApiRequest('PUT', $url, $data);
    }

    protected function deleteJson(string $url): Response
    {
        return $this->makeApiRequest('DELETE', $url);
    }

    protected function postJsonAuthenticated(string $url, array $data = [], ?User $user = null): Response
    {
        return $this->makeApiRequest('POST', $url, $data, $this->getAuthHeaders($user));
    }

    protected function getJsonAuthenticated(string $url, ?User $user = null): Response
    {
        return $this->makeApiRequest('GET', $url, [], $this->getAuthHeaders($user));
    }

    protected function putJsonAuthenticated(string $url, array $data = [], ?User $user = null): Response
    {
        return $this->makeApiRequest('PUT', $url, $data, $this->getAuthHeaders($user));
    }

    protected function deleteJsonAuthenticated(string $url, ?User $user = null): Response
    {
        return $this->makeApiRequest('DELETE', $url, [], $this->getAuthHeaders($user));
    }

    protected function assertJsonContains(array $expected): void
    {
        $response = $this->client->getResponse();
        $contentType = $response->headers->get('content-type');
        $this->assertTrue(
            str_contains($contentType, 'application/json') || 
            str_contains($contentType, 'application/ld+json') ||
            str_contains($contentType, 'application/problem+json'),
            'Response is not JSON. Content-Type: ' . $contentType
        );

        $content = $response->getContent();
        $this->assertJson($content, 'Response content is not valid JSON');
        
        $responseData = json_decode($content, true);
        
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $responseData, "Key '{$key}' not found in response");
            $this->assertEquals($value, $responseData[$key], "Value for key '{$key}' does not match");
        }
    }
}