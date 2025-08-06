<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Employee\Entity\Employee;
use App\Domain\Employee\Repository\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObject\Email;
use App\Domain\Employee\ValueObject\EmployeeId;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class EmployeeRepository implements EmployeeRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        $this->repository = $entityManager->getRepository(Employee::class);
    }

    public function save(Employee $employee): void
    {
        $this->entityManager->persist($employee);
        $this->entityManager->flush();
    }

    public function findById(EmployeeId $id): ?Employee
    {
        return $this->repository->findOneBy(['id' => (string) $id]);
    }

    public function findByEmail(Email $email): ?Employee
    {
        return $this->repository->findOneBy(['email' => (string) $email]);
    }

    public function findAllPaginated(
        int $page,
        int $limit,
        array $filters
    ): array {
        $queryBuilder = $this->repository->createQueryBuilder('e');

        // Apply filters if provided
        if (!empty($filters['department'])) {
            $queryBuilder->andWhere('e.department = :department')
                ->setParameter('department', $filters['department']);
        }

        if (!empty($filters['status'])) {
            $queryBuilder->andWhere('e.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['role'])) {
            $queryBuilder->andWhere('e.role = :role')
                ->setParameter('role', $filters['role']);
        }

        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->orderBy('e.name', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function countAll(array $filters = []): int
    {
        $queryBuilder = $this->repository->createQueryBuilder('e')
            ->select('COUNT(e.id)');

        // Apply the same filters as in findAllPaginated
        if (!empty($filters['department'])) {
            $queryBuilder->andWhere('e.department = :department')
                ->setParameter('department', $filters['department']);
        }

        if (!empty($filters['status'])) {
            $queryBuilder->andWhere('e.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['role'])) {
            $queryBuilder->andWhere('e.role = :role')
                ->setParameter('role', $filters['role']);
        }

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function delete(Employee $employee): void
    {
        $this->entityManager->remove($employee);
        $this->entityManager->flush();
    }
}
