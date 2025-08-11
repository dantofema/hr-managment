<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class DatabaseTestCase extends WebTestCase
{
    protected EntityManagerInterface $entityManager;
    protected Connection $connection;
    protected ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create client which boots the kernel
        static::createClient();
        $this->container = static::getContainer();
        $this->entityManager = $this->container->get(EntityManagerInterface::class);
        $this->connection = $this->entityManager->getConnection();
        
        // Clean database first to ensure complete isolation
        $this->cleanDatabase();
        
        // Start transaction for test isolation
        $this->connection->beginTransaction();
        
        // Clear EntityManager cache to ensure fresh data is loaded
        $this->entityManager->clear();
    }

    protected function tearDown(): void
    {
        // Rollback transaction to clean up test data
        if ($this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }
        
        $this->entityManager->close();
        parent::tearDown();
    }

    protected function persistAndFlush(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    protected function flush(): void
    {
        $this->entityManager->flush();
    }

    protected function clear(): void
    {
        $this->entityManager->clear();
    }

    protected function refresh(object $entity): void
    {
        $this->entityManager->refresh($entity);
    }

    protected function getRepository(string $entityClass): object
    {
        return $this->entityManager->getRepository($entityClass);
    }

    protected function cleanDatabase(): void
    {
        // List of tables to truncate (in order to avoid foreign key constraints)
        $tablesToTruncate = [
            'payrolls',
            'vacations', 
            'employees',
            'users'
        ];
        
        // Disable foreign key checks temporarily for more reliable cleanup
        try {
            $this->connection->executeStatement('SET session_replication_role = replica;');
        } catch (\Exception $e) {
            // Might not be PostgreSQL, continue
        }
        
        foreach ($tablesToTruncate as $table) {
            try {
                // Check if table exists first
                $tableExists = $this->connection->fetchOne(
                    "SELECT COUNT(*) FROM information_schema.tables WHERE table_name = ?",
                    [$table]
                );
                
                if (!$tableExists) {
                    continue;
                }
                
                // Check count before truncate
                $countBefore = $this->connection->fetchOne("SELECT COUNT(*) FROM {$table}");
                
                if ($countBefore > 0) {
                    // Use CASCADE to handle foreign key constraints in PostgreSQL
                    $this->connection->executeStatement("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE");
                    
                    // Verify cleanup
                    $countAfter = $this->connection->fetchOne("SELECT COUNT(*) FROM {$table}");
                    
                    // Debug output only if there was data to clean
                    error_log("Table {$table}: Before={$countBefore}, After={$countAfter}");
                }
            } catch (\Exception $e) {
                // Log error but continue with other tables
                error_log("Error cleaning table {$table}: " . $e->getMessage());
                continue;
            }
        }
        
        // Re-enable foreign key checks
        try {
            $this->connection->executeStatement('SET session_replication_role = DEFAULT;');
        } catch (\Exception $e) {
            // Might not be PostgreSQL, continue
        }
    }
}