<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\User\User as DomainUser;
use App\Domain\User\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use App\Domain\Shared\ValueObject\Uuid;
use App\Infrastructure\Doctrine\Entity\User as UserEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserEntity::class);
    }

    public function save(DomainUser $user): void
    {
        $entity = UserEntity::fromDomain($user);
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function findById(Uuid $id): ?DomainUser
    {
        $entity = $this->find($id->toString());
        
        return $entity ? $entity->toDomain() : null;
    }

    public function findByEmail(Email $email): ?DomainUser
    {
        $entity = $this->findOneBy(['email' => $email->value()]);
        
        return $entity ? $entity->toDomain() : null;
    }

    public function findAll(): array
    {
        $entities = $this->findBy([]);
        
        return array_map(
            fn(UserEntity $entity) => $entity->toDomain(),
            $entities
        );
    }

    public function delete(DomainUser $user): void
    {
        $entity = $this->find($user->getId()->toString());
        
        if ($entity) {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        }
    }

    public function nextIdentity(): Uuid
    {
        return Uuid::generate();
    }

    public function findActiveByEmail(Email $email): ?DomainUser
    {
        $entity = $this->findOneBy([
            'email' => $email->value(),
            'isActive' => true
        ]);
        
        return $entity ? $entity->toDomain() : null;
    }
}