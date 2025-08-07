<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\DBAL\Connection;

class DatabaseConnectionTest extends KernelTestCase
{
    public function testDatabaseConnection(): void
    {
        self::bootKernel();
        
        /** @var Connection $connection */
        $connection = static::getContainer()->get('doctrine.dbal.default_connection');
        
        // Test that we can connect to the database
        $this->assertTrue($connection->connect());
        
        // Test a simple query
        $result = $connection->executeQuery('SELECT 1 as test_value');
        $row = $result->fetchAssociative();
        
        $this->assertEquals(1, $row['test_value']);
    }
    
    public function testDatabaseExists(): void
    {
        self::bootKernel();
        
        /** @var Connection $connection */
        $connection = static::getContainer()->get('doctrine.dbal.default_connection');
        
        // Test that we can query the database name
        $result = $connection->executeQuery('SELECT current_database() as db_name');
        $row = $result->fetchAssociative();
        
        $this->assertEquals('symfony_test', $row['db_name']);
    }
}