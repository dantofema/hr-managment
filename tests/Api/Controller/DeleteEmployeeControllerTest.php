<?php

declare(strict_types=1);

namespace App\Tests\Api\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DeleteEmployeeControllerTest extends WebTestCase
{
    public function testDeleteEmployeeSuccess(): void
    {
        $client = static::createClient();
        
        // First create an employee to delete
        $uniqueEmail = 'delete_' . uniqid() . '@example.com';
        $employeeData = [
            'name' => 'To Delete',
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

        // Now delete the employee
        $client->request('DELETE', '/api/v1/employees/' . $employeeId);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Employee deleted successfully', $responseData['message']);
    }

    public function testDeleteEmployeeNotFound(): void
    {
        $client = static::createClient();
        
        $nonExistentId = '550e8400-e29b-41d4-a716-446655440000';
        
        $client->request('DELETE', '/api/v1/employees/' . $nonExistentId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('error', $responseData);
    }
}