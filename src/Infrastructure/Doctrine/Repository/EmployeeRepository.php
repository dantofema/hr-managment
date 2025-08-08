<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Employee\Employee as DomainEmployee;
use App\Domain\Employee\EmployeeRepository as DomainEmployeeRepository;
use App\Domain\Employee\ValueObject\Email;
use App\Domain\Shared\ValueObject\Uuid;
use App\Infrastructure\Doctrine\Entity\Employee as EmployeeEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class EmployeeRepository extends ServiceEntityRepository implements DomainEmployeeRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmployeeEntity::class);
    }

    public function save(DomainEmployee $employee): void
    {
        $entity = EmployeeEntity::fromDomain($employee);
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function findById(Uuid $id): ?DomainEmployee
    {
        $entity = $this->find($id->toString());
        
        return $entity ? $entity->toDomain() : null;
    }

    public function findByEmail(Email $email): ?DomainEmployee
    {
        $entity = $this->findOneBy(['email' => $email->toString()]);
        
        return $entity ? $entity->toDomain() : null;
    }

    public function findAll(): array
    {
        $entities = $this->findBy([]);
        
        return array_map(
            fn(EmployeeEntity $entity) => $entity->toDomain(),
            $entities
        );
    }

    public function delete(DomainEmployee $employee): void
    {
        $entity = $this->find($employee->getId()->toString());
        
        if ($entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
    }

    public function nextIdentity(): Uuid
    {
        return Uuid::generate();
    }
}