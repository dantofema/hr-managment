<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Domain\Employee\Entity\Employee as EmployeeEntity;

#[ApiResource(
    uriTemplate: '/v1/employees',
    operations: [
        new GetCollection(),
        new Get(uriTemplate: '/v1/employees/{id}'),
        new Patch(
            uriTemplate: '/v1/employees/{id}/status',
            controller: 'App\Api\Controller\ChangeEmployeeStatusController'
        ),
        new Delete(
            uriTemplate: '/v1/employees/{id}',
            controller: 'App\Api\Controller\DeleteEmployeeController'
        )
    ]
)]
class Employee
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $department,
        public string $role,
        public string $status
    ) {
    }

    public static function fromEntity(EmployeeEntity $employee): self
    {
        return new self(
            id: (string) $employee->getId(),
            name: (string) $employee->getName(),
            email: (string) $employee->getEmail(),
            department: $employee->getDepartment()->value,
            role: $employee->getRole()->value,
            status: $employee->getStatus()->value
        );
    }
}