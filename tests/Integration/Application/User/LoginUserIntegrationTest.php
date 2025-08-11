<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\User;

use App\Application\User\DTO\LoginRequest;
use App\Application\User\DTO\LoginResponse;
use App\Application\User\Exception\InvalidCredentialsException;
use App\Application\User\Service\AuthenticationService;
use App\Application\User\Service\JwtTokenService;
use App\Application\User\UseCase\LoginUser;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginUserIntegrationTest extends KernelTestCase
{
    private LoginUser $loginUser;
    private UserRepositoryInterface $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private User $testUser;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->userRepository = $container->get(UserRepositoryInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
        
        $authenticationService = $container->get(AuthenticationService::class);
        $jwtTokenService = $container->get(JwtTokenService::class);
        $validator = $container->get(ValidatorInterface::class);

        $this->loginUser = new LoginUser(
            $authenticationService,
            $jwtTokenService,
            $validator
        );

        $this->createTestUser();
    }

    protected function tearDown(): void
    {
        if (isset($this->testUser)) {
            try {
                $this->userRepository->delete($this->testUser);
            } catch (\Exception $e) {
                // User might not exist, ignore
            }
        }
        parent::tearDown();
    }

    public function testLoginWithValidCredentials(): void
    {
        $email = 'integration.test@example.com';
        $password = 'password123';
        
        $request = new LoginRequest($email, $password);
        $response = $this->loginUser->execute($request);

        $this->assertInstanceOf(LoginResponse::class, $response);
        $this->assertNotEmpty($response->getToken());
        $this->assertEquals('Bearer', $response->getTokenType());
        $this->assertGreaterThan(0, $response->getExpiresIn());
        
        $userData = $response->getUser();
        $this->assertEquals($email, $userData['email']);
        $this->assertContains('ROLE_USER', $userData['roles']);
    }

    public function testLoginWithInvalidEmail(): void
    {
        $request = new LoginRequest('nonexistent@example.com', 'password123');

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Invalid credentials for email: nonexistent@example.com');

        $this->loginUser->execute($request);
    }

    public function testLoginWithInvalidPassword(): void
    {
        $request = new LoginRequest('integration.test@example.com', 'wrongpassword');

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Invalid password provided');

        $this->loginUser->execute($request);
    }

    public function testLoginWithInvalidEmailFormat(): void
    {
        $request = new LoginRequest('invalid-email', 'password123');

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Validation failed');

        $this->loginUser->execute($request);
    }

    public function testLoginWithEmptyCredentials(): void
    {
        $request = new LoginRequest('', '');

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Validation failed');

        $this->loginUser->execute($request);
    }

    public function testLoginWithShortPassword(): void
    {
        $request = new LoginRequest('test@example.com', '123');

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Validation failed');

        $this->loginUser->execute($request);
    }

    public function testTokenContainsCorrectUserData(): void
    {
        $email = 'integration.test@example.com';
        $password = 'password123';
        
        $request = new LoginRequest($email, $password);
        $response = $this->loginUser->execute($request);

        // Verify token structure
        $token = $response->getToken();
        $this->assertIsString($token);
        $this->assertStringContainsString('.', $token); // JWT format check

        // Verify user data in response
        $userData = $response->getUser();
        $this->assertArrayHasKey('id', $userData);
        $this->assertArrayHasKey('email', $userData);
        $this->assertArrayHasKey('roles', $userData);
        $this->assertArrayHasKey('created_at', $userData);
        
        $this->assertEquals($email, $userData['email']);
        $this->assertIsArray($userData['roles']);
        $this->assertContains('ROLE_USER', $userData['roles']);
    }

    private function createTestUser(): void
    {
        $email = new Email('integration.test@example.com');
        $plainPassword = 'password123';
        
        // Use the domain method to create hashed password
        $hashedPassword = HashedPassword::fromPlainPassword($plainPassword);
        
        $this->testUser = User::create(
            $email,
            $hashedPassword,
            ['ROLE_USER']
        );

        $this->userRepository->save($this->testUser);
    }
}