<?php

declare(strict_types=1);

namespace App\Tests\Api\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateEmployeeControllerTest extends WebTestCase
{
    public function testCreateEmployeeSuccess(): void
    {
        $client = static::createClient();
        
        $uniqueEmail = 'john.doe_' . uniqid() . '@example.com';
        $employeeData = [
            'name' => 'John Doe',
            'email' => $uniqueEmail,
            'role' => 'Developer',
            'department' => 'Engineering'
        ];

        $client->request(
            'POST',
            '/api/v1/employees',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($employeeData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('employee_id', $responseData);
        $this->assertEquals('Employee created successfully', $responseData['message']);
    }

    public function testCreateEmployeeWithMissingRequiredField(): void
    {
        $client = static::createClient();
        
        $uniqueEmail = 'john.doe_missing_' . uniqid() . '@example.com';
        $employeeData = [
            'name' => 'John Doe',
            'email' => $uniqueEmail,
            'role' => 'Developer'
            // Missing department
        ];

        $client->request(
            'POST',
            '/api/v1/employees',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($employeeData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('error', $responseData);
        $this->assertStringContainsString('department', $responseData['error']);
    }

    public function testCreateEmployeeWithInvalidEmail(): void
    {
        $client = static::createClient();
        
        $employeeData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'role' => 'Developer',
            'department' => 'Engineering'
        ];

        $client->request(
            'POST',
            '/api/v1/employees',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($employeeData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Invalid email format', $responseData['error']);
    }
}