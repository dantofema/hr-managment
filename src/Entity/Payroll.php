<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Domain\Payroll\Entity\Payroll as DomainPayroll;
use App\Domain\Payroll\ValueObject\PayrollPeriod;
use App\Domain\Payroll\ValueObject\GrossSalary;
use App\Domain\Payroll\ValueObject\NetSalary;
use App\Domain\Payroll\ValueObject\Deductions;
use App\Domain\Payroll\ValueObject\PayrollStatus;
use App\Domain\Shared\ValueObject\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'payrolls')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['payroll:read']],
    denormalizationContext: ['groups' => ['payroll:write']]
)]
class Payroll
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[Groups(['payroll:read'])]
    private string $id;

    #[ORM\Column(type: 'string', length: 36)]
    #[Groups(['payroll:read', 'payroll:write'])]
    private string $employeeId;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['payroll:read', 'payroll:write'])]
    private DateTimeImmutable $periodStartDate;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['payroll:read', 'payroll:write'])]
    private DateTimeImmutable $periodEndDate;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['payroll:read', 'payroll:write'])]
    private float $grossSalary;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['payroll:read', 'payroll:write'])]
    private float $taxes;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['payroll:read', 'payroll:write'])]
    private float $socialSecurity;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['payroll:read', 'payroll:write'])]
    private float $healthInsurance;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['payroll:read', 'payroll:write'])]
    private float $otherDeductions;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['payroll:read'])]
    private float $netSalary;

    #[ORM\Column(type: 'string', length: 3)]
    #[Groups(['payroll:read', 'payroll:write'])]
    private string $currency;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['payroll:read', 'payroll:write'])]
    private string $status;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['payroll:read'])]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['payroll:read'])]
    private ?DateTimeImmutable $processedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['payroll:read'])]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->id = Uuid::generate()->value();
        $this->createdAt = new DateTimeImmutable();
        $this->currency = 'USD';
        $this->status = 'pending';
        $this->taxes = 0.0;
        $this->socialSecurity = 0.0;
        $this->healthInsurance = 0.0;
        $this->otherDeductions = 0.0;
    }

    public function toDomain(): DomainPayroll
    {
        $period = new PayrollPeriod($this->periodStartDate, $this->periodEndDate);
        $grossSalary = new GrossSalary($this->grossSalary, $this->currency);
        $deductions = new Deductions(
            $this->taxes,
            $this->socialSecurity,
            $this->healthInsurance,
            $this->otherDeductions,
            $this->currency
        );

        $domainPayroll = new DomainPayroll(
            Uuid::fromString($this->id),
            Uuid::fromString($this->employeeId),
            $period,
            $grossSalary,
            $deductions
        );

        return $domainPayroll;
    }

    public static function fromDomain(DomainPayroll $domainPayroll): self
    {
        $payroll = new self();
        $payroll->id = $domainPayroll->getId()->value();
        $payroll->employeeId = $domainPayroll->getEmployeeId()->value();
        $payroll->periodStartDate = $domainPayroll->getPeriod()->startDate();
        $payroll->periodEndDate = $domainPayroll->getPeriod()->endDate();
        $payroll->grossSalary = $domainPayroll->getGrossSalary()->amount();
        $payroll->taxes = $domainPayroll->getDeductions()->taxes();
        $payroll->socialSecurity = $domainPayroll->getDeductions()->socialSecurity();
        $payroll->healthInsurance = $domainPayroll->getDeductions()->healthInsurance();
        $payroll->otherDeductions = $domainPayroll->getDeductions()->otherDeductions();
        $payroll->netSalary = $domainPayroll->getNetSalary()->amount();
        $payroll->currency = $domainPayroll->getGrossSalary()->currency();
        $payroll->status = $domainPayroll->getStatus()->value();
        $payroll->createdAt = $domainPayroll->getCreatedAt();
        $payroll->processedAt = $domainPayroll->getProcessedAt();
        $payroll->updatedAt = $domainPayroll->getUpdatedAt();

        return $payroll;
    }

    // Getters and setters for API Platform
    public function getId(): string
    {
        return $this->id;
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    public function setEmployeeId(string $employeeId): void
    {
        $this->employeeId = $employeeId;
        $this->calculateNetSalary();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getPeriodStartDate(): DateTimeImmutable
    {
        return $this->periodStartDate;
    }

    public function setPeriodStartDate(DateTimeImmutable $periodStartDate): void
    {
        $this->periodStartDate = $periodStartDate;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getPeriodEndDate(): DateTimeImmutable
    {
        return $this->periodEndDate;
    }

    public function setPeriodEndDate(DateTimeImmutable $periodEndDate): void
    {
        $this->periodEndDate = $periodEndDate;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getGrossSalary(): float
    {
        return $this->grossSalary;
    }

    public function setGrossSalary(float $grossSalary): void
    {
        $this->grossSalary = $grossSalary;
        $this->calculateNetSalary();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getTaxes(): float
    {
        return $this->taxes;
    }

    public function setTaxes(float $taxes): void
    {
        $this->taxes = $taxes;
        $this->calculateNetSalary();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getSocialSecurity(): float
    {
        return $this->socialSecurity;
    }

    public function setSocialSecurity(float $socialSecurity): void
    {
        $this->socialSecurity = $socialSecurity;
        $this->calculateNetSalary();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getHealthInsurance(): float
    {
        return $this->healthInsurance;
    }

    public function setHealthInsurance(float $healthInsurance): void
    {
        $this->healthInsurance = $healthInsurance;
        $this->calculateNetSalary();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getOtherDeductions(): float
    {
        return $this->otherDeductions;
    }

    public function setOtherDeductions(float $otherDeductions): void
    {
        $this->otherDeductions = $otherDeductions;
        $this->calculateNetSalary();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getNetSalary(): float
    {
        return $this->netSalary;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
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

    private function calculateNetSalary(): void
    {
        $totalDeductions = $this->taxes + $this->socialSecurity + $this->healthInsurance + $this->otherDeductions;
        $this->netSalary = $this->grossSalary - $totalDeductions;
    }
}