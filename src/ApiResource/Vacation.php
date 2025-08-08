<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Domain\Vacation\Vacation as DomainVacation;
use App\Domain\Employee\ValueObject\Uuid;
use App\Domain\Vacation\ValueObject\VacationPeriod;
use App\Domain\Vacation\ValueObject\VacationStatus;

#[ORM\Entity]
#[ORM\Table(name: 'vacations')]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['vacation:read']],
        ),
        new Get(
            normalizationContext: ['groups' => ['vacation:read', 'vacation:item']],
        ),
        new Post(
            denormalizationContext: ['groups' => ['vacation:write']],
            normalizationContext: ['groups' => ['vacation:read']],
        ),
        new Put(
            denormalizationContext: ['groups' => ['vacation:write']],
            normalizationContext: ['groups' => ['vacation:read']],
        ),
        new Patch(
            denormalizationContext: ['groups' => ['vacation:write']],
            normalizationContext: ['groups' => ['vacation:read']],
        ),
        new Delete(),
    ]
)]
class Vacation
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[Groups(['vacation:read'])]
    private string $id;

    #[ORM\Column(type: 'string', length: 36)]
    #[Groups(['vacation:read', 'vacation:write'])]
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private string $employeeId;

    #[ORM\Column(type: 'date_immutable')]
    #[Groups(['vacation:read', 'vacation:write'])]
    #[Assert\NotNull]
    #[Assert\Type(\DateTimeImmutable::class)]
    private \DateTimeImmutable $startDate;

    #[ORM\Column(type: 'date_immutable')]
    #[Groups(['vacation:read', 'vacation:write'])]
    #[Assert\NotNull]
    #[Assert\Type(\DateTimeImmutable::class)]
    private \DateTimeImmutable $endDate;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['vacation:read', 'vacation:write'])]
    private ?string $reason = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['vacation:read'])]
    private string $status = 'pending';

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['vacation:read'])]
    private ?\DateTimeImmutable $approvedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['vacation:read'])]
    private ?string $rejectionReason = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['vacation:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['vacation:read'])]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    public function setEmployeeId(string $employeeId): self
    {
        $this->employeeId = $employeeId;
        return $this;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getApprovedAt(): ?\DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(?\DateTimeImmutable $approvedAt): self
    {
        $this->approvedAt = $approvedAt;
        return $this;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(?string $rejectionReason): self
    {
        $this->rejectionReason = $rejectionReason;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[Groups(['vacation:read'])]
    public function getDaysCount(): int
    {
        return $this->startDate->diff($this->endDate)->days + 1;
    }

    #[Groups(['vacation:read'])]
    public function getWorkingDaysCount(): int
    {
        $workingDays = 0;
        $current = $this->startDate;

        while ($current <= $this->endDate) {
            $dayOfWeek = (int) $current->format('N');
            if ($dayOfWeek < 6) { // Monday to Friday (1-5)
                $workingDays++;
            }
            $current = $current->modify('+1 day');
        }

        return $workingDays;
    }

    public function approve(): self
    {
        if ($this->status !== 'pending') {
            throw new \DomainException('Only pending vacation requests can be approved');
        }

        $this->status = 'approved';
        $this->approvedAt = new \DateTimeImmutable();
        $this->rejectionReason = null;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function reject(string $reason): self
    {
        if ($this->status !== 'pending') {
            throw new \DomainException('Only pending vacation requests can be rejected');
        }

        if (empty(trim($reason))) {
            throw new \InvalidArgumentException('Rejection reason is required');
        }

        $this->status = 'rejected';
        $this->rejectionReason = $reason;
        $this->approvedAt = null;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function cancel(): self
    {
        if ($this->status === 'rejected') {
            throw new \DomainException('Cannot cancel rejected vacation request');
        }

        $this->status = 'cancelled';
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public static function fromDomain(DomainVacation $vacation): self
    {
        $apiVacation = new self();
        $apiVacation->id = $vacation->getId()->toString();
        $apiVacation->employeeId = $vacation->getEmployeeId()->toString();
        $apiVacation->startDate = $vacation->getPeriod()->getStartDate();
        $apiVacation->endDate = $vacation->getPeriod()->getEndDate();
        $apiVacation->reason = $vacation->getReason();
        $apiVacation->status = $vacation->getStatus()->getValue();
        $apiVacation->approvedAt = $vacation->getApprovedAt();
        $apiVacation->rejectionReason = $vacation->getRejectionReason();
        $apiVacation->createdAt = $vacation->getCreatedAt();
        $apiVacation->updatedAt = $vacation->getUpdatedAt();

        return $apiVacation;
    }

    public function toDomain(): DomainVacation
    {
        $vacation = new DomainVacation(
            Uuid::fromString($this->id),
            Uuid::fromString($this->employeeId),
            new VacationPeriod($this->startDate, $this->endDate),
            $this->reason ?? ''
        );

        // Use reflection to set private properties since we need to restore state
        $reflection = new \ReflectionClass($vacation);
        
        $statusProperty = $reflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($vacation, VacationStatus::fromString($this->status));

        if ($this->approvedAt) {
            $approvedAtProperty = $reflection->getProperty('approvedAt');
            $approvedAtProperty->setAccessible(true);
            $approvedAtProperty->setValue($vacation, $this->approvedAt);
        }

        if ($this->rejectionReason) {
            $rejectionReasonProperty = $reflection->getProperty('rejectionReason');
            $rejectionReasonProperty->setAccessible(true);
            $rejectionReasonProperty->setValue($vacation, $this->rejectionReason);
        }

        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($vacation, $this->createdAt);

        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($vacation, $this->updatedAt);

        return $vacation;
    }
}