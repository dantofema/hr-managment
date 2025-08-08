<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Entity;

use App\Domain\Payroll\Payroll as DomainPayroll;
use App\Domain\Employee\ValueObject\Uuid;
use App\Domain\Payroll\ValueObject\PayrollPeriod;
use App\Domain\Payroll\ValueObject\GrossSalary;
use App\Domain\Payroll\ValueObject\Deductions;
use App\Domain\Payroll\ValueObject\PayrollStatus;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'payrolls')]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['payroll:read']],
        ),
        new Get(
            normalizationContext: ['groups' => ['payroll:read', 'payroll:item']],
        ),
        new Post(
            denormalizationContext: ['groups' => ['payroll:write']],
            normalizationContext: ['groups' => ['payroll:read']],
        ),
        new Put(
            denormalizationContext: ['groups' => ['payroll:write']],
            normalizationContext: ['groups' => ['payroll:read']],
        ),
        new Patch(
            denormalizationContext: ['groups' => ['payroll:write']],
            normalizationContext: ['groups' => ['payroll:read']],
        ),
        new Delete(),
    ],
    paginationEnabled: true,
    paginationItemsPerPage: 20
)]
class Payroll
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[Groups(['payroll:read'])]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    #[ORM\JoinColumn(name: 'employee_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['payroll:read', 'payroll:write'])]
    #[Assert\NotNull]
    private Employee $employee;

    #[ORM\Column(type: 'date_immutable')]
    #[Groups(['payroll:read', 'payroll:write'])]
    #[Assert\NotNull]
    #[Assert\Type(\DateTimeImmutable::class)]
    private DateTimeImmutable $startDate;

    #[ORM\Column(type: 'date_immutable')]
    #[Groups(['payroll:read', 'payroll:write'])]
    #[Assert\NotNull]
    #[Assert\Type(\DateTimeImmutable::class)]
    private DateTimeImmutable $endDate;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['payroll:read', 'payroll:write'])]
    #[Assert\NotNull]
    #[Assert\Positive]
    private string $grossSalaryAmount;

    #[ORM\Column(type: 'string', length: 3)]
    #[Groups(['payroll:read', 'payroll:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(exactly: 3)]
    private string $grossSalaryCurrency;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['payroll:read', 'payroll:write'])]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private string $taxesAmount;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['payroll:read', 'payroll:write'])]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private string $socialSecurityAmount;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['payroll:read', 'payroll:write'])]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private string $healthInsuranceAmount;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['payroll:read'])]
    private string $netSalaryAmount;

    #[ORM\Column(type: 'string', length: 3)]
    #[Groups(['payroll:read'])]
    private string $netSalaryCurrency;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['payroll:read'])]
    private string $status = 'pending';

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
        $this->createdAt = new DateTimeImmutable();
    }

    public static function fromDomain(DomainPayroll $payroll, Employee $employee): self
    {
        $entity = new self();
        $entity->id = $payroll->getId()->toString();
        $entity->employee = $employee;
        $entity->startDate = $payroll->getPeriod()->getStartDate();
        $entity->endDate = $payroll->getPeriod()->getEndDate();
        $entity->grossSalaryAmount = (string) $payroll->getGrossSalary()->getAmount();
        $entity->grossSalaryCurrency = $payroll->getGrossSalary()->getCurrency();
        $entity->taxesAmount = (string) $payroll->getDeductions()->getTaxes();
        $entity->socialSecurityAmount = (string) $payroll->getDeductions()->getSocialSecurity();
        $entity->healthInsuranceAmount = (string) $payroll->getDeductions()->getHealthInsurance();
        $entity->netSalaryAmount = (string) $payroll->getNetSalary()->getAmount();
        $entity->netSalaryCurrency = $payroll->getNetSalary()->getCurrency();
        $entity->status = $payroll->getStatus()->getValue();
        $entity->createdAt = $payroll->getCreatedAt();
        $entity->processedAt = $payroll->getProcessedAt();
        $entity->updatedAt = $payroll->getUpdatedAt();

        return $entity;
    }

    public function toDomain(): DomainPayroll
    {
        $period = new PayrollPeriod($this->startDate, $this->endDate);
        $grossSalary = new GrossSalary((float) $this->grossSalaryAmount, $this->grossSalaryCurrency);
        $deductions = new Deductions(
            (float) $this->taxesAmount,
            (float) $this->socialSecurityAmount,
            (float) $this->healthInsuranceAmount,
            $this->grossSalaryCurrency
        );

        $payroll = new DomainPayroll(
            new Uuid($this->id),
            new Uuid($this->employee->getId()),
            $period,
            $grossSalary,
            $deductions
        );

        // Set timestamps and status using reflection since they're private
        $reflection = new \ReflectionClass($payroll);
        
        $statusProperty = $reflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($payroll, PayrollStatus::fromString($this->status));

        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($payroll, $this->createdAt);

        if ($this->processedAt) {
            $processedAtProperty = $reflection->getProperty('processedAt');
            $processedAtProperty->setAccessible(true);
            $processedAtProperty->setValue($payroll, $this->processedAt);
        }

        if ($this->updatedAt) {
            $updatedAtProperty = $reflection->getProperty('updatedAt');
            $updatedAtProperty->setAccessible(true);
            $updatedAtProperty->setValue($payroll, $this->updatedAt);
        }

        return $payroll;
    }

    // Computed properties for API responses
    #[Groups(['payroll:read', 'payroll:item'])]
    public function getDaysInPeriod(): int
    {
        return $this->startDate->diff($this->endDate)->days + 1;
    }

    #[Groups(['payroll:read', 'payroll:item'])]
    public function getTotalDeductions(): float
    {
        return (float) $this->taxesAmount + (float) $this->socialSecurityAmount + (float) $this->healthInsuranceAmount;
    }

    #[Groups(['payroll:read', 'payroll:item'])]
    public function getPeriodFormat(): string
    {
        return sprintf(
            '%s to %s',
            $this->startDate->format('Y-m-d'),
            $this->endDate->format('Y-m-d')
        );
    }

    // Getters and setters
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

    public function getGrossSalaryAmount(): string
    {
        return $this->grossSalaryAmount;
    }

    public function setGrossSalaryAmount(string $grossSalaryAmount): self
    {
        $this->grossSalaryAmount = $grossSalaryAmount;
        $this->calculateNetSalary();
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getGrossSalaryCurrency(): string
    {
        return $this->grossSalaryCurrency;
    }

    public function setGrossSalaryCurrency(string $grossSalaryCurrency): self
    {
        $this->grossSalaryCurrency = $grossSalaryCurrency;
        $this->netSalaryCurrency = $grossSalaryCurrency;
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getTaxesAmount(): string
    {
        return $this->taxesAmount;
    }

    public function setTaxesAmount(string $taxesAmount): self
    {
        $this->taxesAmount = $taxesAmount;
        $this->calculateNetSalary();
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getSocialSecurityAmount(): string
    {
        return $this->socialSecurityAmount;
    }

    public function setSocialSecurityAmount(string $socialSecurityAmount): self
    {
        $this->socialSecurityAmount = $socialSecurityAmount;
        $this->calculateNetSalary();
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getHealthInsuranceAmount(): string
    {
        return $this->healthInsuranceAmount;
    }

    public function setHealthInsuranceAmount(string $healthInsuranceAmount): self
    {
        $this->healthInsuranceAmount = $healthInsuranceAmount;
        $this->calculateNetSalary();
        $this->updatedAt = new DateTimeImmutable();
        return $this;
    }

    public function getNetSalaryAmount(): string
    {
        return $this->netSalaryAmount;
    }

    public function getNetSalaryCurrency(): string
    {
        return $this->netSalaryCurrency;
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

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getProcessedAt(): ?DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function setProcessedAt(?DateTimeImmutable $processedAt): self
    {
        $this->processedAt = $processedAt;
        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function calculateNetSalary(): void
    {
        $grossAmount = (float) $this->grossSalaryAmount;
        $totalDeductions = (float) $this->taxesAmount + (float) $this->socialSecurityAmount + (float) $this->healthInsuranceAmount;
        $this->netSalaryAmount = (string) ($grossAmount - $totalDeductions);
        $this->netSalaryCurrency = $this->grossSalaryCurrency;
    }

    #[Assert\Callback]
    public function validateEndDate(\Symfony\Component\Validator\Context\ExecutionContextInterface $context): void
    {
        if (isset($this->startDate) && isset($this->endDate) && $this->endDate <= $this->startDate) {
            $context->buildViolation('End date must be after start date')
                ->atPath('endDate')
                ->addViolation();
        }
    }
}