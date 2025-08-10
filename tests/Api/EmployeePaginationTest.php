<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class EmployeePaginationTest extends ApiTestCase
{
    /**
     * Test employees collection pagination
     */
    public function testEmployeesCollectionPagination(): void
    {
        // Arrange - Create more employees than the pagination limit
        $timestamp = time();
        for ($i = 1; $i <= 25; $i++) {
            $this->createTestEmployee(
                "employee{$i}_{$timestamp}@example.com",
                "Employee",
                "Number{$i}",
                "Position {$i}",
                50000.00 + ($i * 1000),
                'USD'
            );
        }

        // Act
        $response = static::createClient()->request('GET', '/api/employees');

        // Assert
        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        
        // Debug: dump the actual response structure
        var_dump($data);
        
        $this->assertEquals(25, $data['hydra:totalItems']);
        $this->assertCount(20, $data['hydra:member']); // Default pagination limit
        $this->assertArrayHasKey('hydra:view', $data);
    }

    /**
     * Helper method to create a test employee
     */
    private function createTestEmployee(
        string $email,
        string $firstName,
        string $lastName,
        string $position,
        float $salaryAmount,
        string $salaryCurrency
    ): void {
        $employeeData = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'position' => $position,
            'salaryAmount' => $salaryAmount,
            'salaryCurrency' => $salaryCurrency,
            'hiredAt' => '2024-01-15'
        ];

        static::createClient()->request('POST', '/api/employees', [
            'json' => $employeeData
        ]);
    }
}