<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Domain\Employee\Entity\Employee as DomainEmployee;
use App\Domain\Employee\ValueObject\Email;
use App\Domain\Employee\ValueObject\FullName;
use App\Domain\Employee\ValueObject\Position;
use App\Domain\Employee\ValueObject\Salary;
use App\Domain\Shared\ValueObject\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'employees')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['employee:read']],
    denormalizationContext: ['groups' => ['employee:write']]
)]
class Employee
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[Groups(['employee:read'])]
    private string $id;

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['employee:read', 'employee:write'])]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['employee:read', 'employee:write'])]
    private string $lastName;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Groups(['employee:read', 'employee:write'])]
    private string $email;

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['employee:read', 'employee:write'])]
    private string $position;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['employee:read', 'employee:write'])]
    private float $salary;

    #[ORM\Column(type: 'string', length: 3)]
    #[Groups(['employee:read', 'employee:write'])]
    private string $currency;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['employee:read', 'employee:write'])]
    private DateTimeImmutable $hiredAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['employee:read'])]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['employee:read'])]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->id = Uuid::generate()->value();
        $this->createdAt = new DateTimeImmutable();
        $this->currency = 'USD';
    }

    public function toDomain(): DomainEmployee
    {
        $domainEmployee = new DomainEmployee(
            Uuid::fromString($this->id),
            new FullName($this->firstName, $this->lastName),
            new Email($this->email),
            new Position($this->position),
            new Salary($this->salary, $this->currency),
            $this->hiredAt
        );

        return $domainEmployee;
    }

    public static function fromDomain(DomainEmployee $domainEmployee): self
    {
        $employee = new self();
        $employee->id = $domainEmployee->getId()->value();
        $employee->firstName = $domainEmployee->getFullName()->firstName();
        $employee->lastName = $domainEmployee->getFullName()->lastName();
        $employee->email = $domainEmployee->getEmail()->value();
        $employee->position = $domainEmployee->getPosition()->value();
        $employee->salary = $domainEmployee->getSalary()->amount();
        $employee->currency = $domainEmployee->getSalary()->currency();
        $employee->hiredAt = $domainEmployee->getHiredAt();
        $employee->createdAt = $domainEmployee->getCreatedAt();
        $employee->updatedAt = $domainEmployee->getUpdatedAt();

        return $employee;
    }

    // Getters and setters for API Platform
    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function setPosition(string $position): void
    {
        $this->position = $position;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getSalary(): float
    {
        return $this->salary;
    }

    public function setSalary(float $salary): void
    {
        $this->salary = $salary;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getHiredAt(): DateTimeImmutable
    {
        return $this->hiredAt;
    }

    public function setHiredAt(DateTimeImmutable $hiredAt): void
    {
        $this->hiredAt = $hiredAt;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}