<?php

declare(strict_types=1);

namespace App\Payroll\Infrastructure\Repository;

use App\Employee\Domain\ValueObject\EmployeeId;
use App\Payroll\Domain\Entity\Salary;
use App\Payroll\Domain\Repository\SalaryRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class SalaryRepository implements SalaryRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        $this->repository = $entityManager->getRepository(Salary::class);
    }

    public function save(Salary $salary): void
    {
        $this->entityManager->persist($salary);
        $this->entityManager->flush();
    }

    public function findByEmployeeId(EmployeeId $employeeId): ?Salary
    {
        return $this->repository->findOneBy(['employeeId' => (string) $employeeId]);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function delete(Salary $salary): void
    {
        $this->entityManager->remove($salary);
        $this->entityManager->flush();
    }

    public function existsForEmployee(EmployeeId $employeeId): bool
    {
        $count = $this->repository->count(['employeeId' => (string) $employeeId]);
        return $count > 0;
    }
}