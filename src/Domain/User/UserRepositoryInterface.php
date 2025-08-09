<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\User\ValueObject\Email;

interface UserRepositoryInterface
{
    public function save(User $user): void;
    
    public function findById(Uuid $id): ?User;
    
    public function findByEmail(Email $email): ?User;
    
    public function findAll(): array;
    
    public function delete(User $user): void;
    
    public function nextIdentity(): Uuid;
    
    public function findActiveByEmail(Email $email): ?User;
}