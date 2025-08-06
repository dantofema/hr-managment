<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use OpenApi\Attributes as OA;

#[ApiResource(
    uriTemplate: '/v1/payrolls',
    operations: [
        new GetCollection(
            uriTemplate: '/v1/payrolls',
            controller: 'App\Api\Controller\PayrollController::getAllPayrolls'
        ),
        new Post(
            uriTemplate: '/v1/payrolls/calculate',
            controller: 'App\Api\Controller\PayrollController::calculatePayroll'
        ),
        new Get(
            uriTemplate: '/v1/payrolls/employee/{employeeId}',
            controller: 'App\Api\Controller\PayrollController::getPayrollsByEmployee'
        ),
        new Get(
            uriTemplate: '/v1/payrolls/{payrollId}/receipt',
            controller: 'App\Api\Controller\PayrollController::getPayrollReceipt'
        )
    ]
)]
class Payroll
{
    public function __construct(
        public string $id,
        public string $employeeId,
        public float $grossSalary,
        public float $netSalary,
        public float $taxes,
        public float $deductions,
        public string $periodStart,
        public string $periodEnd,
        public string $calculatedAt
    ) {
    }
}