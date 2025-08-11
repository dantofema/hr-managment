<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
use OpenApi\Attributes as OA;

class AuthController extends AbstractController
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    #[Route('/api/login_check', name: 'api_login_check', methods: ['POST'])]
    #[OA\Post(
        path: '/api/login_check',
        summary: 'User Login',
        description: 'Authenticate user with email and password credentials',
        tags: ['Authentication']
    )]
    #[OA\RequestBody(
        description: 'User login credentials',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['email', 'password'],
            properties: [
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    format: 'email',
                    description: 'User email address',
                    example: 'user@example.com'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    format: 'password',
                    description: 'User password',
                    example: 'password123'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Login successful',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'user',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '123e4567-e89b-12d3-a456-426614174000'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                        new OA\Property(
                            property: 'roles',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['ROLE_USER']
                        )
                    ]
                ),
                new OA\Property(property: 'message', type: 'string', example: 'Login successful')
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Authentication failed',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Missing credentials')
            ]
        )
    )]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return $this->json([
                'message' => 'Missing credentials',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Update last login time
        $user->recordLogin();
        
        // Persist the changes to database
        $this->userRepository->save($user);

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
    #[OA\Post(
        path: '/api/login',
        summary: 'Login Information',
        description: 'Get information about how to perform login authentication',
        tags: ['Authentication']
    )]
    #[OA\Response(
        response: 200,
        description: 'Login instructions',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Send POST request to /api/login_check with email and password in JSON format'),
                new OA\Property(
                    property: 'example',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                        new OA\Property(property: 'password', type: 'string', example: 'password123')
                    ]
                )
            ]
        )
    )]
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

    #[Route('/api/auth/login', name: 'api_auth_login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Alternative User Login',
        description: 'Alternative endpoint to authenticate user with email and password credentials',
        tags: ['Authentication']
    )]
    #[OA\RequestBody(
        description: 'User login credentials',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['email', 'password'],
            properties: [
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    format: 'email',
                    description: 'User email address',
                    example: 'user@example.com'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    format: 'password',
                    description: 'User password',
                    example: 'password123'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Login successful',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'user',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '123e4567-e89b-12d3-a456-426614174000'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                        new OA\Property(
                            property: 'roles',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['ROLE_USER']
                        )
                    ]
                ),
                new OA\Property(property: 'message', type: 'string', example: 'Login successful')
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: 'Authentication failed',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Missing credentials')
            ]
        )
    )]
    public function authLogin(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return $this->json([
                'message' => 'Missing credentials',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Update last login time
        $user->recordLogin();
        
        // Persist the changes to database
        $this->userRepository->save($user);

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
}