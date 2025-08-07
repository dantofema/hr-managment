<?php

declare(strict_types=1);

namespace App\Tests\Domain\Employee\ValueObject;

use App\Domain\Employee\ValueObject\Salary;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SalaryTest extends TestCase
{
    public function testValidSalary(): void
    {
        $salary = new Salary(50000.00, 'USD');
        
        $this->assertEquals(50000.00, $salary->amount());
        $this->assertEquals('USD', $salary->currency());
        $this->assertEquals('50,000.00 USD', (string) $salary);
    }

    public function testDefaultCurrency(): void
    {
        $salary = new Salary(50000.00);
        
        $this->assertEquals('USD', $salary->currency());
    }

    public function testNegativeSalaryThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Salary amount cannot be negative');
        
        new Salary(-1000.00);
    }

    public function testEmptyCurrencyThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency cannot be empty');
        
        new Salary(50000.00, '');
    }

    public function testInvalidCurrencyLengthThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency must be a 3-letter code (e.g., USD, EUR)');
        
        new Salary(50000.00, 'US');
    }

    public function testSalaryEquality(): void
    {
        $salary1 = new Salary(50000.00, 'USD');
        $salary2 = new Salary(50000.00, 'USD');
        $salary3 = new Salary(60000.00, 'USD');
        $salary4 = new Salary(50000.00, 'EUR');
        
        $this->assertTrue($salary1->equals($salary2));
        $this->assertFalse($salary1->equals($salary3));
        $this->assertFalse($salary1->equals($salary4));
    }

    public function testSalaryComparison(): void
    {
        $salary1 = new Salary(50000.00, 'USD');
        $salary2 = new Salary(60000.00, 'USD');
        
        $this->assertTrue($salary2->isGreaterThan($salary1));
        $this->assertFalse($salary1->isGreaterThan($salary2));
    }

    public function testComparisonWithDifferentCurrenciesThrowsException(): void
    {
        $salary1 = new Salary(50000.00, 'USD');
        $salary2 = new Salary(60000.00, 'EUR');
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot compare salaries with different currencies');
        
        $salary1->isGreaterThan($salary2);
    }
}