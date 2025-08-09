<?php

declare(strict_types=1);

namespace App\Tests\Application\User\Service;

use App\Application\User\Exception\InvalidCredentialsException;
use App\Application\User\Service\AuthenticationService;
use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthenticationServiceTest extends TestCase
{
    private AuthenticationService $authenticationService;
    private UserRepositoryInterface|MockObject $userRepository;
    private UserPasswordHasherInterface|MockObject $passwordHasher;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        
        $this->authenticationService = new AuthenticationService(
            $this->userRepository,
            $this->passwordHasher
        );
    }

    public function testAuthenticateWithValidCredentials(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        
        $user = User::create(
            new Email($email),
            new HashedPassword('hashed_password'),
            ['ROLE_USER']
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with($this->callback(fn($emailVO) => (string) $emailVO === $email))
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, $password)
            ->willReturn(true);

        $result = $this->authenticationService->authenticate($email, $password);

        $this->assertSame($user, $result);
    }

    public function testAuthenticateWithInvalidEmail(): void
    {
        $email = 'nonexistent@example.com';
        $password = 'password123';

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn(null);

        $this->passwordHasher
            ->expects($this->never())
            ->method('isPasswordValid');

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Invalid credentials for email: nonexistent@example.com');

        $this->authenticationService->authenticate($email, $password);
    }

    public function testAuthenticateWithInvalidPassword(): void
    {
        $email = 'test@example.com';
        $password = 'wrong_password';
        
        $user = User::create(
            new Email($email),
            new HashedPassword('hashed_password'),
            ['ROLE_USER']
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, $password)
            ->willReturn(false);

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Invalid password provided');

        $this->authenticationService->authenticate($email, $password);
    }

    public function testIsPasswordValid(): void
    {
        $user = User::create(
            new Email('test@example.com'),
            new HashedPassword('hashed_password'),
            ['ROLE_USER']
        );
        $password = 'password123';

        $this->passwordHasher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, $password)
            ->willReturn(true);

        $result = $this->authenticationService->isPasswordValid($user, $password);

        $this->assertTrue($result);
    }

    public function testVerifyUserExists(): void
    {
        $email = 'test@example.com';
        $user = User::create(
            new Email($email),
            new HashedPassword('hashed_password'),
            ['ROLE_USER']
        );

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn($user);

        $result = $this->authenticationService->verifyUserExists($email);

        $this->assertTrue($result);
    }

    public function testVerifyUserExistsReturnsFalseForNonexistentUser(): void
    {
        $email = 'nonexistent@example.com';

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn(null);

        $result = $this->authenticationService->verifyUserExists($email);

        $this->assertFalse($result);
    }
}