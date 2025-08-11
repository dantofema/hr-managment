<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Tests\Support\ApiTestCase;

class EmployeePaginationTest extends ApiTestCase
{
    /**
     * Test employees collection pagination
     */
    public function testEmployeesCollectionPagination(): void
    {
        // Arrange - Create authenticated user first
        $user = $this->createAuthenticatedUser('pagination_test@example.com');
        
        // Arrange - Create more employees than the pagination limit
        $timestamp = time();
        for ($i = 1; $i <= 25; $i++) {
            $this->createTestEmployee(
                "employee{$i}_{$timestamp}@example.com",
                "Employee",
                "Number{$i}",
                "Position {$i}",
                50000.00 + ($i * 1000),
                'USD',
                $user
            );
        }

        // Act
        $response = $this->getJsonAuthenticated('/api/employees', $user);

        // Assert
        $this->assertApiResponse($response, 200);
        $data = json_decode($response->getContent(), true);
        
        // Check if using hydra format or simple format
        if (isset($data['hydra:totalItems'])) {
            $this->assertEquals(25, $data['hydra:totalItems']); // Total employees created in test
            $this->assertCount(20, $data['hydra:member']); // Items per page (pagination limit)
            $this->assertArrayHasKey('hydra:view', $data);
        } else {
            // Use simple format
            $this->assertEquals(25, $data['totalItems']); // Total employees created in test
            $this->assertCount(20, $data['member']); // Items per page (pagination limit)
        }
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
        string $salaryCurrency,
        $user = null
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

        $this->postJsonAuthenticated('/api/employees', $employeeData, $user);
    }
}