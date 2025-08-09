<?php

declare(strict_types=1);

namespace App\Application\User\UseCase;

use App\Application\User\DTO\LoginRequest;
use App\Application\User\DTO\LoginResponse;
use App\Application\User\Exception\InvalidCredentialsException;
use App\Application\User\Service\AuthenticationService;
use App\Application\User\Service\JwtTokenService;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginUser
{
    private AuthenticationService $authenticationService;
    private JwtTokenService $jwtTokenService;
    private ValidatorInterface $validator;

    public function __construct(
        AuthenticationService $authenticationService,
        JwtTokenService $jwtTokenService,
        ValidatorInterface $validator
    ) {
        $this->authenticationService = $authenticationService;
        $this->jwtTokenService = $jwtTokenService;
        $this->validator = $validator;
    }

    public function execute(LoginRequest $request): LoginResponse
    {
        $this->validateRequest($request);

        try {
            $user = $this->authenticationService->authenticate(
                $request->getEmail(),
                $request->getPassword()
            );

            $token = $this->jwtTokenService->generateToken($user);
            $expiresAt = $this->jwtTokenService->getTokenExpirationDate();

            return new LoginResponse($token, $expiresAt, $user);
        } catch (InvalidCredentialsException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new InvalidCredentialsException('Authentication failed: ' . $e->getMessage());
        }
    }

    private function validateRequest(LoginRequest $request): void
    {
        $violations = $this->validator->validate($request);

        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }
            throw new InvalidCredentialsException('Validation failed: ' . implode(', ', $messages));
        }
    }
}