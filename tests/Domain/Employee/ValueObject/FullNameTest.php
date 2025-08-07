<?php

declare(strict_types=1);

namespace App\Tests\Domain\Employee\ValueObject;

use App\Domain\Employee\ValueObject\FullName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FullNameTest extends TestCase
{
    public function testValidFullName(): void
    {
        $fullName = new FullName('John', 'Doe');
        
        $this->assertEquals('John', $fullName->firstName());
        $this->assertEquals('Doe', $fullName->lastName());
        $this->assertEquals('John Doe', $fullName->fullName());
        $this->assertEquals('John Doe', (string) $fullName);
    }

    public function testEmptyFirstNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('First name cannot be empty');
        
        new FullName('', 'Doe');
    }

    public function testEmptyLastNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Last name cannot be empty');
        
        new FullName('John', '');
    }

    public function testWhitespaceOnlyNamesThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('First name cannot be empty');
        
        new FullName('   ', 'Doe');
    }

    public function testFullNameEquality(): void
    {
        $fullName1 = new FullName('John', 'Doe');
        $fullName2 = new FullName('John', 'Doe');
        $fullName3 = new FullName('Jane', 'Doe');
        
        $this->assertTrue($fullName1->equals($fullName2));
        $this->assertFalse($fullName1->equals($fullName3));
    }
}