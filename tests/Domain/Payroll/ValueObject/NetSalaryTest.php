<?php

declare(strict_types=1);

namespace App\Tests\Domain\Payroll\ValueObject;

use App\Domain\Payroll\ValueObject\NetSalary;
use PHPUnit\Framework\TestCase;

class NetSalaryTest extends TestCase
{
    public function testCanCreateValidNetSalary(): void
    {
        $salary = new NetSalary(4000.00, 'USD');
        
        $this->assertEquals(4000.00, $salary->getAmount());
        $this->assertEquals('USD', $salary->getCurrency());
    }

    public function testThrowsExceptionForNegativeAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Net salary amount must be positive');
        
        new NetSalary(-1000.00, 'USD');
    }

    public function testThrowsExceptionForZeroAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Net salary amount must be positive');
        
        new NetSalary(0.00, 'USD');
    }

    public function testThrowsExceptionForInvalidCurrency(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid currency code: INVALID');
        
        new NetSalary(4000.00, 'INVALID');
    }

    public function testAcceptsValidCurrencyCodes(): void
    {
        $validCurrencies = ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY'];
        
        foreach ($validCurrencies as $currency) {
            $salary = new NetSalary(4000.00, $currency);
            $this->assertEquals($currency, $salary->getCurrency());
        }
    }

    public function testCanFormatNetSalary(): void
    {
        $salary = new NetSalary(4000.50, 'USD');
        
        $this->assertEquals('4000.50 USD', $salary->format());
    }

    public function testNetSalaryEquality(): void
    {
        $salary1 = new NetSalary(4000.00, 'USD');
        $salary2 = new NetSalary(4000.00, 'USD');
        $salary3 = new NetSalary(3000.00, 'USD');
        $salary4 = new NetSalary(4000.00, 'EUR');
        
        $this->assertTrue($salary1->equals($salary2));
        $this->assertFalse($salary1->equals($salary3));
        $this->assertFalse($salary1->equals($salary4));
    }

    public function testToString(): void
    {
        $salary = new NetSalary(4000.50, 'USD');
        
        $this->assertEquals('4000.50 USD', (string) $salary);
    }

    public function testCanCompareNetSalaries(): void
    {
        $salary1 = new NetSalary(4000.00, 'USD');
        $salary2 = new NetSalary(3000.00, 'USD');
        $salary3 = new NetSalary(4000.00, 'USD');
        
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
        
        $salary1 = new NetSalary(4000.00, 'USD');
        $salary2 = new NetSalary(3000.00, 'EUR');
        
        $salary1->isGreaterThan($salary2);
    }
}