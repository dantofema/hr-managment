<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\Exception;

use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\ValueObject\Email;
use PHPUnit\Framework\TestCase;

class UserNotFoundExceptionTest extends TestCase
{
    public function testCanCreateWithId(): void
    {
        $id = Uuid::generate();
        $exception = UserNotFoundException::withId($id);

        $this->assertInstanceOf(UserNotFoundException::class, $exception);
        $this->assertEquals(
            sprintf('User with ID "%s" not found', $id->toString()),
            $exception->getMessage()
        );
    }

    public function testCanCreateWithEmail(): void
    {
        $email = new Email('test@example.com');
        $exception = UserNotFoundException::withEmail($email);

        $this->assertInstanceOf(UserNotFoundException::class, $exception);
        $this->assertEquals(
            'User with email "test@example.com" not found',
            $exception->getMessage()
        );
    }

    public function testExceptionIsThrowable(): void
    {
        $id = Uuid::generate();
        $exception = UserNotFoundException::withId($id);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage(sprintf('User with ID "%s" not found', $id->toString()));

        throw $exception;
    }

    public function testEmailExceptionIsThrowable(): void
    {
        $email = new Email('notfound@example.com');
        $exception = UserNotFoundException::withEmail($email);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User with email "notfound@example.com" not found');

        throw $exception;
    }
}