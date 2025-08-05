<?php

declare(strict_types=1);

namespace App\Domain\Payroll\Entity;

use App\Domain\Employee\ValueObject\EmployeeId;
use App\Domain\Payroll\ValueObject\Money;
use App\Domain\Payroll\ValueObject\PayrollId;
use App\Domain\Payroll\ValueObject\PayrollPeriod;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payrolls')]
class Payroll
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 36)]
    private string $employeeId;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $grossSalary;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $incomeTax;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $socialSecurity;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $healthInsurance;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $totalDeductions;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $netSalary;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'date_immutable')]
    private DateTimeImmutable $periodStart;

    #[ORM\Column(type: 'date_immutable')]
    private DateTimeImmutable $periodEnd;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $calculatedAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        PayrollId $id,
        EmployeeId $employeeId,
        Money $grossSalary,
        PayrollPeriod $period
    ) {
        $this->id = (string) $id;
        $this->employeeId = (string) $employeeId;
        $this->grossSalary = $grossSalary->getAmount();
        $this->currency = $grossSalary->getCurrency();
        $this->periodStart = $period->getStartDate();
        $this->periodEnd = $period->getEndDate();
        $this->calculatedAt = new DateTimeImmutable();
        $this->createdAt = new DateTimeImmutable();

        // Calculate deductions
        $this->calculateDeductions($grossSalary);
    }

    public static function calculate(
        EmployeeId $employeeId,
        Money $grossSalary,
        PayrollPeriod $period
    ): self {
        return new self(
            PayrollId::generate(),
            $employeeId,
            $grossSalary,
            $period
        );
    }

    public function getId(): PayrollId
    {
        return PayrollId::fromString($this->id);
    }

    public function getEmployeeId(): EmployeeId
    {
        return EmployeeId::fromString($this->employeeId);
    }

    public function getGrossSalary(): Money
    {
        return Money::fromFloat($this->grossSalary, $this->currency);
    }

    public function getIncomeTax(): Money
    {
        return Money::fromFloat($this->incomeTax, $this->currency);
    }

    public function getSocialSecurity(): Money
    {
        return Money::fromFloat($this->socialSecurity, $this->currency);
    }

    public function getHealthInsurance(): Money
    {
        return Money::fromFloat($this->healthInsurance, $this->currency);
    }

    public function getTotalDeductions(): Money
    {
        return Money::fromFloat($this->totalDeductions, $this->currency);
    }

    public function getNetSalary(): Money
    {
        return Money::fromFloat($this->netSalary, $this->currency);
    }

    public function getPeriod(): PayrollPeriod
    {
        return PayrollPeriod::fromDates($this->periodStart, $this->periodEnd);
    }

    public function getCalculatedAt(): DateTimeImmutable
    {
        return $this->calculatedAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function generateReceipt(): array
    {
        return [
            'payroll_id' => $this->id,
            'employee_id' => $this->employeeId,
            'period' => $this->getPeriod()->toArray(),
            'gross_salary' => $this->getGrossSalary()->toArray(),
            'deductions' => [
                'income_tax' => $this->getIncomeTax()->toArray(),
                'social_security' => $this->getSocialSecurity()->toArray(),
                'health_insurance' => $this->getHealthInsurance()->toArray(),
                'total' => $this->getTotalDeductions()->toArray(),
            ],
            'net_salary' => $this->getNetSalary()->toArray(),
            'calculated_at' => $this->calculatedAt->format('Y-m-d H:i:s'),
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employeeId,
            'gross_salary' => $this->getGrossSalary()->toArray(),
            'income_tax' => $this->getIncomeTax()->toArray(),
            'social_security' => $this->getSocialSecurity()->toArray(),
            'health_insurance' => $this->getHealthInsurance()->toArray(),
            'total_deductions' => $this->getTotalDeductions()->toArray(),
            'net_salary' => $this->getNetSalary()->toArray(),
            'period' => $this->getPeriod()->toArray(),
            'calculated_at' => $this->calculatedAt->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }

    private function calculateDeductions(Money $grossSalary): void
    {
        // Simple tax calculation logic
        // Income tax: 15% for amounts up to $50,000, 25% for amounts above
        $incomeTaxRate = $grossSalary->getAmount() <= 50000 ? 0.15 : 0.25;
        $this->incomeTax = $grossSalary->getAmount() * $incomeTaxRate;

        // Social Security: 6.2% of gross salary
        $this->socialSecurity = $grossSalary->getAmount() * 0.062;

        // Health Insurance: 2% of gross salary
        $this->healthInsurance = $grossSalary->getAmount() * 0.02;

        // Total deductions
        $this->totalDeductions = $this->incomeTax + $this->socialSecurity + $this->healthInsurance;

        // Net salary
        $this->netSalary = $grossSalary->getAmount() - $this->totalDeductions;
    }
}