<?php

declare(strict_types=1);

namespace App\Application\Employee\Handler;

use App\Employee\Application\Query\GetEmployeeByIdQuery;
use App\Domain\Employee\Repository\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObject\EmployeeId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
final readonly class GetEmployeeByIdHandler
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {
    }

    public function __invoke(GetEmployeeByIdQuery $query): ?array
    {
        $employeeId = EmployeeId::fromString($query->employeeId);
        $employee = $this->employeeRepository->findById($employeeId);

        Assert::notNull($employee, 'Employee not found.');

        return $employee->getFullInfo();
    }
}

