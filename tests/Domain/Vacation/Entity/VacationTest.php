<?php

declare(strict_types=1);

namespace App\Tests\Domain\Vacation\Entity;

use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\Vacation\Vacation;
use App\Domain\Vacation\ValueObject\VacationPeriod;
use App\Domain\Vacation\ValueObject\VacationStatus;
use PHPUnit\Framework\TestCase;

class VacationTest extends TestCase
{
    private Uuid $vacationId;
    private Uuid $employeeId;
    private VacationPeriod $period;

    protected function setUp(): void
    {
        $this->vacationId = Uuid::generate();
        $this->employeeId = Uuid::generate();
        $this->period = new VacationPeriod(
            new \DateTimeImmutable('+1 day'),
            new \DateTimeImmutable('+5 days')
        );
    }

    public function testCanCreateVacationRequest(): void
    {
        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $this->assertEquals($this->vacationId, $vacation->getId());
        $this->assertEquals($this->employeeId, $vacation->getEmployeeId());
        $this->assertEquals($this->period, $vacation->getPeriod());
        $this->assertEquals('Family vacation', $vacation->getReason());
        $this->assertTrue($vacation->getStatus()->isPending());
        $this->assertNull($vacation->getApprovedAt());
        $this->assertNull($vacation->getRejectionReason());
        $this->assertInstanceOf(\DateTimeImmutable::class, $vacation->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $vacation->getUpdatedAt());
    }

    public function testCanCreateVacationRequestWithoutReason(): void
    {
        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period
        );

        $this->assertEquals('', $vacation->getReason());
    }

    public function testCanApproveVacationRequest(): void
    {
        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $vacation->approve();

        $this->assertTrue($vacation->getStatus()->isApproved());
        $this->assertInstanceOf(\DateTimeImmutable::class, $vacation->getApprovedAt());
        $this->assertNull($vacation->getRejectionReason());
    }

    public function testCanRejectVacationRequest(): void
    {
        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $vacation->reject('Insufficient vacation days');

        $this->assertTrue($vacation->getStatus()->isRejected());
        $this->assertEquals('Insufficient vacation days', $vacation->getRejectionReason());
        $this->assertNull($vacation->getApprovedAt());
    }

    public function testCanCancelVacationRequest(): void
    {
        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $vacation->cancel();

        $this->assertTrue($vacation->getStatus()->isCancelled());
    }

    public function testCanCancelApprovedVacation(): void
    {
        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $vacation->approve();
        $vacation->cancel();

        $this->assertTrue($vacation->getStatus()->isCancelled());
    }

    public function testCannotApproveNonPendingVacation(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Only pending vacation requests can be approved');

        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $vacation->approve();
        $vacation->approve(); // Should throw exception
    }

    public function testCannotRejectNonPendingVacation(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Only pending vacation requests can be rejected');

        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $vacation->approve();
        $vacation->reject('Some reason'); // Should throw exception
    }

    public function testCannotRejectWithEmptyReason(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rejection reason is required');

        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $vacation->reject('');
    }

    public function testCannotCancelRejectedVacation(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot cancel rejected vacation request');

        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $vacation->reject('Some reason');
        $vacation->cancel(); // Should throw exception
    }

    public function testCanUpdateReasonForPendingVacation(): void
    {
        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $vacation->updateReason('Medical leave');

        $this->assertEquals('Medical leave', $vacation->getReason());
    }

    public function testCannotUpdateReasonForNonPendingVacation(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Can only update reason for pending vacation requests');

        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $vacation->approve();
        $vacation->updateReason('Medical leave'); // Should throw exception
    }

    public function testCanUpdatePeriodForPendingVacation(): void
    {
        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $newPeriod = new VacationPeriod(
            new \DateTimeImmutable('+10 days'),
            new \DateTimeImmutable('+15 days')
        );

        $vacation->updatePeriod($newPeriod);

        $this->assertEquals($newPeriod, $vacation->getPeriod());
    }

    public function testCannotUpdatePeriodForNonPendingVacation(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Can only update period for pending vacation requests');

        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $vacation->approve();
        
        $newPeriod = new VacationPeriod(
            new \DateTimeImmutable('+10 days'),
            new \DateTimeImmutable('+15 days')
        );
        
        $vacation->updatePeriod($newPeriod); // Should throw exception
    }

    public function testCanGetDaysRequested(): void
    {
        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $this->assertEquals($this->period->getDaysCount(), $vacation->getDaysRequested());
    }

    public function testCanGetWorkingDaysRequested(): void
    {
        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $this->assertEquals($this->period->getWorkingDaysCount(), $vacation->getWorkingDaysRequested());
    }

    public function testCanDetectActiveVacation(): void
    {
        $activePeriod = new VacationPeriod(
            new \DateTimeImmutable('today'),
            new \DateTimeImmutable('+2 days')
        );

        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $activePeriod,
            'Family vacation'
        );

        $vacation->approve();

        $this->assertTrue($vacation->isActive());
        $this->assertFalse($vacation->isUpcoming());
        $this->assertFalse($vacation->isPast());
    }

    public function testCanDetectUpcomingVacation(): void
    {
        $upcomingPeriod = new VacationPeriod(
            new \DateTimeImmutable('+10 days'),
            new \DateTimeImmutable('+15 days')
        );

        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $upcomingPeriod,
            'Family vacation'
        );

        $vacation->approve();

        $this->assertFalse($vacation->isActive());
        $this->assertTrue($vacation->isUpcoming());
        $this->assertFalse($vacation->isPast());
    }

    public function testCanDetectPastVacation(): void
    {
        $pastPeriod = new VacationPeriod(
            new \DateTimeImmutable('+20 days'),
            new \DateTimeImmutable('+25 days')
        );

        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $pastPeriod,
            'Family vacation'
        );

        $vacation->approve();

        $this->assertFalse($vacation->isActive());
        $this->assertTrue($vacation->isUpcoming());
        $this->assertFalse($vacation->isPast());
    }

    public function testNonApprovedVacationIsNotActive(): void
    {
        $activePeriod = new VacationPeriod(
            new \DateTimeImmutable('today'),
            new \DateTimeImmutable('+2 days')
        );

        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $activePeriod,
            'Family vacation'
        );

        // Not approved, so should not be active
        $this->assertFalse($vacation->isActive());
        $this->assertFalse($vacation->isUpcoming());
    }

    public function testCanDetectOverlappingVacations(): void
    {
        $vacation1 = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $overlappingPeriod = new VacationPeriod(
            new \DateTimeImmutable('+3 days'),
            new \DateTimeImmutable('+7 days')
        );

        $vacation2 = Vacation::request(
            Uuid::generate(),
            $this->employeeId,
            $overlappingPeriod,
            'Medical leave'
        );

        $this->assertTrue($vacation1->overlaps($vacation2));
        $this->assertTrue($vacation2->overlaps($vacation1));
    }

    public function testCanConvertToArray(): void
    {
        $vacation = Vacation::request(
            $this->vacationId,
            $this->employeeId,
            $this->period,
            'Family vacation'
        );

        $array = $vacation->toArray();

        $this->assertIsArray($array);
        $this->assertEquals($this->vacationId->toString(), $array['id']);
        $this->assertEquals($this->employeeId->toString(), $array['employee_id']);
        $this->assertEquals($this->period->getStartDate()->format('Y-m-d'), $array['start_date']);
        $this->assertEquals($this->period->getEndDate()->format('Y-m-d'), $array['end_date']);
        $this->assertEquals('Family vacation', $array['reason']);
        $this->assertEquals('pending', $array['status']);
        $this->assertNull($array['approved_at']);
        $this->assertNull($array['rejection_reason']);
        $this->assertIsString($array['created_at']);
        $this->assertIsString($array['updated_at']);
    }
}