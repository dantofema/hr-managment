<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Infrastructure\Doctrine\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface, EventSubscriberInterface
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        /** @var User $user */
        $user = $token->getUser();
        
        // Update last login timestamp
        $user->setLastLoginAt(new DateTimeImmutable());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $jwt = $this->jwtManager->create($user);
        
        $data = [
            'token' => $jwt,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName() ?? $user->getEmail(),
                'roles' => $user->getRoles(),
            ]
        ];
        
        return new JsonResponse($data);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::JWT_CREATED => 'onJWTCreated',
        ];
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $data = $event->getData();
        $user = $event->getUser();
        
        if ($user instanceof User) {
            // Add unique session identifier to make tokens unique for concurrent sessions
            $data['jti'] = bin2hex(random_bytes(16)); // JWT ID claim with cryptographically secure unique identifier
            $data['user'] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName() ?? $user->getEmail(),
                'roles' => $user->getRoles(),
            ];
            
            $event->setData($data);
        }
    }
}