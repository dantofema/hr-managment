<?php

declare(strict_types=1);

namespace App\Employee\Infrastructure\Repository;

use App\Employee\Domain\Entity\Employee;
use App\Employee\Domain\Repository\EmployeeRepositoryInterface;
use App\Employee\Domain\ValueObject\Email;
use App\Employee\Domain\ValueObject\EmployeeId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employee>
 */
class EmployeeRepository extends ServiceEntityRepository implements EmployeeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    public function save(Employee $employee): void
    {
        $em = $this->getEntityManager();
        $em->persist($employee);
        $em->flush();
    }

    public function findById(EmployeeId $id): ?Employee
    {
        return $this->find((string) $id);
    }

    public function findByEmail(Email $email): ?Employee
    {
        return $this->findOneBy(['email' => (string) $email]);
    }

    public function findAllPaginated(
        int $page,
        int $limit,
        array $filters
    ): array {
        $qb = $this->createQueryBuilder('e');

        if (isset($filters['department'])) {
            $qb->andWhere('e.department = :department')
                ->setParameter('department', $filters['department']);
        }

        if (isset($filters['status'])) {
            $qb->andWhere('e.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (isset($filters['role'])) {
            $qb->andWhere('e.role = :role')
                ->setParameter('role', $filters['role']);
        }

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function delete(Employee $employee): void
    {
        $em = $this->getEntityManager();
        $em->remove($employee);
        $em->flush();
    }
}
