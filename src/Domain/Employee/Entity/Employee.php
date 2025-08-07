<?php

declare(strict_types=1);

namespace App\Domain\Employee\Entity;

use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\Employee\ValueObject\Email;
use App\Domain\Employee\ValueObject\FullName;
use App\Domain\Employee\ValueObject\Position;
use App\Domain\Employee\ValueObject\Salary;
use DateTimeImmutable;

class Employee
{
    private Uuid $id;
    private FullName $fullName;
    private Email $email;
    private Position $position;
    private Salary $salary;
    private DateTimeImmutable $hiredAt;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct(
        Uuid $id,
        FullName $fullName,
        Email $email,
        Position $position,
        Salary $salary,
        DateTimeImmutable $hiredAt
    ) {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->position = $position;
        $this->salary = $salary;
        $this->hiredAt = $hiredAt;
        $this->createdAt = new DateTimeImmutable();
    }

    public static function create(
        FullName $fullName,
        Email $email,
        Position $position,
        Salary $salary,
        DateTimeImmutable $hiredAt
    ): self {
        return new self(
            Uuid::generate(),
            $fullName,
            $email,
            $position,
            $salary,
            $hiredAt
        );
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getFullName(): FullName
    {
        return $this->fullName;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function getSalary(): Salary
    {
        return $this->salary;
    }

    public function getHiredAt(): DateTimeImmutable
    {
        return $this->hiredAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updatePosition(Position $position): void
    {
        $this->position = $position;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateSalary(Salary $salary): void
    {
        $this->salary = $salary;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateEmail(Email $email): void
    {
        $this->email = $email;
        $this->updatedAt = new DateTimeImmutable();
    }
}