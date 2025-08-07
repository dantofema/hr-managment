<?php

declare(strict_types=1);

namespace App\Tests\Domain\Vacation\ValueObject;

use App\Domain\Vacation\ValueObject\VacationPeriod;
use PHPUnit\Framework\TestCase;

class VacationPeriodTest extends TestCase
{
    public function testCanCreateValidVacationPeriod(): void
    {
        $startDate = new \DateTimeImmutable('+1 day');
        $endDate = new \DateTimeImmutable('+5 days');
        
        $period = new VacationPeriod($startDate, $endDate);
        
        $this->assertEquals($startDate, $period->getStartDate());
        $this->assertEquals($endDate, $period->getEndDate());
    }

    public function testThrowsExceptionWhenEndDateIsBeforeStartDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('End date must be after start date');
        
        $startDate = new \DateTimeImmutable('+5 days');
        $endDate = new \DateTimeImmutable('+1 day');
        
        new VacationPeriod($startDate, $endDate);
    }

    public function testThrowsExceptionWhenEndDateEqualsStartDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('End date must be after start date');
        
        $date = new \DateTimeImmutable('+1 day');
        
        new VacationPeriod($date, $date);
    }

    public function testThrowsExceptionWhenStartDateIsInPast(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Vacation cannot start in the past');
        
        $startDate = new \DateTimeImmutable('-1 day');
        $endDate = new \DateTimeImmutable('+1 day');
        
        new VacationPeriod($startDate, $endDate);
    }

    public function testThrowsExceptionWhenPeriodExceeds365Days(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Vacation period cannot exceed 365 days');
        
        $startDate = new \DateTimeImmutable('+1 day');
        $endDate = new \DateTimeImmutable('+367 days');
        
        new VacationPeriod($startDate, $endDate);
    }

    public function testCanCalculateDaysCount(): void
    {
        $startDate = new \DateTimeImmutable('+10 days');
        $endDate = new \DateTimeImmutable('+14 days');
        
        $period = new VacationPeriod($startDate, $endDate);
        
        $this->assertEquals(5, $period->getDaysCount());
    }

    public function testCanCalculateWorkingDaysCount(): void
    {
        // Create a period that starts on a Monday and ends on a Friday
        $monday = new \DateTimeImmutable('next Monday');
        $friday = $monday->modify('+4 days');
        
        $period = new VacationPeriod($monday, $friday);
        
        $this->assertEquals(5, $period->getWorkingDaysCount());
    }

    public function testWorkingDaysCountExcludesWeekends(): void
    {
        // Create a period that includes a weekend
        $monday = new \DateTimeImmutable('next Monday');
        $sunday = $monday->modify('+6 days');
        
        $period = new VacationPeriod($monday, $sunday);
        
        $this->assertEquals(5, $period->getWorkingDaysCount());
    }

    public function testCanCheckIfDateIsInPeriod(): void
    {
        $startDate = new \DateTimeImmutable('+10 days');
        $endDate = new \DateTimeImmutable('+14 days');
        
        $period = new VacationPeriod($startDate, $endDate);
        
        $middleDate = $startDate->modify('+2 days');
        $beforeDate = $startDate->modify('-1 day');
        $afterDate = $endDate->modify('+1 day');
        
        $this->assertTrue($period->contains($middleDate));
        $this->assertTrue($period->contains($startDate));
        $this->assertTrue($period->contains($endDate));
        $this->assertFalse($period->contains($beforeDate));
        $this->assertFalse($period->contains($afterDate));
    }

    public function testCanDetectOverlappingPeriods(): void
    {
        $period1 = new VacationPeriod(
            new \DateTimeImmutable('+10 days'),
            new \DateTimeImmutable('+14 days')
        );
        
        $period2 = new VacationPeriod(
            new \DateTimeImmutable('+12 days'),
            new \DateTimeImmutable('+16 days')
        );
        
        $period3 = new VacationPeriod(
            new \DateTimeImmutable('+20 days'),
            new \DateTimeImmutable('+25 days')
        );
        
        $this->assertTrue($period1->overlaps($period2));
        $this->assertTrue($period2->overlaps($period1));
        $this->assertFalse($period1->overlaps($period3));
        $this->assertFalse($period3->overlaps($period1));
    }

    public function testCanDetectAdjacentPeriodsAsNonOverlapping(): void
    {
        $period1 = new VacationPeriod(
            new \DateTimeImmutable('+10 days'),
            new \DateTimeImmutable('+14 days')
        );
        
        $period2 = new VacationPeriod(
            new \DateTimeImmutable('+15 days'),
            new \DateTimeImmutable('+19 days')
        );
        
        $this->assertFalse($period1->overlaps($period2));
        $this->assertFalse($period2->overlaps($period1));
    }

    public function testCanFormatPeriod(): void
    {
        $startDate = new \DateTimeImmutable('+10 days');
        $endDate = new \DateTimeImmutable('+14 days');
        
        $period = new VacationPeriod($startDate, $endDate);
        
        $expected = $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d') . ' (5 days)';
        $this->assertEquals($expected, $period->format());
    }

    public function testPeriodEquality(): void
    {
        $startDate = new \DateTimeImmutable('+10 days');
        $endDate = new \DateTimeImmutable('+14 days');
        
        $period1 = new VacationPeriod($startDate, $endDate);
        $period2 = new VacationPeriod($startDate, $endDate);
        $period3 = new VacationPeriod($startDate, $startDate->modify('+3 days'));
        
        $this->assertTrue($period1->equals($period2));
        $this->assertFalse($period1->equals($period3));
    }

    public function testToString(): void
    {
        $startDate = new \DateTimeImmutable('+10 days');
        $endDate = new \DateTimeImmutable('+14 days');
        
        $period = new VacationPeriod($startDate, $endDate);
        
        $expected = $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d') . ' (5 days)';
        $this->assertEquals($expected, (string) $period);
    }

    public function testCanCreatePeriodStartingToday(): void
    {
        $today = new \DateTimeImmutable('today');
        $tomorrow = new \DateTimeImmutable('tomorrow');
        
        $period = new VacationPeriod($today, $tomorrow);
        
        $this->assertEquals($today, $period->getStartDate());
        $this->assertEquals($tomorrow, $period->getEndDate());
    }
}