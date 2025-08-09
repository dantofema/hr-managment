<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\ValueObject;

use App\Domain\User\ValueObject\HashedPassword;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class HashedPasswordTest extends TestCase
{
    public function testCanCreateFromValidHashedPassword(): void
    {
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $password = new HashedPassword($hashedPassword);

        $this->assertEquals($hashedPassword, $password->value());
        $this->assertEquals($hashedPassword, (string) $password);
    }

    public function testCanCreateFromPlainPassword(): void
    {
        $password = HashedPassword::fromPlainPassword('password123');

        $this->assertNotEquals('password123', $password->value());
        $this->assertTrue($password->verify('password123'));
        $this->assertFalse($password->verify('wrongpassword'));
    }

    public function testThrowsExceptionForShortPlainPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must be at least 8 characters long');

        HashedPassword::fromPlainPassword('short');
    }

    public function testThrowsExceptionForInvalidHashedPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid hashed password format');

        new HashedPassword('not-a-valid-hash');
    }

    public function testThrowsExceptionForEmptyPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid hashed password format');

        new HashedPassword('');
    }

    public function testVerifyPassword(): void
    {
        $plainPassword = 'mySecurePassword123';
        $password = HashedPassword::fromPlainPassword($plainPassword);

        $this->assertTrue($password->verify($plainPassword));
        $this->assertFalse($password->verify('wrongPassword'));
        $this->assertFalse($password->verify(''));
        $this->assertFalse($password->verify('mySecurePassword124'));
    }

    public function testEquals(): void
    {
        $plainPassword = 'password123';
        $password1 = HashedPassword::fromPlainPassword($plainPassword);
        $password2 = HashedPassword::fromPlainPassword($plainPassword);
        
        // Even with same plain password, hashes should be different due to salt
        $this->assertFalse($password1->equals($password2));
        
        // Same hash should be equal
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
        $password3 = new HashedPassword($hashedPassword);
        $password4 = new HashedPassword($hashedPassword);
        
        $this->assertTrue($password3->equals($password4));
    }

    public function testToString(): void
    {
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
        $password = new HashedPassword($hashedPassword);

        $this->assertEquals($hashedPassword, $password->__toString());
        $this->assertEquals($hashedPassword, (string) $password);
    }

    public function testDifferentPlainPasswordsProduceDifferentHashes(): void
    {
        $password1 = HashedPassword::fromPlainPassword('password123');
        $password2 = HashedPassword::fromPlainPassword('differentPassword');

        $this->assertNotEquals($password1->value(), $password2->value());
        $this->assertFalse($password1->equals($password2));
    }

    public function testSamePlainPasswordProducesDifferentHashesDueToSalt(): void
    {
        $password1 = HashedPassword::fromPlainPassword('password123');
        $password2 = HashedPassword::fromPlainPassword('password123');

        // Due to salt, same plain password should produce different hashes
        $this->assertNotEquals($password1->value(), $password2->value());
        $this->assertFalse($password1->equals($password2));
        
        // But both should verify the same plain password
        $this->assertTrue($password1->verify('password123'));
        $this->assertTrue($password2->verify('password123'));
    }
}