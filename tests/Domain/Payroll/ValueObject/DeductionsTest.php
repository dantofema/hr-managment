<?php

declare(strict_types=1);

namespace App\Tests\Domain\Payroll\ValueObject;

use App\Domain\Payroll\ValueObject\Deductions;
use PHPUnit\Framework\TestCase;

class DeductionsTest extends TestCase
{
    public function testCanCreateValidDeductions(): void
    {
        $deductions = new Deductions(500.00, 200.00, 100.00, 'USD');
        
        $this->assertEquals(500.00, $deductions->getTaxes());
        $this->assertEquals(200.00, $deductions->getSocialSecurity());
        $this->assertEquals(100.00, $deductions->getHealthInsurance());
        $this->assertEquals('USD', $deductions->getCurrency());
        $this->assertEquals(800.00, $deductions->getTotal());
    }

    public function testCanCreateDeductionsWithZeroValues(): void
    {
        $deductions = new Deductions(0.00, 0.00, 0.00, 'USD');
        
        $this->assertEquals(0.00, $deductions->getTaxes());
        $this->assertEquals(0.00, $deductions->getSocialSecurity());
        $this->assertEquals(0.00, $deductions->getHealthInsurance());
        $this->assertEquals(0.00, $deductions->getTotal());
    }

    public function testThrowsExceptionForNegativeTaxes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Taxes cannot be negative');
        
        new Deductions(-100.00, 200.00, 100.00, 'USD');
    }

    public function testThrowsExceptionForNegativeSocialSecurity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Social security cannot be negative');
        
        new Deductions(500.00, -200.00, 100.00, 'USD');
    }

    public function testThrowsExceptionForNegativeHealthInsurance(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Health insurance cannot be negative');
        
        new Deductions(500.00, 200.00, -100.00, 'USD');
    }

    public function testThrowsExceptionForInvalidCurrency(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid currency code: INVALID');
        
        new Deductions(500.00, 200.00, 100.00, 'INVALID');
    }

    public function testAcceptsValidCurrencyCodes(): void
    {
        $validCurrencies = ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY'];
        
        foreach ($validCurrencies as $currency) {
            $deductions = new Deductions(500.00, 200.00, 100.00, $currency);
            $this->assertEquals($currency, $deductions->getCurrency());
        }
    }

    public function testCanAddDeductions(): void
    {
        $deductions1 = new Deductions(300.00, 150.00, 50.00, 'USD');
        $deductions2 = new Deductions(200.00, 50.00, 50.00, 'USD');
        
        $result = $deductions1->add($deductions2);
        
        $this->assertEquals(500.00, $result->getTaxes());
        $this->assertEquals(200.00, $result->getSocialSecurity());
        $this->assertEquals(100.00, $result->getHealthInsurance());
        $this->assertEquals(800.00, $result->getTotal());
        $this->assertEquals('USD', $result->getCurrency());
    }

    public function testCannotAddDifferentCurrencies(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot add different currencies: USD and EUR');
        
        $deductions1 = new Deductions(300.00, 150.00, 50.00, 'USD');
        $deductions2 = new Deductions(200.00, 50.00, 50.00, 'EUR');
        
        $deductions1->add($deductions2);
    }

    public function testCanCalculatePercentageOfGrossSalary(): void
    {
        $deductions = new Deductions(500.00, 200.00, 100.00, 'USD');
        $grossSalary = 4000.00;
        
        $percentage = $deductions->getPercentageOf($grossSalary);
        
        $this->assertEquals(20.0, $percentage); // 800/4000 * 100
    }

    public function testThrowsExceptionForZeroGrossSalaryInPercentage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Gross salary must be positive');
        
        $deductions = new Deductions(500.00, 200.00, 100.00, 'USD');
        $deductions->getPercentageOf(0.00);
    }

    public function testCanFormatDeductions(): void
    {
        $deductions = new Deductions(500.50, 200.25, 100.75, 'USD');
        
        $expected = 'Taxes: 500.50 USD, Social Security: 200.25 USD, Health Insurance: 100.75 USD, Total: 801.50 USD';
        $this->assertEquals($expected, $deductions->format());
    }

    public function testDeductionsEquality(): void
    {
        $deductions1 = new Deductions(500.00, 200.00, 100.00, 'USD');
        $deductions2 = new Deductions(500.00, 200.00, 100.00, 'USD');
        $deductions3 = new Deductions(400.00, 200.00, 100.00, 'USD');
        $deductions4 = new Deductions(500.00, 200.00, 100.00, 'EUR');
        
        $this->assertTrue($deductions1->equals($deductions2));
        $this->assertFalse($deductions1->equals($deductions3));
        $this->assertFalse($deductions1->equals($deductions4));
    }

    public function testToString(): void
    {
        $deductions = new Deductions(500.50, 200.25, 100.75, 'USD');
        
        $expected = 'Taxes: 500.50 USD, Social Security: 200.25 USD, Health Insurance: 100.75 USD, Total: 801.50 USD';
        $this->assertEquals($expected, (string) $deductions);
    }

    public function testCanCreateFromArray(): void
    {
        $data = [
            'taxes' => 500.00,
            'social_security' => 200.00,
            'health_insurance' => 100.00,
            'currency' => 'USD'
        ];
        
        $deductions = Deductions::fromArray($data);
        
        $this->assertEquals(500.00, $deductions->getTaxes());
        $this->assertEquals(200.00, $deductions->getSocialSecurity());
        $this->assertEquals(100.00, $deductions->getHealthInsurance());
        $this->assertEquals('USD', $deductions->getCurrency());
    }

    public function testCanConvertToArray(): void
    {
        $deductions = new Deductions(500.00, 200.00, 100.00, 'USD');
        
        $expected = [
            'taxes' => 500.00,
            'social_security' => 200.00,
            'health_insurance' => 100.00,
            'total' => 800.00,
            'currency' => 'USD'
        ];
        
        $this->assertEquals($expected, $deductions->toArray());
    }
}