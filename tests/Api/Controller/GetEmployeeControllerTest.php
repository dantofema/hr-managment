<?php

declare(strict_types=1);

namespace App\Tests\Api\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetEmployeeControllerTest extends WebTestCase
{
    public function testGetEmployeeSuccess(): void
    {
        $client = static::createClient();
        
        // First create an employee to get
        $uniqueEmail = 'jane.doe_' . uniqid() . '@example.com';
        $employeeData = [
            'name' => 'Jane Doe',
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
        
        $createResponse = json_decode($client->getResponse()->getContent(), true);
        $employeeId = $createResponse['employee_id'];

        // Now get the employee
        $client->request('GET', '/api/v1/employees/' . $employeeId);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('role', $responseData);
        $this->assertArrayHasKey('department', $responseData);
        $this->assertEquals($employeeId, $responseData['id']);
        $this->assertEquals('Jane Doe', $responseData['name']);
        $this->assertEquals($uniqueEmail, $responseData['email']);
    }

    public function testGetEmployeeNotFound(): void
    {
        $client = static::createClient();
        
        $nonExistentId = '550e8400-e29b-41d4-a716-446655440000';
        
        $client->request('GET', '/api/v1/employees/' . $nonExistentId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Employee not found', $responseData['error']);
    }

    public function testGetEmployeeWithInvalidId(): void
    {
        $client = static::createClient();
        
        $invalidId = 'invalid-uuid';
        
        $client->request('GET', '/api/v1/employees/' . $invalidId);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('error', $responseData);
    }
}