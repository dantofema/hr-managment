<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Domain\Employee\Employee as DomainEmployee;
use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\Employee\ValueObject\Email;
use App\Domain\Employee\ValueObject\FullName;
use App\Domain\Employee\ValueObject\Position;
use App\Domain\Employee\ValueObject\Salary;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'employees')]
class Employee
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 100)]
    private string $lastName;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 100)]
    private string $position;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $salaryAmount;

    #[ORM\Column(type: 'string', length: 3)]
    private string $salaryCurrency;

    #[ORM\Column(type: 'date_immutable')]
    private DateTimeImmutable $hiredAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public static function fromDomain(DomainEmployee $employee): self
    {
        $entity = new self();
        $entity->id = $employee->getId()->toString();
        $entity->firstName = $employee->getFullName()->firstName();
        $entity->lastName = $employee->getFullName()->lastName();
        $entity->email = $employee->getEmail()->value();
        $entity->position = $employee->getPosition()->value();
        $entity->salaryAmount = (string) $employee->getSalary()->amount();
        $entity->salaryCurrency = $employee->getSalary()->currency();
        $entity->hiredAt = $employee->getHiredAt();
        $entity->createdAt = $employee->getCreatedAt();
        $entity->updatedAt = $employee->getUpdatedAt();

        return $entity;
    }

    public function toDomain(): DomainEmployee
    {
        $employee = new DomainEmployee(
            new Uuid($this->id),
            new FullName($this->firstName, $this->lastName),
            new Email($this->email),
            new Position($this->position),
            new Salary((float) $this->salaryAmount, $this->salaryCurrency),
            $this->hiredAt
        );

        // Set timestamps using reflection since they're private
        $reflection = new \ReflectionClass($employee);
        
        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($employee, $this->createdAt);

        if ($this->updatedAt) {
            $updatedAtProperty = $reflection->getProperty('updatedAt');
            $updatedAtProperty->setAccessible(true);
            $updatedAtProperty->setValue($employee, $this->updatedAt);
        }

        return $employee;
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

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        $this->updatedAt = new DateTimeImmutable();
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

    public function getPosition(): string
    {
        return $this->position;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getSalaryAmount(): string
    {
        return $this->salaryAmount;
    }

    public function setSalaryAmount(string $salaryAmount): self
    {
        $this->salaryAmount = $salaryAmount;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getSalaryCurrency(): string
    {
        return $this->salaryCurrency;
    }

    public function setSalaryCurrency(string $salaryCurrency): self
    {
        $this->salaryCurrency = $salaryCurrency;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getHiredAt(): DateTimeImmutable
    {
        return $this->hiredAt;
    }

    public function setHiredAt(DateTimeImmutable $hiredAt): self
    {
        $this->hiredAt = $hiredAt;
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
}