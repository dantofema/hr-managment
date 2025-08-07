<?php

declare(strict_types=1);

namespace App\Domain\Shared\Interface;

use App\Domain\Shared\ValueObject\Uuid;

interface RepositoryInterface
{
    public function save(object $entity): void;
    
    public function findById(Uuid $id): ?object;
    
    public function findAll(): array;
    
    public function delete(object $entity): void;
}