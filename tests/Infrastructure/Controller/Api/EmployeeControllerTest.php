<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Controller\Api;

use App\Tests\Support\ApiTestCase;

class EmployeeControllerTest extends ApiTestCase
{
    public function testPostEmployeesSuccess(): void
    {
        $employeeData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'position' => 'Software Developer',
            'salaryAmount' => 50000,
            'salaryCurrency' => 'USD',
            'hiredAt' => '2024-01-15'
        ];

        $response = $this->postJsonAuthenticated('/api/employees', $employeeData);

        $this->assertApiResponse($response, 201);
        $data = $this->assertJsonResponse($response);

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('fullName', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('position', $data);
        $this->assertArrayHasKey('salaryAmount', $data);
        $this->assertArrayHasKey('salaryCurrency', $data);
        $this->assertArrayHasKey('hiredAt', $data);

        $this->assertNotEmpty($data['id']);
        $this->assertEquals('John Doe', $data['fullName']);
        $this->assertEquals('john.doe@example.com', $data['email']);
        $this->assertEquals('Software Developer', $data['position']);
        $this->assertEquals(50000, $data['salaryAmount']);
        $this->assertEquals('USD', $data['salaryCurrency']);
        $this->assertEquals('2024-01-15', $data['hiredAt']);
    }

    public function testPostEmployeesValidationErrors(): void
    {
        // Test missing required fields
        $incompleteData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            // Missing email, position, salaryAmount, salaryCurrency, hiredAt
        ];

        $response = $this->postJsonAuthenticated('/api/employees', $incompleteData);

        $this->assertApiResponse($response, 400);
        $data = $this->assertJsonResponse($response);

        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Missing required fields', $data['error']);
    }

    public function testPostEmployeesEmptyData(): void
    {
        $response = $this->postJsonAuthenticated('/api/employees', []);

        $this->assertApiResponse($response, 400);
        $data = $this->assertJsonResponse($response);

        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Missing required fields', $data['error']);
    }

    public function testPostEmployeesInvalidJson(): void
    {
        $headers = array_merge(
            ['CONTENT_TYPE' => 'application/json'],
            $this->getAuthHeaders()
        );
        
        $this->client->request(
            'POST',
            '/api/employees',
            [],
            [],
            $headers,
            'invalid json'
        );

        $response = $this->client->getResponse();

        $this->assertApiResponse($response, 400);
        $data = $this->assertJsonResponse($response);

        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Missing required fields', $data['error']);
    }

    public function testPostEmployeesInvalidDate(): void
    {
        $employeeData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'position' => 'Software Developer',
            'salaryAmount' => 50000,
            'salaryCurrency' => 'USD',
            'hiredAt' => 'invalid-date'
        ];

        $response = $this->postJsonAuthenticated('/api/employees', $employeeData);

        $this->assertApiResponse($response, 400);
        $data = $this->assertJsonResponse($response);

        $this->assertArrayHasKey('error', $data);
    }

    public function testPostEmployeesWithDifferentCurrencies(): void
    {
        $currencies = ['USD', 'EUR', 'GBP', 'CAD'];

        foreach ($currencies as $currency) {
            $employeeData = [
                'firstName' => 'Employee',
                'lastName' => $currency,
                'email' => "employee.{$currency}@example.com",
                'position' => 'Developer',
                'salaryAmount' => 55000,
                'salaryCurrency' => $currency,
                'hiredAt' => '2024-02-01'
            ];

            $response = $this->postJsonAuthenticated('/api/employees', $employeeData);

            $this->assertApiResponse($response, 201);
            $data = $this->assertJsonResponse($response);

            $this->assertEquals($currency, $data['salaryCurrency']);
            $this->assertEquals("Employee {$currency}", $data['fullName']);
        }
    }

    public function testGetEmployeeByIdSuccess(): void
    {
        // First create an employee
        $employeeData = [
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane.smith@example.com',
            'position' => 'Designer',
            'salaryAmount' => 60000,
            'salaryCurrency' => 'USD',
            'hiredAt' => '2023-06-01'
        ];

        $createResponse = $this->postJsonAuthenticated('/api/employees', $employeeData);
        $this->assertApiResponse($createResponse, 201);
        $createdEmployee = $this->assertJsonResponse($createResponse);

        // Then retrieve the employee
        $response = $this->getJsonAuthenticated("/api/employees/{$createdEmployee['id']}");

        $this->assertApiResponse($response, 200);
        $data = $this->assertJsonResponse($response);

        $this->assertEquals($createdEmployee['id'], $data['id']);
        $this->assertEquals('Jane Smith', $data['fullName']);
        $this->assertEquals('jane.smith@example.com', $data['email']);
        $this->assertEquals('Designer', $data['position']);
        $this->assertEquals(60000, $data['salaryAmount']);
        $this->assertEquals('USD', $data['salaryCurrency']);
        $this->assertEquals('2023-06-01', $data['hiredAt']);

        // Check business logic fields are present
        $this->assertArrayHasKey('yearsOfService', $data);
        $this->assertArrayHasKey('annualVacationDays', $data);
        $this->assertArrayHasKey('vacationEligible', $data);

        $this->assertIsInt($data['yearsOfService']);
        $this->assertIsInt($data['annualVacationDays']);
        $this->assertIsBool($data['vacationEligible']);
    }

    public function testGetEmployeeByIdNotFound(): void
    {
        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $response = $this->getJsonAuthenticated("/api/employees/{$nonExistentId}");

        $this->assertApiResponse($response, 404);
        $data = $this->assertJsonResponse($response);

        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Employee not found', $data['error']);
    }

    public function testGetEmployeeByIdInvalidUuid(): void
    {
        $invalidId = 'invalid-uuid';

        $response = $this->getJsonAuthenticated("/api/employees/{$invalidId}");

        $this->assertApiResponse($response, 500);
        $data = $this->assertJsonResponse($response);

        $this->assertArrayHasKey('error', $data);
    }

    public function testGetEmployeesCollectionSuccess(): void
    {
        // Create multiple employees
        $employees = [
            [
                'firstName' => 'Alice',
                'lastName' => 'Johnson',
                'email' => 'alice.johnson@example.com',
                'position' => 'Senior Developer',
                'salaryAmount' => 75000,
                'salaryCurrency' => 'USD',
                'hiredAt' => '2022-01-15'
            ],
            [
                'firstName' => 'Bob',
                'lastName' => 'Wilson',
                'email' => 'bob.wilson@example.com',
                'position' => 'Manager',
                'salaryAmount' => 80000,
                'salaryCurrency' => 'USD',
                'hiredAt' => '2021-03-10'
            ]
        ];

        $createdEmployees = [];
        foreach ($employees as $employeeData) {
            $response = $this->postJsonAuthenticated('/api/employees', $employeeData);
            $this->assertApiResponse($response, 201);
            $createdEmployees[] = $this->assertJsonResponse($response);
        }

        // Test that we can retrieve individual employees
        foreach ($createdEmployees as $employee) {
            $response = $this->getJsonAuthenticated("/api/employees/{$employee['id']}");
            $this->assertApiResponse($response, 200);
            $data = $this->assertJsonResponse($response);
            $this->assertEquals($employee['id'], $data['id']);
        }
    }

    public function testEmployeeBusinessLogicCalculations(): void
    {
        // Create an employee hired 2 years ago
        $employeeData = [
            'firstName' => 'Veteran',
            'lastName' => 'Employee',
            'email' => 'veteran@example.com',
            'position' => 'Senior Manager',
            'salaryAmount' => 90000,
            'salaryCurrency' => 'USD',
            'hiredAt' => (new \DateTimeImmutable('-2 years'))->format('Y-m-d')
        ];

        $createResponse = $this->postJsonAuthenticated('/api/employees', $employeeData);
        $this->assertApiResponse($createResponse, 201);
        $createdEmployee = $this->assertJsonResponse($createResponse);

        // Retrieve and verify business calculations
        $response = $this->getJsonAuthenticated("/api/employees/{$createdEmployee['id']}");
        $this->assertApiResponse($response, 200);
        $data = $this->assertJsonResponse($response);

        // Employee hired 2 years ago should have:
        $this->assertEquals(2, $data['yearsOfService']);
        $this->assertEquals(15, $data['annualVacationDays']); // Base 15 days for 0-4 years
        $this->assertTrue($data['vacationEligible']); // Eligible after 3 months
    }

    public function testEmployeeCreationAndRetrievalWorkflow(): void
    {
        // Complete workflow test
        $employeeData = [
            'firstName' => 'Workflow',
            'lastName' => 'Test',
            'email' => 'workflow.test@example.com',
            'position' => 'QA Engineer',
            'salaryAmount' => 65000,
            'salaryCurrency' => 'EUR',
            'hiredAt' => '2023-09-15'
        ];

        // Step 1: Create employee
        $createResponse = $this->postJsonAuthenticated('/api/employees', $employeeData);
        $this->assertApiResponse($createResponse, 201);
        $createdEmployee = $this->assertJsonResponse($createResponse);

        // Verify creation response
        $this->assertNotEmpty($createdEmployee['id']);
        $this->assertEquals('Workflow Test', $createdEmployee['fullName']);

        // Step 2: Retrieve employee
        $getResponse = $this->getJsonAuthenticated("/api/employees/{$createdEmployee['id']}");
        $this->assertApiResponse($getResponse, 200);
        $retrievedEmployee = $this->assertJsonResponse($getResponse);

        // Verify retrieved data matches created data
        $this->assertEquals($createdEmployee['id'], $retrievedEmployee['id']);
        $this->assertEquals($createdEmployee['fullName'], $retrievedEmployee['fullName']);
        $this->assertEquals($createdEmployee['email'], $retrievedEmployee['email']);
        $this->assertEquals($createdEmployee['position'], $retrievedEmployee['position']);
        $this->assertEquals($createdEmployee['salaryAmount'], $retrievedEmployee['salaryAmount']);
        $this->assertEquals($createdEmployee['salaryCurrency'], $retrievedEmployee['salaryCurrency']);
        $this->assertEquals($createdEmployee['hiredAt'], $retrievedEmployee['hiredAt']);

        // Verify business logic fields are present in GET but not in POST
        $this->assertArrayNotHasKey('yearsOfService', $createdEmployee);
        $this->assertArrayHasKey('yearsOfService', $retrievedEmployee);
        $this->assertArrayNotHasKey('annualVacationDays', $createdEmployee);
        $this->assertArrayHasKey('annualVacationDays', $retrievedEmployee);
        $this->assertArrayNotHasKey('vacationEligible', $createdEmployee);
        $this->assertArrayHasKey('vacationEligible', $retrievedEmployee);
    }

    public function testResponseHeaders(): void
    {
        $employeeData = [
            'firstName' => 'Header',
            'lastName' => 'Test',
            'email' => 'header.test@example.com',
            'position' => 'Developer',
            'salaryAmount' => 50000,
            'salaryCurrency' => 'USD',
            'hiredAt' => '2024-01-01'
        ];

        $response = $this->postJsonAuthenticated('/api/employees', $employeeData);

        $this->assertApiResponse($response, 201);
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
    }
}