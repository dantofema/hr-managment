<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Domain\Employee\Employee;
use App\Domain\Employee\ValueObject\Email;
use App\Domain\Employee\ValueObject\FullName;
use App\Domain\Employee\ValueObject\Position;
use App\Domain\Employee\ValueObject\Salary;
use App\Infrastructure\Doctrine\Entity\Employee as EmployeeEntity;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Test class for Employee API endpoints.
 * 
 * This class tests the Employee API functionality including:
 * - Creating employees via POST /api/employees
 * - Retrieving employees via GET /api/employees/{id}
 * - Retrieving employees collection via GET /api/employees
 * - Authentication and authorization tests
 * - Data validation tests
 */
class EmployeeApiTest extends ApiTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        
        // Clean up any existing test data
        $this->cleanupEmployees();
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->cleanupEmployees();
        parent::tearDown();
    }

    private function cleanupEmployees(): void
    {
        try {
            // Try to clean up related entities first, but ignore if tables don't exist
            try {
                $this->entityManager->createQuery('DELETE FROM App\Infrastructure\Doctrine\Entity\Vacation v')->execute();
            } catch (\Exception $e) {
                // Ignore if vacation table doesn't exist
            }
            
            try {
                $this->entityManager->createQuery('DELETE FROM App\Infrastructure\Doctrine\Entity\Payroll p')->execute();
            } catch (\Exception $e) {
                // Ignore if payroll table doesn't exist
            }
            
            // Clean up employees
            $this->entityManager->createQuery('DELETE FROM App\Infrastructure\Doctrine\Entity\Employee e')->execute();
            $this->entityManager->flush();
        } catch (\Exception $e) {
            // If all else fails, just clear the entity manager
            $this->entityManager->clear();
        }
    }

    /**
     * Test creating a new employee via POST /api/employees
     */
    public function testCreateEmployee(): void
    {
        // Arrange
        $employeeData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'position' => 'Software Developer',
            'salaryAmount' => 75000.00,
            'salaryCurrency' => 'USD',
            'hiredAt' => '2024-01-15'
        ];

        // Act
        $response = static::createClient()->request('POST', '/api/employees', [
            'json' => $employeeData
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = $response->toArray();
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('fullName', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('position', $data);
        $this->assertArrayHasKey('salary', $data);
        $this->assertArrayHasKey('hiredAt', $data);
        
        // Verify the employee was actually created in the database
        $createdEmployee = $this->entityManager
            ->getRepository(EmployeeEntity::class)
            ->findOneBy(['email' => 'john.doe@example.com']);
        
        $this->assertNotNull($createdEmployee);
        $this->assertEquals('John Doe', $createdEmployee->getFullName());
        $this->assertEquals('john.doe@example.com', $createdEmployee->getEmail());
    }

    /**
     * Test creating employee with missing required fields
     */
    public function testCreateEmployeeWithMissingFields(): void
    {
        // Arrange
        $incompleteData = [
            'firstName' => 'John',
            'lastName' => 'Doe'
            // Missing email, position, salary, etc.
        ];

        // Act
        static::createClient()->request('POST', '/api/employees', [
            'json' => $incompleteData
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(422);
    }

    /**
     * Test creating employee with invalid email format
     */
    public function testCreateEmployeeWithInvalidEmail(): void
    {
        // Arrange
        $employeeData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'invalid-email',
            'position' => 'Software Developer',
            'salaryAmount' => 75000.00,
            'salaryCurrency' => 'USD',
            'hiredAt' => '2024-01-15'
        ];

        // Act
        static::createClient()->request('POST', '/api/employees', [
            'json' => $employeeData
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(422);
    }

    /**
     * Test retrieving a specific employee via GET /api/employees/{id}
     */
    public function testGetEmployee(): void
    {
        // Arrange
        $employeeEntity = $this->createTestEmployee(
            'jane.smith@example.com',
            'Jane',
            'Smith',
            'Product Manager',
            85000.00,
            'USD'
        );

        // Act
        $response = static::createClient()->request('GET', '/api/employees/' . $employeeEntity->getId());

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        
        $data = $response->toArray();
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('fullName', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('position', $data);
        $this->assertArrayHasKey('salaryAmount', $data);
        $this->assertArrayHasKey('salaryCurrency', $data);
        $this->assertArrayHasKey('hiredAt', $data);
        $this->assertArrayHasKey('createdAt', $data);
        
        // Verify specific values
        $this->assertEquals('jane.smith@example.com', $data['email']);
        $this->assertEquals('Jane Smith', $data['fullName']);
        $this->assertEquals('Product Manager', $data['position']);
    }

    /**
     * Test retrieving a non-existent employee
     */
    public function testGetNonExistentEmployee(): void
    {
        // Act
        static::createClient()->request('GET', '/api/employees/non-existent-id');

        // Assert
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Test retrieving employees collection via GET /api/employees
     */
    public function testGetEmployeesCollection(): void
    {
        // Arrange
        $this->createTestEmployee('emp1@example.com', 'Employee', 'One', 'Developer', 70000.00, 'USD');
        $this->createTestEmployee('emp2@example.com', 'Employee', 'Two', 'Designer', 65000.00, 'USD');

        // Act
        $response = static::createClient()->request('GET', '/api/employees');

        // Assert
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        
        $data = $response->toArray();
        $this->assertArrayHasKey('member', $data);
        $this->assertArrayHasKey('totalItems', $data);
        $this->assertEquals(2, $data['totalItems']);
        $this->assertCount(2, $data['member']);
        
        // Verify each employee has required fields
        foreach ($data['member'] as $employee) {
            $this->assertArrayHasKey('id', $employee);
            $this->assertArrayHasKey('fullName', $employee);
            $this->assertArrayHasKey('email', $employee);
            $this->assertArrayHasKey('position', $employee);
        }
    }

    /**
     * Test employees collection pagination
     */
    public function testEmployeesCollectionPagination(): void
    {
        // Arrange - Create more employees than the pagination limit
        for ($i = 1; $i <= 25; $i++) {
            $this->createTestEmployee(
                "employee{$i}@example.com",
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
        
        $this->assertEquals(25, $data['totalItems']);
        $this->assertCount(20, $data['member']); // Default pagination limit
    }

    /**
     * Test creating employee with duplicate email
     */
    public function testCreateEmployeeWithDuplicateEmail(): void
    {
        // Arrange
        $this->createTestEmployee('duplicate@example.com', 'First', 'Employee', 'Developer', 70000.00, 'USD');
        
        $duplicateData = [
            'firstName' => 'Second',
            'lastName' => 'Employee',
            'email' => 'duplicate@example.com',
            'position' => 'Designer',
            'salaryAmount' => 65000.00,
            'salaryCurrency' => 'USD',
            'hiredAt' => '2024-01-15'
        ];

        // Act
        $response = static::createClient()->request('POST', '/api/employees', [
            'json' => $duplicateData
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(400);
        
        // Check if the error message contains duplicate email validation
        $responseData = $response->toArray(false);
        $this->assertStringContainsString('email already exists', $responseData['error'] ?? $responseData['detail'] ?? '');
    }

    /**
     * Test creating employee with invalid salary amount
     */
    public function testCreateEmployeeWithInvalidSalary(): void
    {
        // Arrange
        $employeeData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'position' => 'Software Developer',
            'salaryAmount' => -1000.00, // Invalid negative salary
            'salaryCurrency' => 'USD',
            'hiredAt' => '2024-01-15'
        ];

        // Act
        static::createClient()->request('POST', '/api/employees', [
            'json' => $employeeData
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(422);
    }

    /**
     * Test creating employee with invalid hire date
     */
    public function testCreateEmployeeWithInvalidHireDate(): void
    {
        // Arrange
        $employeeData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'position' => 'Software Developer',
            'salaryAmount' => 75000.00,
            'salaryCurrency' => 'USD',
            'hiredAt' => 'invalid-date'
        ];

        // Act
        static::createClient()->request('POST', '/api/employees', [
            'json' => $employeeData
        ]);

        // Assert
        $this->assertResponseStatusCodeSame(422);
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
    ): EmployeeEntity {
        $domainEmployee = Employee::create(
            new FullName($firstName, $lastName),
            new Email($email),
            new Position($position),
            new Salary($salaryAmount, $salaryCurrency),
            new \DateTimeImmutable('2024-01-15')
        );

        $employeeEntity = EmployeeEntity::fromDomain($domainEmployee);
        
        $this->entityManager->persist($employeeEntity);
        $this->entityManager->flush();

        return $employeeEntity;
    }
}