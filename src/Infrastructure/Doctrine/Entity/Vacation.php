<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Domain\Vacation\Vacation as DomainVacation;
use App\Domain\Shared\ValueObject\Uuid;
use App\Domain\Vacation\ValueObject\VacationPeriod;
use App\Domain\Vacation\ValueObject\VacationStatus;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'vacations')]
class Vacation
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    #[ORM\JoinColumn(name: 'employee_id', referencedColumnName: 'id', nullable: false)]
    private Employee $employee;

    #[ORM\Column(type: 'date_immutable')]
    private DateTimeImmutable $startDate;

    #[ORM\Column(type: 'date_immutable')]
    private DateTimeImmutable $endDate;

    #[ORM\Column(type: 'text')]
    private string $reason;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'pending';

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $approvedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $rejectionReason = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function fromDomain(DomainVacation $vacation, Employee $employee): self
    {
        $entity = new self();
        $entity->id = $vacation->getId()->toString();
        $entity->employee = $employee;
        $entity->startDate = $vacation->getPeriod()->getStartDate();
        $entity->endDate = $vacation->getPeriod()->getEndDate();
        $entity->reason = $vacation->getReason();
        $entity->status = $vacation->getStatus()->getValue();
        $entity->approvedAt = $vacation->getApprovedAt();
        $entity->rejectionReason = $vacation->getRejectionReason();
        $entity->createdAt = $vacation->getCreatedAt();
        $entity->updatedAt = $vacation->getUpdatedAt();

        return $entity;
    }

    public function toDomain(): DomainVacation
    {
        $vacation = new DomainVacation(
            new Uuid($this->id),
            new Uuid($this->employee->getId()),
            new VacationPeriod($this->startDate, $this->endDate),
            $this->reason
        );

        // Set private properties using reflection
        $reflection = new \ReflectionClass($vacation);
        
        $statusProperty = $reflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($vacation, VacationStatus::fromString($this->status));

        $approvedAtProperty = $reflection->getProperty('approvedAt');
        $approvedAtProperty->setAccessible(true);
        $approvedAtProperty->setValue($vacation, $this->approvedAt);

        $rejectionReasonProperty = $reflection->getProperty('rejectionReason');
        $rejectionReasonProperty->setAccessible(true);
        $rejectionReasonProperty->setValue($vacation, $this->rejectionReason);

        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($vacation, $this->createdAt);

        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($vacation, $this->updatedAt);

        return $vacation;
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

    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    public function setEmployee(Employee $employee): self
    {
        $this->employee = $employee;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getEmployeeId(): string
    {
        return $this->employee->getId();
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getApprovedAt(): ?DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(?DateTimeImmutable $approvedAt): self
    {
        $this->approvedAt = $approvedAt;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(?string $rejectionReason): self
    {
        $this->rejectionReason = $rejectionReason;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}