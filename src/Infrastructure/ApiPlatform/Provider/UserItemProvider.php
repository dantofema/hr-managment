<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Infrastructure\ApiResource\User;
use App\Domain\User\UserRepositoryInterface;
use App\Domain\Shared\ValueObject\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class UserItemProvider implements ProviderInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $id = $uriVariables['id'] ?? null;
        
        if (!$id) {
            throw new NotFoundHttpException('User ID is required');
        }

        try {
            $userId = new Uuid($id);
            $user = $this->userRepository->findById($userId);
            
            if (!$user) {
                throw new NotFoundHttpException('User not found');
            }
            
            return User::fromDomain($user);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException('Invalid user ID format');
        }
    }
}