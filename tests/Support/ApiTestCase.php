<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiTestCase extends DatabaseTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    protected function makeApiRequest(
        string $method,
        string $url,
        array $data = [],
        array $headers = []
    ): Response {
        $defaultHeaders = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
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
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            'Response is not JSON'
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
}