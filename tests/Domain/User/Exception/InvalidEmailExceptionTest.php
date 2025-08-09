<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\Exception;

use App\Domain\User\Exception\InvalidEmailException;
use PHPUnit\Framework\TestCase;

class InvalidEmailExceptionTest extends TestCase
{
    public function testCanCreateWithValue(): void
    {
        $invalidEmail = 'invalid-email';
        $exception = InvalidEmailException::withValue($invalidEmail);

        $this->assertInstanceOf(InvalidEmailException::class, $exception);
        $this->assertEquals(
            'Invalid email format: "invalid-email"',
            $exception->getMessage()
        );
    }

    public function testCanCreateWithDifferentInvalidValues(): void
    {
        $invalidEmails = [
            '',
            'no-at-symbol',
            '@domain.com',
            'user@',
            'user@@domain.com',
        ];

        foreach ($invalidEmails as $invalidEmail) {
            $exception = InvalidEmailException::withValue($invalidEmail);
            
            $this->assertInstanceOf(InvalidEmailException::class, $exception);
            $this->assertEquals(
                sprintf('Invalid email format: "%s"', $invalidEmail),
                $exception->getMessage()
            );
        }
    }

    public function testExceptionIsThrowable(): void
    {
        $invalidEmail = 'not-an-email';
        $exception = InvalidEmailException::withValue($invalidEmail);

        $this->expectException(InvalidEmailException::class);
        $this->expectExceptionMessage('Invalid email format: "not-an-email"');

        throw $exception;
    }
}