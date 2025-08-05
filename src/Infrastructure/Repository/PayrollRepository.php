<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Employee\Domain\ValueObject\EmployeeId;
use App\Domain\Payroll\Entity\Payroll;
use App\Domain\Payroll\Repository\PayrollRepositoryInterface;
use App\Domain\Payroll\ValueObject\PayrollId;
use App\Domain\Payroll\ValueObject\PayrollPeriod;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class PayrollRepository implements PayrollRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        $this->repository = $entityManager->getRepository(Payroll::class);
    }

    public function save(Payroll $payroll): void
    {
        $this->entityManager->persist($payroll);
        $this->entityManager->flush();
    }

    public function findById(PayrollId $id): ?Payroll
    {
        return $this->repository->findOneBy(['id' => (string) $id]);
    }

    public function findByEmployeeId(EmployeeId $employeeId): array
    {
        return $this->repository->findBy(
            ['employeeId' => (string) $employeeId],
            ['createdAt' => 'DESC']
        );
    }

    public function findByEmployeeIdAndPeriod(EmployeeId $employeeId, PayrollPeriod $period): ?Payroll
    {
        return $this->repository->findOneBy([
            'employeeId' => (string) $employeeId,
            'periodStart' => $period->getStartDate(),
            'periodEnd' => $period->getEndDate(),
        ]);
    }

    public function findAll(): array
    {
        return $this->repository->findBy([], ['createdAt' => 'DESC']);
    }

    public function delete(Payroll $payroll): void
    {
        $this->entityManager->remove($payroll);
        $this->entityManager->flush();
    }

    public function existsForEmployeeAndPeriod(EmployeeId $employeeId, PayrollPeriod $period): bool
    {
        $count = $this->repository->count([
            'employeeId' => (string) $employeeId,
            'periodStart' => $period->getStartDate(),
            'periodEnd' => $period->getEndDate(),
        ]);
        
        return $count > 0;
    }
}