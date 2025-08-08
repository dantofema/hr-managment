<?php

declare(strict_types=1);

namespace App\Domain\Employee;

use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\Employee\ValueObject\Email;

interface EmployeeRepository
{
    public function save(Employee $employee): void;
    
    public function findById(Uuid $id): ?Employee;
    
    public function findByEmail(Email $email): ?Employee;
    
    public function findAll(): array;
    
    public function delete(Employee $employee): void;
    
    public function nextIdentity(): Uuid;
}