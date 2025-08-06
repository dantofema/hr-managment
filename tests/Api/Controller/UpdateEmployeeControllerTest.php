<?php

declare(strict_types=1);

namespace App\Tests\Api\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UpdateEmployeeControllerTest extends WebTestCase
{
    public function testUpdateEmployeeSuccess(): void
    {
        $client = static::createClient();
        
        // First create an employee to update
        $uniqueEmail = 'original_' . uniqid() . '@example.com';
        $employeeData = [
            'name' => 'Original Name',
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

        // Now update the employee
        $updateData = [
            'name' => 'Updated Name',
            'role' => 'Senior Developer'
        ];

        $client->request(
            'PATCH',
            '/api/v1/employees/' . $employeeId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('employee_id', $responseData);
        $this->assertEquals('Employee updated successfully', $responseData['message']);
        $this->assertEquals($employeeId, $responseData['employee_id']);
    }

    public function testUpdateEmployeeWithEmptyData(): void
    {
        $client = static::createClient();
        
        $employeeId = '550e8400-e29b-41d4-a716-446655440000';
        
        $client->request(
            'PATCH',
            '/api/v1/employees/' . $employeeId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('error', $responseData);
        $this->assertStringContainsString('At least one field', $responseData['error']);
    }

    public function testUpdateEmployeeWithInvalidEmail(): void
    {
        $client = static::createClient();
        
        $employeeId = '550e8400-e29b-41d4-a716-446655440000';
        
        $updateData = [
            'email' => 'invalid-email'
        ];

        $client->request(
            'PATCH',
            '/api/v1/employees/' . $employeeId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Invalid email format', $responseData['error']);
    }

    public function testUpdateEmployeeWithInvalidSalary(): void
    {
        $client = static::createClient();
        
        $employeeId = '550e8400-e29b-41d4-a716-446655440000';
        
        $updateData = [
            'salary' => -1000
        ];

        $client->request(
            'PATCH',
            '/api/v1/employees/' . $employeeId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('error', $responseData);
        $this->assertStringContainsString('positive number', $responseData['error']);
    }
}