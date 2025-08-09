<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Infrastructure\ApiResource\Employee;
use App\Domain\Employee\EmployeeRepository;
use App\Domain\Shared\ValueObject\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class EmployeeItemProvider implements ProviderInterface
{
    public function __construct(
        private EmployeeRepository $employeeRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $id = $uriVariables['id'] ?? null;
        
        if (!$id) {
            throw new NotFoundHttpException('Employee ID is required');
        }

        try {
            $employeeId = new Uuid($id);
            $employee = $this->employeeRepository->findById($employeeId);
            
            if (!$employee) {
                throw new NotFoundHttpException('Employee not found');
            }
            
            $apiEmployee = new Employee();
            $apiEmployee->id = $employee->getId()->toString();
            $apiEmployee->firstName = $employee->getFullName()->firstName();
            $apiEmployee->lastName = $employee->getFullName()->lastName();
            $apiEmployee->email = $employee->getEmail()->value();
            $apiEmployee->position = $employee->getPosition()->value();
            $apiEmployee->salaryAmount = $employee->getSalary()->amount();
            $apiEmployee->salaryCurrency = $employee->getSalary()->currency();
            $apiEmployee->hiredAt = $employee->getHiredAt()->format('Y-m-d');
            $apiEmployee->createdAt = $employee->getCreatedAt()?->format('c');
            $apiEmployee->updatedAt = $employee->getUpdatedAt()?->format('c');
            $apiEmployee->fullName = $employee->getFullName()->fullName();
            $apiEmployee->yearsOfService = $employee->getYearsOfService();
            $apiEmployee->annualVacationDays = $employee->calculateAnnualVacationDays();
            $apiEmployee->vacationEligible = $employee->isEligibleForVacation();
            
            return $apiEmployee;
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException('Invalid employee ID format');
        }
    }
}