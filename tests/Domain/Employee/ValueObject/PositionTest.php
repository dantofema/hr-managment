<?php

declare(strict_types=1);

namespace App\Tests\Domain\Employee\ValueObject;

use App\Domain\Employee\ValueObject\Position;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PositionTest extends TestCase
{
    public function testValidPosition(): void
    {
        $position = new Position('Software Engineer');
        
        $this->assertEquals('Software Engineer', $position->value());
        $this->assertEquals('Software Engineer', (string) $position);
    }

    public function testEmptyPositionThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Position cannot be empty');
        
        new Position('');
    }

    public function testWhitespaceOnlyPositionThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Position cannot be empty');
        
        new Position('   ');
    }

    public function testTooLongPositionThrowsException(): void
    {
        $longPosition = str_repeat('a', 101);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Position cannot exceed 100 characters');
        
        new Position($longPosition);
    }

    public function testMaxLengthPositionIsValid(): void
    {
        $maxLengthPosition = str_repeat('a', 100);
        $position = new Position($maxLengthPosition);
        
        $this->assertEquals($maxLengthPosition, $position->value());
    }

    public function testPositionEquality(): void
    {
        $position1 = new Position('Software Engineer');
        $position2 = new Position('Software Engineer');
        $position3 = new Position('Senior Software Engineer');
        
        $this->assertTrue($position1->equals($position2));
        $this->assertFalse($position1->equals($position3));
    }
}