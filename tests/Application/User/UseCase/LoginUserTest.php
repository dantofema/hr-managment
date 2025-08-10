<?php

declare(strict_types=1);

namespace App\Tests\Application\User\UseCase;

use App\Application\User\DTO\LoginRequest;
use App\Application\User\DTO\LoginResponse;
use App\Application\User\Exception\InvalidCredentialsException;
use App\Application\User\Service\AuthenticationService;
use App\Application\User\Service\JwtTokenService;
use App\Application\User\UseCase\LoginUser;
use App\Domain\User\User;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginUserTest extends TestCase
{
    private LoginUser $loginUser;
    private AuthenticationService|MockObject $authenticationService;
    private JwtTokenService|MockObject $jwtTokenService;
    private ValidatorInterface|MockObject $validator;

    protected function setUp(): void
    {
        $this->authenticationService = $this->createMock(AuthenticationService::class);
        $this->jwtTokenService = $this->createMock(JwtTokenService::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->loginUser = new LoginUser(
            $this->authenticationService,
            $this->jwtTokenService,
            $this->validator
        );
    }

    public function testExecuteWithValidCredentials(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $token = 'jwt.token.here';
        $expiresAt = new DateTimeImmutable('+1 hour');

        $request = new LoginRequest($email, $password);
        $user = User::create(
            new Email($email),
            HashedPassword::fromPlainPassword('test_password'),
            ['ROLE_USER']
        );

        // Mock validator - no violations
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn(new ConstraintViolationList());

        // Mock authentication service
        $this->authenticationService
            ->expects($this->once())
            ->method('authenticate')
            ->with($email, $password)
            ->willReturn($user);

        // Mock JWT token service
        $this->jwtTokenService
            ->expects($this->once())
            ->method('generateToken')
            ->with($user)
            ->willReturn($token);

        $this->jwtTokenService
            ->expects($this->once())
            ->method('getTokenExpirationDate')
            ->willReturn($expiresAt);

        $result = $this->loginUser->execute($request);

        $this->assertInstanceOf(LoginResponse::class, $result);
        $this->assertEquals($token, $result->getToken());
        $this->assertEquals($expiresAt, $result->getExpiresAt());
        $this->assertEquals($user->toArray(), $result->getUser());
    }

    public function testExecuteWithValidationErrors(): void
    {
        $request = new LoginRequest('', ''); // Invalid request

        $violation = $this->createMock(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn('Email is required');
        
        $violations = new ConstraintViolationList([$violation]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($violations);

        $this->authenticationService
            ->expects($this->never())
            ->method('authenticate');

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Validation failed: Email is required');

        $this->loginUser->execute($request);
    }

    public function testExecuteWithInvalidCredentials(): void
    {
        $email = 'test@example.com';
        $password = 'wrong_password';
        $request = new LoginRequest($email, $password);

        // Mock validator - no violations
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn(new ConstraintViolationList());

        // Mock authentication service to throw exception
        $this->authenticationService
            ->expects($this->once())
            ->method('authenticate')
            ->with($email, $password)
            ->willThrowException(new InvalidCredentialsException('Invalid credentials'));

        $this->jwtTokenService
            ->expects($this->never())
            ->method('generateToken');

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->loginUser->execute($request);
    }

    public function testExecuteWithUnexpectedException(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $request = new LoginRequest($email, $password);

        // Mock validator - no violations
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn(new ConstraintViolationList());

        // Mock authentication service to throw unexpected exception
        $this->authenticationService
            ->expects($this->once())
            ->method('authenticate')
            ->with($email, $password)
            ->willThrowException(new \RuntimeException('Database connection failed'));

        $this->jwtTokenService
            ->expects($this->never())
            ->method('generateToken');

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Authentication failed: Database connection failed');

        $this->loginUser->execute($request);
    }

    public function testExecuteWithMultipleValidationErrors(): void
    {
        $request = new LoginRequest('invalid-email', '123'); // Multiple validation errors

        $violation1 = $this->createMock(ConstraintViolation::class);
        $violation1->method('getMessage')->willReturn('Please provide a valid email address');
        
        $violation2 = $this->createMock(ConstraintViolation::class);
        $violation2->method('getMessage')->willReturn('Password must be at least 6 characters long');
        
        $violations = new ConstraintViolationList([$violation1, $violation2]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($violations);

        $this->authenticationService
            ->expects($this->never())
            ->method('authenticate');

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Validation failed: Please provide a valid email address, Password must be at least 6 characters long');

        $this->loginUser->execute($request);
    }
}