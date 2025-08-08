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
use App\Application\DTO\Employee\EmployeeResponse;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['employee:read']],
        ),
        new Get(
            uriTemplate: '/employees/{id}',
            normalizationContext: ['groups' => ['employee:read', 'employee:item']],
        ),
        new Post(
            denormalizationContext: ['groups' => ['employee:write']],
            normalizationContext: ['groups' => ['employee:read']],
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
    #[Groups(['employee:read'])]
    public string $id;

    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    public string $firstName;

    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    public string $lastName;

    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    public string $position;

    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\NotBlank]
    #[Assert\Positive]
    public float $salaryAmount;

    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(exactly: 3)]
    public string $salaryCurrency;

    #[Groups(['employee:read', 'employee:write'])]
    #[Assert\NotNull]
    #[Assert\Type(\DateTimeImmutable::class)]
    public \DateTimeImmutable $hiredAt;

    #[Groups(['employee:read'])]
    public \DateTimeImmutable $createdAt;

    #[Groups(['employee:read'])]
    public ?\DateTimeImmutable $updatedAt = null;

    // Computed properties - values set from Application layer
    #[Groups(['employee:read', 'employee:item'])]
    public string $fullName;

    #[Groups(['employee:read', 'employee:item'])]
    public int $yearsOfService;

    #[Groups(['employee:read', 'employee:item'])]
    public int $annualVacationDays;

    #[Groups(['employee:read', 'employee:item'])]
    public bool $vacationEligible;

    public static function fromApplicationDTO(EmployeeResponse $dto): self
    {
        $resource = new self();
        $resource->id = $dto->id;
        $resource->fullName = $dto->fullName;
        $resource->email = $dto->email;
        $resource->position = $dto->position;
        $resource->salaryAmount = $dto->salaryAmount;
        $resource->salaryCurrency = $dto->salaryCurrency;
        $resource->hiredAt = new \DateTimeImmutable($dto->hiredAt);
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