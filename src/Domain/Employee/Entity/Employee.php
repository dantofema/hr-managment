<?php

declare(strict_types=1);

namespace App\Domain\Employee\Entity;

use App\Domain\Employee\ValueObject\Department;
use App\Domain\Employee\ValueObject\Email;
use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Employee\ValueObject\EmployeeName;
use App\Domain\Employee\ValueObject\EmployeeStatus;
use App\Domain\Employee\ValueObject\Role;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'employees')]
class Employee
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 50, enumType: Department::class)]
    private Department $department;

    #[ORM\Column(type: 'string', length: 50, enumType: Role::class)]
    private Role $role;

    #[ORM\Column(type: 'string', length: 20, enumType: EmployeeStatus::class)]
    private EmployeeStatus $status;

    public function __construct(
        EmployeeId $id,
        EmployeeName $name,
        Email $email,
        Department $department,
        Role $role
    ) {
        $this->id = (string) $id;
        $this->name = (string) $name;
        $this->email = (string) $email;
        $this->department = $department;
        $this->role = $role;
        $this->status = EmployeeStatus::ACTIVE;
    }

    public function getId(): EmployeeId
    {
        return EmployeeId::fromString($this->id);
    }

    public function getName(): EmployeeName
    {
        return EmployeeName::fromString($this->name);
    }

    public function getEmail(): Email
    {
        return Email::fromString($this->email);
    }

    public function getDepartment(): Department
    {
        return $this->department;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getStatus(): EmployeeStatus
    {
        return $this->status;
    }

    public function changeStatus(EmployeeStatus $newStatus): void
    {
        // Add any domain logic here before changing status
        $this->status = $newStatus;
    }

    public function changeName(EmployeeName $name): void
    {
        $this->name = (string) $name;
    }

    public function changeEmail(Email $email): void
    {
        $this->email = (string) $email;
    }

    public function changeDepartment(Department $department): void
    {
        $this->department = $department;
    }

    public function changeRole(Role $role): void
    {
        $this->role = $role;
    }

    public function isActive(): bool
    {
        return $this->status === EmployeeStatus::ACTIVE;
    }

    /**
     * @return array{id: string, name: string, email: string, department: string, role: string, status: string}
     */
    public function getFullInfo(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'department' => $this->department->value,
            'role' => $this->role->value,
            'status' => $this->status->value,
        ];
    }
}

