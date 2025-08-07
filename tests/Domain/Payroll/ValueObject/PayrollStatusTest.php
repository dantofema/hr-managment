<?php

declare(strict_types=1);

namespace App\Tests\Domain\Payroll\ValueObject;

use App\Domain\Payroll\ValueObject\PayrollStatus;
use PHPUnit\Framework\TestCase;

class PayrollStatusTest extends TestCase
{
    public function testCanCreatePendingStatus(): void
    {
        $status = PayrollStatus::pending();
        
        $this->assertEquals('pending', $status->getValue());
        $this->assertTrue($status->isPending());
        $this->assertFalse($status->isProcessed());
        $this->assertFalse($status->isPaid());
    }

    public function testCanCreateProcessedStatus(): void
    {
        $status = PayrollStatus::processed();
        
        $this->assertEquals('processed', $status->getValue());
        $this->assertFalse($status->isPending());
        $this->assertTrue($status->isProcessed());
        $this->assertFalse($status->isPaid());
    }

    public function testCanCreatePaidStatus(): void
    {
        $status = PayrollStatus::paid();
        
        $this->assertEquals('paid', $status->getValue());
        $this->assertFalse($status->isPending());
        $this->assertFalse($status->isProcessed());
        $this->assertTrue($status->isPaid());
    }

    public function testCanCreateFromValidString(): void
    {
        $status = PayrollStatus::fromString('processed');
        
        $this->assertEquals('processed', $status->getValue());
        $this->assertTrue($status->isProcessed());
    }

    public function testThrowsExceptionForInvalidStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid payroll status: invalid_status');
        
        PayrollStatus::fromString('invalid_status');
    }

    public function testCanTransitionFromPendingToProcessed(): void
    {
        $status = PayrollStatus::pending();
        $newStatus = $status->process();
        
        $this->assertTrue($newStatus->isProcessed());
        $this->assertFalse($newStatus->isPending());
    }

    public function testCanTransitionFromProcessedToPaid(): void
    {
        $status = PayrollStatus::processed();
        $newStatus = $status->pay();
        
        $this->assertTrue($newStatus->isPaid());
        $this->assertFalse($newStatus->isProcessed());
    }

    public function testCannotProcessAlreadyProcessedPayroll(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot process payroll that is not pending');
        
        $status = PayrollStatus::processed();
        $status->process();
    }

    public function testCannotPayNonProcessedPayroll(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot pay payroll that is not processed');
        
        $status = PayrollStatus::pending();
        $status->pay();
    }

    public function testStatusEquality(): void
    {
        $status1 = PayrollStatus::pending();
        $status2 = PayrollStatus::pending();
        $status3 = PayrollStatus::processed();
        
        $this->assertTrue($status1->equals($status2));
        $this->assertFalse($status1->equals($status3));
    }

    public function testToString(): void
    {
        $status = PayrollStatus::pending();
        
        $this->assertEquals('pending', (string) $status);
    }
}