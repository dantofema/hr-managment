<?php

declare(strict_types=1);

namespace App\Domain\Vacation\Entity;

use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\Vacation\ValueObject\VacationStatus;
use App\Domain\Vacation\ValueObject\VacationPeriod;
use DateTimeImmutable;

class Vacation
{
    private VacationStatus $status;
    private ?DateTimeImmutable $approvedAt = null;
    private ?string $rejectionReason = null;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        private Uuid $id,
        private Uuid $employeeId,
        private VacationPeriod $period,
        private string $reason = ''
    ) {
        $this->status = VacationStatus::pending();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function request(
        Uuid $id,
        Uuid $employeeId,
        VacationPeriod $period,
        string $reason = ''
    ): self {
        return new self($id, $employeeId, $period, $reason);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmployeeId(): Uuid
    {
        return $this->employeeId;
    }

    public function getPeriod(): VacationPeriod
    {
        return $this->period;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getStatus(): VacationStatus
    {
        return $this->status;
    }

    public function getApprovedAt(): ?DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function approve(): void
    {
        if (!$this->status->isPending()) {
            throw new \DomainException('Only pending vacation requests can be approved');
        }

        $this->status = $this->status->approve();
        $this->approvedAt = new DateTimeImmutable();
        $this->rejectionReason = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function reject(string $reason): void
    {
        if (!$this->status->isPending()) {
            throw new \DomainException('Only pending vacation requests can be rejected');
        }

        if (empty(trim($reason))) {
            throw new \InvalidArgumentException('Rejection reason is required');
        }

        $this->status = $this->status->reject();
        $this->rejectionReason = $reason;
        $this->approvedAt = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        if ($this->status->isRejected()) {
            throw new \DomainException('Cannot cancel rejected vacation request');
        }

        $this->status = $this->status->cancel();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateReason(string $reason): void
    {
        if (!$this->status->isPending()) {
            throw new \DomainException('Can only update reason for pending vacation requests');
        }

        $this->reason = $reason;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updatePeriod(VacationPeriod $period): void
    {
        if (!$this->status->isPending()) {
            throw new \DomainException('Can only update period for pending vacation requests');
        }

        $this->period = $period;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getDaysRequested(): int
    {
        return $this->period->getDaysCount();
    }

    public function getWorkingDaysRequested(): int
    {
        return $this->period->getWorkingDaysCount();
    }

    public function isActive(): bool
    {
        if (!$this->status->isApproved()) {
            return false;
        }

        $now = new DateTimeImmutable();
        return $this->period->contains($now);
    }

    public function isUpcoming(): bool
    {
        if (!$this->status->isApproved()) {
            return false;
        }

        $now = new DateTimeImmutable();
        return $this->period->getStartDate() > $now;
    }

    public function isPast(): bool
    {
        $now = new DateTimeImmutable();
        return $this->period->getEndDate() < $now;
    }

    public function overlaps(self $other): bool
    {
        return $this->period->overlaps($other->period);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'employee_id' => $this->employeeId->toString(),
            'start_date' => $this->period->getStartDate()->format('Y-m-d'),
            'end_date' => $this->period->getEndDate()->format('Y-m-d'),
            'days_count' => $this->period->getDaysCount(),
            'working_days_count' => $this->period->getWorkingDaysCount(),
            'reason' => $this->reason,
            'status' => $this->status->getValue(),
            'approved_at' => $this->approvedAt?->format('Y-m-d H:i:s'),
            'rejection_reason' => $this->rejectionReason,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}