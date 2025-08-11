<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\ArrayPaginator;
use App\Infrastructure\ApiResource\User;
use App\Domain\User\UserRepositoryInterface;

final readonly class UserCollectionProvider implements ProviderInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $users = $this->userRepository->findAll();
        
        $apiUsers = [];
        foreach ($users as $user) {
            $apiUsers[] = User::fromDomain($user);
        }
        
        // Get pagination parameters
        $page = (int) ($context['filters']['page'] ?? 1);
        $itemsPerPage = $operation->getPaginationItemsPerPage() ?? 20;
        $offset = ($page - 1) * $itemsPerPage;
        
        // Create paginated result
        $totalItems = count($apiUsers);
        $paginatedItems = array_slice($apiUsers, $offset, $itemsPerPage);
        
        return new ArrayPaginator($paginatedItems, $offset, $itemsPerPage, $totalItems);
    }
}