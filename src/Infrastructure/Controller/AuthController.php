<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Domain\User\User;

class AuthController extends AbstractController
{
    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return $this->json([
                'message' => 'Missing credentials',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Update last login time
        $user->recordLogin();

        return $this->json([
            'user' => [
                'id' => $user->getId()->toString(),
                'email' => (string) $user->getEmail(),
                'name' => $user->getName(),
                'roles' => $user->getRoles(),
            ],
            'message' => 'Login successful',
        ]);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function loginInfo(): JsonResponse
    {
        return $this->json([
            'message' => 'Send POST request to /api/login_check with email and password in JSON format',
            'example' => [
                'email' => 'user@example.com',
                'password' => 'password123'
            ]
        ]);
    }
}