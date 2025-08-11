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
        
        // Start transaction for test isolation
        $this->connection->beginTransaction();
        
        // Clean database after transaction starts to ensure isolation
        $this->cleanDatabase();
        
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
        
        foreach ($tablesToTruncate as $table) {
            try {
                // Check count before truncate
                $countBefore = $this->connection->fetchOne("SELECT COUNT(*) FROM {$table}");
                
                // Use CASCADE to handle foreign key constraints in PostgreSQL
                $this->connection->executeStatement("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE");
                
                // Check count after truncate
                $countAfter = $this->connection->fetchOne("SELECT COUNT(*) FROM {$table}");
                
                // Debug output
                error_log("Table {$table}: Before={$countBefore}, After={$countAfter}");
            } catch (\Exception $e) {
                // Table might not exist or might be empty, continue
                error_log("Error truncating table {$table}: " . $e->getMessage());
                continue;
            }
        }
    }
}