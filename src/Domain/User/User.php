<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use DateTimeImmutable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private Uuid $id;
    private Email $email;
    private HashedPassword $password;
    private array $roles;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct(
        Uuid $id,
        Email $email,
        HashedPassword $password,
        array $roles = ['ROLE_USER']
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
        $this->createdAt = new DateTimeImmutable();
    }

    public static function create(
        Email $email,
        HashedPassword $password,
        array $roles = ['ROLE_USER']
    ): self {
        return new self(
            Uuid::generate(),
            $email,
            $password,
            $roles
        );
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return (string) $this->password;
    }

    public function getHashedPassword(): HashedPassword
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateEmail(Email $email): void
    {
        $this->email = $email;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updatePassword(HashedPassword $password): void
    {
        $this->password = $password;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addRole(string $role): void
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function removeRole(string $role): void
    {
        $key = array_search($role, $this->roles, true);
        if ($key !== false) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    // Symfony UserInterface implementation
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function eraseCredentials(): void
    {
        // Nothing to erase as we use value objects
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'email' => (string) $this->email,
            'roles' => $this->roles,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}