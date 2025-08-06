<?php

declare(strict_types=1);

namespace App\Tests\Api\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListEmployeesControllerTest extends WebTestCase
{
    public function testListEmployeesSuccess(): void
    {
        $client = static::createClient();
        
        // Create a test employee first
        $uniqueEmail = 'test_' . uniqid() . '@example.com';
        $employeeData = [
            'name' => 'Test Employee',
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

        // Now list employees
        $client->request('GET', '/api/v1/employees');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('pagination', $responseData);
        $this->assertIsArray($responseData['data']);
        
        // Check pagination structure
        $pagination = $responseData['pagination'];
        $this->assertArrayHasKey('page', $pagination);
        $this->assertArrayHasKey('limit', $pagination);
        $this->assertArrayHasKey('total', $pagination);
        $this->assertArrayHasKey('pages', $pagination);
    }

    public function testListEmployeesWithPagination(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/v1/employees?page=1&limit=5');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('pagination', $responseData);
        
        $pagination = $responseData['pagination'];
        $this->assertEquals(1, $pagination['page']);
        $this->assertEquals(5, $pagination['limit']);
    }

    public function testListEmployeesWithDepartmentFilter(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/v1/employees?department=Engineering');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('pagination', $responseData);
        
        // If there are employees, they should all be from Engineering department
        if (!empty($responseData['data'])) {
            foreach ($responseData['data'] as $employee) {
                $this->assertEquals('Engineering', $employee['department']);
            }
        }
    }

    public function testListEmployeesWithRoleFilter(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/v1/employees?role=Developer');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('pagination', $responseData);
        
        // If there are employees, they should all have Developer role
        if (!empty($responseData['data'])) {
            foreach ($responseData['data'] as $employee) {
                $this->assertEquals('Developer', $employee['role']);
            }
        }
    }

    public function testListEmployeesEmptyResult(): void
    {
        $client = static::createClient();
        
        // Use a filter that likely returns no results
        $client->request('GET', '/api/v1/employees?department=NonExistentDepartment');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('pagination', $responseData);
        $this->assertIsArray($responseData['data']);
    }
}