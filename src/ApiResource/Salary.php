<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;

#[ApiResource(
    uriTemplate: '/v1/salaries',
    operations: [
        new GetCollection(
            uriTemplate: '/v1/salaries',
            controller: 'App\Api\Controller\SalaryController::getAllSalaries'
        ),
        new Post(
            uriTemplate: '/v1/salaries',
            controller: 'App\Api\Controller\SalaryController::createSalary'
        ),
        new Get(
            uriTemplate: '/v1/salaries/employee/{employeeId}',
            controller: 'App\Api\Controller\SalaryController::getSalaryByEmployee'
        )
    ]
)]
class Salary
{
    public function __construct(
        public string $id,
        public string $employeeId,
        public float $baseSalary,
        public float $bonus,
        public string $currency,
        public float $totalSalary
    ) {
    }
}