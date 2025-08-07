<?php

declare(strict_types=1);

namespace App\Tests\Domain\Payroll\ValueObject;

use App\Domain\Payroll\ValueObject\GrossSalary;
use PHPUnit\Framework\TestCase;

class GrossSalaryTest extends TestCase
{
    public function testCanCreateValidGrossSalary(): void
    {
        $salary = new GrossSalary(5000.00, 'USD');
        
        $this->assertEquals(5000.00, $salary->getAmount());
        $this->assertEquals('USD', $salary->getCurrency());
    }

    public function testThrowsExceptionForNegativeAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Gross salary amount must be positive');
        
        new GrossSalary(-1000.00, 'USD');
    }

    public function testThrowsExceptionForZeroAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Gross salary amount must be positive');
        
        new GrossSalary(0.00, 'USD');
    }

    public function testThrowsExceptionForInvalidCurrency(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid currency code: INVALID');
        
        new GrossSalary(5000.00, 'INVALID');
    }

    public function testAcceptsValidCurrencyCodes(): void
    {
        $validCurrencies = ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY'];
        
        foreach ($validCurrencies as $currency) {
            $salary = new GrossSalary(5000.00, $currency);
            $this->assertEquals($currency, $salary->getCurrency());
        }
    }

    public function testCanAddToGrossSalary(): void
    {
        $salary1 = new GrossSalary(3000.00, 'USD');
        $salary2 = new GrossSalary(2000.00, 'USD');
        
        $result = $salary1->add($salary2);
        
        $this->assertEquals(5000.00, $result->getAmount());
        $this->assertEquals('USD', $result->getCurrency());
    }

    public function testCannotAddDifferentCurrencies(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot add different currencies: USD and EUR');
        
        $salary1 = new GrossSalary(3000.00, 'USD');
        $salary2 = new GrossSalary(2000.00, 'EUR');
        
        $salary1->add($salary2);
    }

    public function testCanSubtractFromGrossSalary(): void
    {
        $salary1 = new GrossSalary(5000.00, 'USD');
        $salary2 = new GrossSalary(2000.00, 'USD');
        
        $result = $salary1->subtract($salary2);
        
        $this->assertEquals(3000.00, $result->getAmount());
        $this->assertEquals('USD', $result->getCurrency());
    }

    public function testCannotSubtractDifferentCurrencies(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot subtract different currencies: USD and EUR');
        
        $salary1 = new GrossSalary(5000.00, 'USD');
        $salary2 = new GrossSalary(2000.00, 'EUR');
        
        $salary1->subtract($salary2);
    }

    public function testCanMultiplyGrossSalary(): void
    {
        $salary = new GrossSalary(1000.00, 'USD');
        $result = $salary->multiply(2.5);
        
        $this->assertEquals(2500.00, $result->getAmount());
        $this->assertEquals('USD', $result->getCurrency());
    }

    public function testCannotMultiplyByNegativeNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Multiplier must be positive');
        
        $salary = new GrossSalary(1000.00, 'USD');
        $salary->multiply(-1.5);
    }

    public function testCanFormatGrossSalary(): void
    {
        $salary = new GrossSalary(5000.50, 'USD');
        
        $this->assertEquals('5000.50 USD', $salary->format());
    }

    public function testGrossSalaryEquality(): void
    {
        $salary1 = new GrossSalary(5000.00, 'USD');
        $salary2 = new GrossSalary(5000.00, 'USD');
        $salary3 = new GrossSalary(4000.00, 'USD');
        $salary4 = new GrossSalary(5000.00, 'EUR');
        
        $this->assertTrue($salary1->equals($salary2));
        $this->assertFalse($salary1->equals($salary3));
        $this->assertFalse($salary1->equals($salary4));
    }

    public function testToString(): void
    {
        $salary = new GrossSalary(5000.50, 'USD');
        
        $this->assertEquals('5000.50 USD', (string) $salary);
    }

    public function testCanCompareGrossSalaries(): void
    {
        $salary1 = new GrossSalary(5000.00, 'USD');
        $salary2 = new GrossSalary(3000.00, 'USD');
        $salary3 = new GrossSalary(5000.00, 'USD');
        
        $this->assertTrue($salary1->isGreaterThan($salary2));
        $this->assertFalse($salary2->isGreaterThan($salary1));
        $this->assertFalse($salary1->isGreaterThan($salary3));
        
        $this->assertTrue($salary2->isLessThan($salary1));
        $this->assertFalse($salary1->isLessThan($salary2));
        $this->assertFalse($salary1->isLessThan($salary3));
    }

    public function testCannotCompareDifferentCurrencies(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot compare different currencies: USD and EUR');
        
        $salary1 = new GrossSalary(5000.00, 'USD');
        $salary2 = new GrossSalary(3000.00, 'EUR');
        
        $salary1->isGreaterThan($salary2);
    }
}