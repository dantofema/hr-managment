<?php

declare(strict_types=1);

namespace App\Domain\Payroll\Entity;

use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Employee\ValueObject\Role;
use App\Domain\Payroll\ValueObject\Money;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity]
#[ORM\Table(name: 'salaries')]
class Salary
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $employeeId;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $baseSalary;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $bonus;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'string', length: 50, enumType: Role::class)]
    private Role $role;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $effectiveDate;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        EmployeeId $employeeId,
        Money $baseSalary,
        Money $bonus,
        Role $role,
        ?DateTimeImmutable $effectiveDate = null
    ) {
        if ($baseSalary->getCurrency() !== $bonus->getCurrency()) {
            throw new InvalidArgumentException('Base salary and bonus must have the same currency');
        }

        $this->employeeId = (string) $employeeId;
        $this->baseSalary = (string) $baseSalary->getAmount();
        $this->bonus = (string) $bonus->getAmount();
        $this->currency = $baseSalary->getCurrency();
        $this->role = $role;
        $this->effectiveDate = $effectiveDate ?? new DateTimeImmutable();
        $this->createdAt = new DateTimeImmutable();
    }

    public static function createForEmployee(
        EmployeeId $employeeId,
        Role $role,
        Money $baseSalary,
        ?Money $bonus = null
    ): self {
        $bonus = $bonus ?? Money::zero($baseSalary->getCurrency());
        return new self($employeeId, $baseSalary, $bonus, $role);
    }

    public function getEmployeeId(): EmployeeId
    {
        return EmployeeId::fromString($this->employeeId);
    }

    public function getBaseSalary(): Money
    {
        return Money::fromFloat((float) $this->baseSalary, $this->currency);
    }

    public function getBonus(): Money
    {
        return Money::fromFloat((float) $this->bonus, $this->currency);
    }

    public function getTotalSalary(): Money
    {
        return $this->getBaseSalary()->add($this->getBonus());
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getEffectiveDate(): DateTimeImmutable
    {
        return $this->effectiveDate;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updateBaseSalary(Money $newBaseSalary): void
    {
        if ($newBaseSalary->getCurrency() !== $this->currency) {
            throw new InvalidArgumentException('New base salary must have the same currency');
        }

        $this->baseSalary = (string) $newBaseSalary->getAmount();
    }

    public function updateBonus(Money $newBonus): void
    {
        if ($newBonus->getCurrency() !== $this->currency) {
            throw new InvalidArgumentException('New bonus must have the same currency');
        }

        $this->bonus = (string) $newBonus->getAmount();
    }

    public function updateRole(Role $newRole): void
    {
        $this->role = $newRole;
    }

    public function isEffectiveAt(DateTimeImmutable $date): bool
    {
        return $date >= $this->effectiveDate;
    }

    public function toArray(): array
    {
        return [
            'employee_id' => $this->employeeId,
            'base_salary' => $this->getBaseSalary()->toArray(),
            'bonus' => $this->getBonus()->toArray(),
            'total_salary' => $this->getTotalSalary()->toArray(),
            'role' => $this->role->value,
            'effective_date' => $this->effectiveDate->format('Y-m-d'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}