<?php

declare(strict_types=1);

namespace App\Tests\Domain\Payroll\ValueObject;

use App\Domain\Payroll\ValueObject\PayrollPeriod;
use PHPUnit\Framework\TestCase;

class PayrollPeriodTest extends TestCase
{
    public function testCanCreateValidPayrollPeriod(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');
        
        $period = new PayrollPeriod($startDate, $endDate);
        
        $this->assertEquals($startDate, $period->getStartDate());
        $this->assertEquals($endDate, $period->getEndDate());
    }

    public function testThrowsExceptionWhenEndDateIsBeforeStartDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('End date must be after start date');
        
        $startDate = new \DateTimeImmutable('2024-01-31');
        $endDate = new \DateTimeImmutable('2024-01-01');
        
        new PayrollPeriod($startDate, $endDate);
    }

    public function testThrowsExceptionWhenEndDateEqualsStartDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('End date must be after start date');
        
        $date = new \DateTimeImmutable('2024-01-01');
        
        new PayrollPeriod($date, $date);
    }

    public function testCanCalculateDaysInPeriod(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');
        
        $period = new PayrollPeriod($startDate, $endDate);
        
        $this->assertEquals(30, $period->getDaysInPeriod());
    }

    public function testCanCheckIfDateIsInPeriod(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');
        
        $period = new PayrollPeriod($startDate, $endDate);
        
        $this->assertTrue($period->contains(new \DateTimeImmutable('2024-01-15')));
        $this->assertTrue($period->contains($startDate));
        $this->assertTrue($period->contains($endDate));
        $this->assertFalse($period->contains(new \DateTimeImmutable('2023-12-31')));
        $this->assertFalse($period->contains(new \DateTimeImmutable('2024-02-01')));
    }

    public function testCanFormatPeriodAsString(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');
        
        $period = new PayrollPeriod($startDate, $endDate);
        
        $this->assertEquals('2024-01-01 to 2024-01-31', $period->format());
    }

    public function testCanCreateMonthlyPeriod(): void
    {
        $period = PayrollPeriod::forMonth(2024, 1);
        
        $this->assertEquals('2024-01-01', $period->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2024-01-31', $period->getEndDate()->format('Y-m-d'));
    }

    public function testCanCreateBiweeklyPeriod(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $period = PayrollPeriod::biweekly($startDate);
        
        $this->assertEquals('2024-01-01', $period->getStartDate()->format('Y-m-d'));
        $this->assertEquals('2024-01-14', $period->getEndDate()->format('Y-m-d'));
    }

    public function testPeriodEquality(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');
        
        $period1 = new PayrollPeriod($startDate, $endDate);
        $period2 = new PayrollPeriod($startDate, $endDate);
        $period3 = new PayrollPeriod($startDate, new \DateTimeImmutable('2024-01-30'));
        
        $this->assertTrue($period1->equals($period2));
        $this->assertFalse($period1->equals($period3));
    }

    public function testToString(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');
        
        $period = new PayrollPeriod($startDate, $endDate);
        
        $this->assertEquals('2024-01-01 to 2024-01-31', (string) $period);
    }
}