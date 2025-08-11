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
}