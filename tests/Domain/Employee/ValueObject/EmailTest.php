<?php

declare(strict_types=1);

namespace App\Tests\Domain\Employee\ValueObject;

use App\Domain\Employee\ValueObject\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testValidEmail(): void
    {
        $email = new Email('test@example.com');
        
        $this->assertEquals('test@example.com', $email->value());
        $this->assertEquals('test@example.com', (string) $email);
    }

    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');
        
        new Email('invalid-email');
    }

    public function testEmptyEmailThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');
        
        new Email('');
    }

    public function testEmailEquality(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('test@example.com');
        $email3 = new Email('different@example.com');
        
        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }
}