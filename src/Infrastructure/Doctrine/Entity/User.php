<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Domain\User\User as DomainUser;
use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastLoginAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->isActive = true;
    }

    public static function fromDomain(DomainUser $user): self
    {
        $entity = new self();
        $entity->id = $user->getId()->toString();
        $entity->email = $user->getEmail()->value();
        $entity->password = $user->getPassword()->value();
        $entity->name = (string) $user->getEmail(); // Use email as name since domain User doesn't have name
        $entity->isActive = true; // Default to active since domain User doesn't track this
        $entity->createdAt = $user->getCreatedAt();
        $entity->updatedAt = $user->getUpdatedAt();
        $entity->lastLoginAt = null; // Domain User doesn't track last login

        return $entity;
    }

    public function toDomain(): DomainUser
    {
        $user = new DomainUser(
            new Uuid($this->id),
            new Email($this->email),
            new HashedPassword($this->password),
            ['ROLE_USER'] // Default roles since domain User expects array
        );

        // Set timestamps using reflection since they're private
        $reflection = new \ReflectionClass($user);
        
        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($user, $this->createdAt);

        if ($this->updatedAt) {
            $updatedAtProperty = $reflection->getProperty('updatedAt');
            $updatedAtProperty->setAccessible(true);
            $updatedAtProperty->setValue($user, $this->updatedAt);
        }

        return $user;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getLastLoginAt(): ?DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?DateTimeImmutable $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    // UserInterface methods
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        // Return default role for all users
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // This method is used to remove sensitive data from the user entity
        // In this case, we don't store plain text passwords, so nothing to erase
    }
}