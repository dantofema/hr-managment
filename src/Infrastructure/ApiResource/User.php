<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['user:read']],
        ),
        new Get(
            uriTemplate: '/users/{id}',
            normalizationContext: ['groups' => ['user:read', 'user:item']],
        ),
    ],
    paginationEnabled: true,
    paginationItemsPerPage: 20
)]
class User
{
    #[Groups(['user:read'])]
    public string $id;

    #[Groups(['user:read', 'user:item'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Groups(['user:read', 'user:item'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public string $name;

    #[Groups(['user:read'])]
    public bool $isActive;

    #[Groups(['user:read'])]
    public \DateTimeImmutable $createdAt;

    #[Groups(['user:read'])]
    public ?\DateTimeImmutable $updatedAt = null;

    #[Groups(['user:item'])]
    public ?\DateTimeImmutable $lastLoginAt = null;

    // Password is intentionally excluded from all serialization groups
    // for security reasons - it should never be exposed via API

    public static function fromDomain(\App\Domain\User\User $user): self
    {
        $resource = new self();
        $resource->id = $user->getId()->toString();
        $resource->email = $user->getEmail()->value();
        $resource->name = $user->getName();
        $resource->isActive = $user->isActive();
        $resource->createdAt = $user->getCreatedAt();
        $resource->updatedAt = $user->getUpdatedAt();
        $resource->lastLoginAt = $user->getLastLoginAt();

        return $resource;
    }
}