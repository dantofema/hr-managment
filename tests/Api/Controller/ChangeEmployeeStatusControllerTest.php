<?php

declare(strict_types=1);

namespace App\Tests\Api\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ChangeEmployeeStatusControllerTest extends WebTestCase
{
    public function testChangeEmployeeStatusSuccess(): void
    {
        $client = static::createClient();
        
        // First create an employee with unique email
        $uniqueEmail = 'status_' . uniqid() . '@example.com';
        $employeeData = [
            'name' => 'Status Test',
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

        // Now change the employee status
        $statusData = [
            'status' => 'INACTIVE'
        ];

        $client->request(
            'PATCH',
            '/api/v1/employees/' . $employeeId . '/status',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($statusData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Employee status changed successfully', $responseData['message']);
    }

    public function testChangeEmployeeStatusWithInvalidStatus(): void
    {
        $client = static::createClient();
        
        $employeeId = '550e8400-e29b-41d4-a716-446655440000';
        
        $statusData = [
            'status' => 'INVALID_STATUS'
        ];

        $client->request(
            'PATCH',
            '/api/v1/employees/' . $employeeId . '/status',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($statusData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('error', $responseData);
    }

    public function testChangeEmployeeStatusNotFound(): void
    {
        $client = static::createClient();
        
        $nonExistentId = '550e8400-e29b-41d4-a716-446655440000';
        
        $statusData = [
            'status' => 'INACTIVE'
        ];

        $client->request(
            'PATCH',
            '/api/v1/employees/' . $nonExistentId . '/status',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($statusData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('error', $responseData);
    }
}