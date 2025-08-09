<?php

declare(strict_types=1);

namespace App\Tests\Application\User\Service;

use App\Application\User\Service\JwtTokenService;
use App\Domain\User\User;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JwtTokenServiceTest extends TestCase
{
    private JwtTokenService $jwtTokenService;
    private JWTTokenManagerInterface|MockObject $jwtManager;
    private int $tokenTtl = 3600;

    protected function setUp(): void
    {
        $this->jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $this->jwtTokenService = new JwtTokenService($this->jwtManager, $this->tokenTtl);
    }

    public function testGenerateToken(): void
    {
        $user = User::create(
            new Email('test@example.com'),
            new HashedPassword('hashed_password'),
            ['ROLE_USER']
        );

        $expectedToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.test.token';

        $this->jwtManager
            ->expects($this->once())
            ->method('create')
            ->with($user)
            ->willReturn($expectedToken);

        $result = $this->jwtTokenService->generateToken($user);

        $this->assertEquals($expectedToken, $result);
    }

    public function testGetTokenExpirationDate(): void
    {
        $beforeCall = new DateTimeImmutable();
        $expirationDate = $this->jwtTokenService->getTokenExpirationDate();
        $afterCall = new DateTimeImmutable();

        $expectedMinTime = $beforeCall->modify('+' . $this->tokenTtl . ' seconds');
        $expectedMaxTime = $afterCall->modify('+' . $this->tokenTtl . ' seconds');

        $this->assertGreaterThanOrEqual($expectedMinTime->getTimestamp(), $expirationDate->getTimestamp());
        $this->assertLessThanOrEqual($expectedMaxTime->getTimestamp(), $expirationDate->getTimestamp());
    }

    public function testValidateTokenWithValidToken(): void
    {
        $token = 'valid.jwt.token';
        $payload = ['user_id' => 1, 'email' => 'test@example.com'];

        $this->jwtManager
            ->expects($this->once())
            ->method('parse')
            ->with($token)
            ->willReturn($payload);

        $result = $this->jwtTokenService->validateToken($token);

        $this->assertTrue($result);
    }

    public function testValidateTokenWithInvalidToken(): void
    {
        $token = 'invalid.jwt.token';

        $this->jwtManager
            ->expects($this->once())
            ->method('parse')
            ->with($token)
            ->willThrowException(new \Exception('Invalid token'));

        $result = $this->jwtTokenService->validateToken($token);

        $this->assertFalse($result);
    }

    public function testGetUserFromTokenWithValidToken(): void
    {
        $token = 'valid.jwt.token';
        $expectedPayload = ['user_id' => 1, 'email' => 'test@example.com', 'exp' => time() + 3600];

        $this->jwtManager
            ->expects($this->once())
            ->method('parse')
            ->with($token)
            ->willReturn($expectedPayload);

        $result = $this->jwtTokenService->getUserFromToken($token);

        $this->assertEquals($expectedPayload, $result);
    }

    public function testGetUserFromTokenWithInvalidToken(): void
    {
        $token = 'invalid.jwt.token';

        $this->jwtManager
            ->expects($this->once())
            ->method('parse')
            ->with($token)
            ->willThrowException(new \Exception('Invalid token'));

        $result = $this->jwtTokenService->getUserFromToken($token);

        $this->assertNull($result);
    }

    public function testIsTokenExpiredWithExpiredToken(): void
    {
        $token = 'expired.jwt.token';
        $expiredPayload = ['user_id' => 1, 'email' => 'test@example.com', 'exp' => time() - 3600];

        $this->jwtManager
            ->expects($this->once())
            ->method('parse')
            ->with($token)
            ->willReturn($expiredPayload);

        $result = $this->jwtTokenService->isTokenExpired($token);

        $this->assertTrue($result);
    }

    public function testIsTokenExpiredWithValidToken(): void
    {
        $token = 'valid.jwt.token';
        $validPayload = ['user_id' => 1, 'email' => 'test@example.com', 'exp' => time() + 3600];

        $this->jwtManager
            ->expects($this->once())
            ->method('parse')
            ->with($token)
            ->willReturn($validPayload);

        $result = $this->jwtTokenService->isTokenExpired($token);

        $this->assertFalse($result);
    }

    public function testIsTokenExpiredWithInvalidToken(): void
    {
        $token = 'invalid.jwt.token';

        $this->jwtManager
            ->expects($this->once())
            ->method('parse')
            ->with($token)
            ->willThrowException(new \Exception('Invalid token'));

        $result = $this->jwtTokenService->isTokenExpired($token);

        $this->assertTrue($result);
    }

    public function testGetTokenTtl(): void
    {
        $result = $this->jwtTokenService->getTokenTtl();

        $this->assertEquals($this->tokenTtl, $result);
    }
}