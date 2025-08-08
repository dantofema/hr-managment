<?php

declare(strict_types=1);

namespace App\Domain\Payroll;

use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\Payroll\ValueObject\PayrollPeriod;
use App\Domain\Payroll\ValueObject\GrossSalary;
use App\Domain\Payroll\ValueObject\NetSalary;
use App\Domain\Payroll\ValueObject\Deductions;
use App\Domain\Payroll\ValueObject\PayrollStatus;
use DateTimeImmutable;

class Payroll
{
    private Uuid $id;
    private Uuid $employeeId;
    private PayrollPeriod $period;
    private GrossSalary $grossSalary;
    private Deductions $deductions;
    private NetSalary $netSalary;
    private PayrollStatus $status;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $processedAt = null;
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct(
        Uuid $id,
        Uuid $employeeId,
        PayrollPeriod $period,
        GrossSalary $grossSalary,
        Deductions $deductions
    ) {
        $this->id = $id;
        $this->employeeId = $employeeId;
        $this->period = $period;
        $this->grossSalary = $grossSalary;
        $this->deductions = $deductions;
        $this->netSalary = $this->calculateNetSalary();
        $this->status = PayrollStatus::pending();
        $this->createdAt = new DateTimeImmutable();
    }

    public static function create(
        Uuid $employeeId,
        PayrollPeriod $period,
        GrossSalary $grossSalary,
        Deductions $deductions
    ): self {
        return new self(
            Uuid::generate(),
            $employeeId,
            $period,
            $grossSalary,
            $deductions
        );
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmployeeId(): Uuid
    {
        return $this->employeeId;
    }

    public function getPeriod(): PayrollPeriod
    {
        return $this->period;
    }

    public function getGrossSalary(): GrossSalary
    {
        return $this->grossSalary;
    }

    public function getDeductions(): Deductions
    {
        return $this->deductions;
    }

    public function getNetSalary(): NetSalary
    {
        return $this->netSalary;
    }

    public function getStatus(): PayrollStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getProcessedAt(): ?DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function process(): void
    {
        if (!$this->status->isPending()) {
            throw new \DomainException('Payroll can only be processed when in pending status');
        }

        $this->status = PayrollStatus::processed();
        $this->processedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        if ($this->status->isProcessed()) {
            throw new \DomainException('Cannot cancel a processed payroll');
        }

        $this->status = PayrollStatus::cancelled();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateDeductions(Deductions $deductions): void
    {
        if (!$this->status->isPending()) {
            throw new \DomainException('Cannot update deductions for non-pending payroll');
        }

        $this->deductions = $deductions;
        $this->netSalary = $this->calculateNetSalary();
        $this->updatedAt = new DateTimeImmutable();
    }

    private function calculateNetSalary(): NetSalary
    {
        $netAmount = $this->grossSalary->getAmount() - $this->deductions->getTotal();
        
        return new NetSalary($netAmount, $this->grossSalary->getCurrency());
    }
}