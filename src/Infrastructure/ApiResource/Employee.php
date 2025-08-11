<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use App\Application\UseCase\Employee\GetEmployee\GetEmployeeResponse;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/employees/{id}',
            normalizationContext: ['groups' => ['employee:read', 'employee:item']],
            provider: \App\Infrastructure\ApiPlatform\Provider\EmployeeItemProvider::class,
        ),
        new Post(
            denormalizationContext: ['groups' => ['employee:write']],
            normalizationContext: ['groups' => ['employee:create']],
            processor: \App\Infrastructure\ApiPlatform\Processor\EmployeeProcessor::class,
        ),
        new Put(
            uriTemplate: '/employees/{id}',
            denormalizationContext: ['groups' => ['employee:write']],
            normalizationContext: ['groups' => ['employee:read']],
        ),
        new Patch(
            uriTemplate: '/employees/{id}',
            denormalizationContext: ['groups' => ['employee:write']],
            normalizationContext: ['groups' => ['employee:read']],
        ),
        new Delete(
            uriTemplate: '/employees/{id}',
        ),
    ],
    paginationEnabled: true,
    paginationItemsPerPage: 20
)]
class Employee
{
    #[Groups(['employee:read', 'employee:create'])]
    public string $id = '';

    #[Groups(['employee:read', 'employee:write', 'employee:create'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    public string $firstName = '';

    #[Groups(['employee:read', 'employee:write', 'employee:create'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    public string $lastName = '';

    #[Groups(['employee:read', 'employee:write', 'employee:create'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email = '';

    #[Groups(['employee:read', 'employee:write', 'employee:create'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    public string $position = '';

    #[Groups(['employee:read', 'employee:write', 'employee:create'])]
    #[Assert\NotBlank]
    #[Assert\Positive]
    public float $salaryAmount = 0.0;

    #[Groups(['employee:read', 'employee:write', 'employee:create'])]
    #[Assert\NotBlank]
    #[Assert\Length(exactly: 3)]
    public string $salaryCurrency = '';

    #[Groups(['employee:read', 'employee:write', 'employee:create'])]
    #[Assert\NotBlank]
    #[Assert\Date(message: 'The hire date must be a valid date in YYYY-MM-DD format.')]
    public string $hiredAt = '';

    #[Groups(['employee:read', 'employee:create'])]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups(['employee:read', 'employee:create'])]
    public ?\DateTimeImmutable $updatedAt = null;

    // Computed properties - values set from Application layer
    #[Groups(['employee:read', 'employee:item', 'employee:create'])]
    public string $fullName = '';

    #[Groups(['employee:read', 'employee:item'])]
    public int $yearsOfService = 0;

    #[Groups(['employee:read', 'employee:item'])]
    public int $annualVacationDays = 0;

    #[Groups(['employee:read', 'employee:item'])]
    public bool $vacationEligible = false;

    public static function fromApplicationDTO(GetEmployeeResponse $dto): self
    {
        $resource = new self();
        $resource->id = $dto->id;
        $resource->fullName = $dto->fullName;
        $resource->email = $dto->email;
        $resource->position = $dto->position;
        $resource->salaryAmount = $dto->salaryAmount;
        $resource->salaryCurrency = $dto->salaryCurrency;
        $resource->hiredAt = $dto->hiredAt;
        $resource->yearsOfService = $dto->yearsOfService;
        $resource->annualVacationDays = $dto->annualVacationDays;
        $resource->vacationEligible = $dto->vacationEligible;

        // Extract first and last name from full name for API compatibility
        $nameParts = explode(' ', $dto->fullName, 2);
        $resource->firstName = $nameParts[0] ?? '';
        $resource->lastName = $nameParts[1] ?? '';

        return $resource;
    }
}