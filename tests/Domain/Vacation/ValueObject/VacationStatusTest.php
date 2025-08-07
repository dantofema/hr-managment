<?php

declare(strict_types=1);

namespace App\Tests\Domain\Vacation\ValueObject;

use App\Domain\Vacation\ValueObject\VacationStatus;
use PHPUnit\Framework\TestCase;

class VacationStatusTest extends TestCase
{
    public function testCanCreatePendingStatus(): void
    {
        $status = VacationStatus::pending();
        
        $this->assertEquals('pending', $status->getValue());
        $this->assertTrue($status->isPending());
        $this->assertFalse($status->isApproved());
        $this->assertFalse($status->isRejected());
        $this->assertFalse($status->isCancelled());
    }

    public function testCanCreateApprovedStatus(): void
    {
        $status = VacationStatus::approved();
        
        $this->assertEquals('approved', $status->getValue());
        $this->assertFalse($status->isPending());
        $this->assertTrue($status->isApproved());
        $this->assertFalse($status->isRejected());
        $this->assertFalse($status->isCancelled());
    }

    public function testCanCreateRejectedStatus(): void
    {
        $status = VacationStatus::rejected();
        
        $this->assertEquals('rejected', $status->getValue());
        $this->assertFalse($status->isPending());
        $this->assertFalse($status->isApproved());
        $this->assertTrue($status->isRejected());
        $this->assertFalse($status->isCancelled());
    }

    public function testCanCreateCancelledStatus(): void
    {
        $status = VacationStatus::cancelled();
        
        $this->assertEquals('cancelled', $status->getValue());
        $this->assertFalse($status->isPending());
        $this->assertFalse($status->isApproved());
        $this->assertFalse($status->isRejected());
        $this->assertTrue($status->isCancelled());
    }

    public function testCanCreateFromValidString(): void
    {
        $status = VacationStatus::fromString('approved');
        
        $this->assertEquals('approved', $status->getValue());
        $this->assertTrue($status->isApproved());
    }

    public function testThrowsExceptionForInvalidStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid vacation status: invalid_status');
        
        VacationStatus::fromString('invalid_status');
    }

    public function testCanTransitionFromPendingToApproved(): void
    {
        $status = VacationStatus::pending();
        $newStatus = $status->approve();
        
        $this->assertTrue($newStatus->isApproved());
        $this->assertFalse($newStatus->isPending());
    }

    public function testCanTransitionFromPendingToRejected(): void
    {
        $status = VacationStatus::pending();
        $newStatus = $status->reject();
        
        $this->assertTrue($newStatus->isRejected());
        $this->assertFalse($newStatus->isPending());
    }

    public function testCanTransitionFromPendingToCancelled(): void
    {
        $status = VacationStatus::pending();
        $newStatus = $status->cancel();
        
        $this->assertTrue($newStatus->isCancelled());
        $this->assertFalse($newStatus->isPending());
    }

    public function testCanTransitionFromApprovedToCancelled(): void
    {
        $status = VacationStatus::approved();
        $newStatus = $status->cancel();
        
        $this->assertTrue($newStatus->isCancelled());
        $this->assertFalse($newStatus->isApproved());
    }

    public function testCannotApproveAlreadyApprovedVacation(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot approve vacation that is not pending');
        
        $status = VacationStatus::approved();
        $status->approve();
    }

    public function testCannotRejectAlreadyRejectedVacation(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot reject vacation that is not pending');
        
        $status = VacationStatus::rejected();
        $status->reject();
    }

    public function testCannotApproveRejectedVacation(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot approve vacation that is not pending');
        
        $status = VacationStatus::rejected();
        $status->approve();
    }

    public function testCannotRejectApprovedVacation(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot reject vacation that is not pending');
        
        $status = VacationStatus::approved();
        $status->reject();
    }

    public function testCannotCancelRejectedVacation(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot cancel rejected vacation');
        
        $status = VacationStatus::rejected();
        $status->cancel();
    }

    public function testStatusEquality(): void
    {
        $status1 = VacationStatus::pending();
        $status2 = VacationStatus::pending();
        $status3 = VacationStatus::approved();
        
        $this->assertTrue($status1->equals($status2));
        $this->assertFalse($status1->equals($status3));
    }

    public function testToString(): void
    {
        $status = VacationStatus::pending();
        
        $this->assertEquals('pending', (string) $status);
    }
}