<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\ValueObject;

use App\Domain\User\ValueObject\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testCanCreateValidEmail(): void
    {
        $email = new Email('test@example.com');

        $this->assertEquals('test@example.com', $email->value());
        $this->assertEquals('test@example.com', (string) $email);
    }

    public function testCanCreateEmailWithValidFormats(): void
    {
        $validEmails = [
            'user@domain.com',
            'user.name@domain.com',
            'user+tag@domain.com',
            'user123@domain123.com',
            'user@sub.domain.com',
            'a@b.co',
        ];

        foreach ($validEmails as $validEmail) {
            $email = new Email($validEmail);
            $this->assertEquals($validEmail, $email->value());
        }
    }

    public function testThrowsExceptionForInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        new Email('invalid-email');
    }

    public function testThrowsExceptionForInvalidEmailFormats(): void
    {
        $invalidEmails = [
            '',
            'invalid',
            '@domain.com',
            'user@',
            'user@@domain.com',
            'user@domain',
            'user name@domain.com',
            'user@domain..com',
        ];

        foreach ($invalidEmails as $invalidEmail) {
            try {
                new Email($invalidEmail);
                $this->fail("Expected InvalidArgumentException for email: {$invalidEmail}");
            } catch (InvalidArgumentException $e) {
                $this->assertEquals('Invalid email format', $e->getMessage());
            }
        }
    }

    public function testEquals(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('test@example.com');
        $email3 = new Email('different@example.com');

        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }

    public function testToString(): void
    {
        $email = new Email('test@example.com');

        $this->assertEquals('test@example.com', $email->__toString());
        $this->assertEquals('test@example.com', (string) $email);
    }
}