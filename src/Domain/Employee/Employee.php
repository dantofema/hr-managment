<?php

declare(strict_types=1);

namespace App\Domain\Employee;

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

    public function getYearsOfService(): int
    {
        $now = new DateTimeImmutable();
        return $now->diff($this->hiredAt)->y;
    }

    public function getMonthsOfService(): int
    {
        $now = new DateTimeImmutable();
        $diff = $now->diff($this->hiredAt);
        return ($diff->y * 12) + $diff->m;
    }

    public function calculateAnnualVacationDays(): int
    {
        $yearsOfService = $this->getYearsOfService();
        
        // Base vacation days: 15 days
        $baseDays = 15;
        
        // Additional days based on years of service
        if ($yearsOfService >= 10) {
            return $baseDays + 10; // 25 days for 10+ years
        } elseif ($yearsOfService >= 5) {
            return $baseDays + 5; // 20 days for 5-9 years
        }
        
        return $baseDays; // 15 days for 0-4 years
    }

    public function isEligibleForVacation(): bool
    {
        // Employee must have worked for at least 3 months to be eligible for vacation
        return $this->getMonthsOfService() >= 3;
    }

    public function getVacationEligibilityDate(): DateTimeImmutable
    {
        return $this->hiredAt->modify('+3 months');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'full_name' => (string) $this->fullName,
            'email' => (string) $this->email,
            'position' => (string) $this->position,
            'salary' => (string) $this->salary,
            'hired_at' => $this->hiredAt->format('Y-m-d'),
            'years_of_service' => $this->getYearsOfService(),
            'annual_vacation_days' => $this->calculateAnnualVacationDays(),
            'vacation_eligible' => $this->isEligibleForVacation(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}